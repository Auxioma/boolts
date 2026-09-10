// assets/controllers/mes_biens/images_controller.js
//
// Étape 6 du tunnel « Mes biens » : les photos sont téléversées, réordonnées et
// supprimées individuellement en AJAX (AgenceImmobiliereMesBiensImagesController)
// et persistées au fil de l'eau. Plus aucun fichier ne transite par le
// formulaire Symfony : le bouton « Suivant » ne fait que valider qu'au moins une
// photo est en base.

import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['browseCard', 'browseButton'];

    static values = {
        uploadUrl: String,
        reorderUrl: String,
        deleteUrl: String,
        csrfToken: String,
        maxImages: {
            type: Number,
            default: 50,
        },
        minimumVisibleCards: {
            type: Number,
            default: 5,
        },
    };

    connect() {
        this.draggedCard = null;
        this.dragGhost = null;

        this.uploadQueue = [];
        this.isProcessingQueue = false;
        this.persistOrderTimeout = null;

        this.imageActionsHtml = `
            <div class="property-image-actions">
                <button
                    type="button"
                    class="property-image-actions-button js-property-image-actions-button"
                    aria-label="Actions de l'image"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M12 13C12.5523 13 13 12.5523 13 12C13 11.4477 12.5523 11 12 11C11.4477 11 11 11.4477 11 12C11 12.5523 11.4477 13 12 13Z" stroke="#5D00FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M19 13C19.5523 13 20 12.5523 20 12C20 11.4477 19.5523 11 19 11C18.4477 11 18 11.4477 18 12C18 12.5523 18.4477 13 19 13Z" stroke="#5D00FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M5 13C5.55228 13 6 12.5523 6 12C6 11.4477 5.55228 11 5 11C4.44772 11 4 11.4477 4 12C4 12.5523 4.44772 13 5 13Z" stroke="#5D00FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>

                <div class="property-image-actions-menu d-none">
                    <button type="button" data-image-action="cover" class="couverture-image">
                        <i class="icon-image mr-8"></i> Définir comme couverture
                    </button>

                    <button type="button" data-image-action="forward" class="avant-image">
                        <i class="icon-chevrons-up-down mr-8"></i> Déplacer vers l’avant
                    </button>

                    <button type="button" data-image-action="backward" class="arriere-image">
                        <i class="icon-chevrons-up-down mr-8"></i> Déplacer vers l’arrière
                    </button>

                    <button type="button" data-image-action="delete" class="supprime-image">
                        <i class="icon-trash mr-8"></i> Supprimer
                    </button>
                </div>
            </div>
        `;

        this.emptyCardHtml = `
            <div class="property-empty-content">
                <svg xmlns="http://www.w3.org/2000/svg" width="29" height="24" viewBox="0 0 29 24" fill="none">
                    <path d="M21 15L17.914 11.914C17.5389 11.5391 17.0303 11.3284 16.5 11.3284C15.9697 11.3284 15.4611 11.5391 15.086 11.914L6 21M5 3H19C20.1046 3 21 3.89543 21 5V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V5C3 3.89543 3.89543 5 5 3ZM11 9C11 10.1046 10.1046 11 9 11C7.89543 11 7 10.1046 7 9C7 7.89543 7.89543 7 9 7C10.1046 7 11 7.89543 11 9Z" stroke="#858789" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
        `;

        this.closeAllMenusHandler = this.closeAllMenus.bind(this);

        document.addEventListener('click', this.closeAllMenusHandler);

        this.enableAllCards();
        this.ensureMinimumCards();
        this.ensureOneEmptyCardIfPossible();
        this.normalizeGrid();

        this.notifySubmitController();
    }

    disconnect() {
        document.removeEventListener('click', this.closeAllMenusHandler);
        this.removeDragGhost();

        if (this.persistOrderTimeout) {
            clearTimeout(this.persistOrderTimeout);
        }
    }

    notifySubmitController() {
        this.element.dispatchEvent(new Event('change', {
            bubbles: true,
        }));
    }

    // ------------------------------------------------------------------
    // Sélection de fichiers
    // ------------------------------------------------------------------

    browse(event) {
        event.preventDefault();
        event.stopPropagation();

        this.openFilePicker();
    }

    dragOverBrowse(event) {
        event.preventDefault();

        if (event.dataTransfer) {
            event.dataTransfer.dropEffect = 'copy';
        }
    }

    dropOnBrowse(event) {
        event.preventDefault();

        if (!event.dataTransfer || !event.dataTransfer.files) {
            return;
        }

        this.enqueueFiles(event.dataTransfer.files);
    }

    /**
     * Ouvre le sélecteur de fichiers du système en autorisant la sélection
     * multiple. Chaque image choisie rejoint la file d'attente d'upload.
     */
    openFilePicker() {
        const picker = document.createElement('input');

        picker.type = 'file';
        picker.accept = 'image/*';
        picker.multiple = true;
        picker.classList.add('d-none');

        picker.addEventListener('change', () => {
            if (picker.files && picker.files.length > 0) {
                this.enqueueFiles(picker.files);
            }

            picker.remove();
        });

        this.element.appendChild(picker);
        picker.click();
    }

    isImageFile(file) {
        return file && file.type && file.type.startsWith('image/');
    }

    /**
     * Crée immédiatement une carte « en cours d'upload » pour chaque fichier
     * valide, dans la limite de maxImages, puis lance la file d'attente.
     */
    enqueueFiles(files) {
        const validFiles = Array.from(files).filter((file) => this.isImageFile(file));

        if (validFiles.length === 0) {
            return;
        }

        const used = this.getFilledCards().length
            + this.getUploadingCards().length
            + this.getErrorCards().length;

        const freeSlots = Math.max(this.maxImagesValue - used, 0);
        const filesToProcess = validFiles.slice(0, freeSlots);

        filesToProcess.forEach((file) => {
            const card = this.createUploadingCard(file);

            this.uploadQueue.push({ card, file });
        });

        this.normalizeGrid();
        this.ensureMinimumCards();
        this.ensureOneEmptyCardIfPossible();
        this.notifySubmitController();

        this.processQueue();
    }

    createUploadingCard(file) {
        const card = document.createElement('div');
        const previewUrl = URL.createObjectURL(file);

        card.className = 'property-preview-card is-uploading';
        card.dataset.previewUrl = previewUrl;
        card.innerHTML = `
            <img src="${previewUrl}" alt="Photo du bien">
            <div class="property-image-uploading">
                <span class="property-image-uploading-spinner"></span>
            </div>
            ${this.imageActionsHtml}
        `;

        this.element.insertBefore(card, this.browseCardTarget);

        return card;
    }

    // ------------------------------------------------------------------
    // File d'attente d'upload (séquentielle)
    // ------------------------------------------------------------------

    async processQueue() {
        if (this.isProcessingQueue) {
            return;
        }

        this.isProcessingQueue = true;

        while (this.uploadQueue.length > 0) {
            const { card, file } = this.uploadQueue.shift();

            if (!card.isConnected) {
                this.revokePreview(card);

                continue;
            }

            await this.uploadOne(card, file);
        }

        this.isProcessingQueue = false;

        this.schedulePersistOrder();
        this.notifySubmitController();
    }

    async uploadOne(card, file) {
        const formData = new FormData();

        formData.append('image', file);
        formData.append('csrfToken', this.csrfTokenValue);

        try {
            const response = await fetch(this.uploadUrlValue, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            const result = await response.json().catch(() => ({
                success: false,
                message: 'Réponse invalide du serveur.',
            }));

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Le téléversement a échoué.');
            }

            this.markCardUploaded(card, result.image);
        } catch (error) {
            this.markCardError(card, file, error.message || 'Le téléversement a échoué.');
        }
    }

    markCardUploaded(card, image) {
        card.classList.remove('is-uploading', 'is-error');
        card.classList.add('is-filled');
        card.dataset.imageId = image.id;
        card.setAttribute('draggable', 'true');

        const uploadingOverlay = card.querySelector('.property-image-uploading');

        if (uploadingOverlay) {
            uploadingOverlay.remove();
        }

        const img = card.querySelector('img');

        if (img) {
            const fallback = card.dataset.previewUrl;

            img.addEventListener('error', () => {
                if (fallback && img.src !== fallback) {
                    img.src = fallback;
                }
            }, { once: true });

            img.src = image.thumbnailUrl;
        }

        this.revokePreview(card);
        this.enableFilledCard(card);
        this.normalizeGrid();
        this.notifySubmitController();
    }

    markCardError(card, file, message) {
        card.classList.remove('is-uploading', 'is-filled');
        card.classList.add('is-error');
        delete card.dataset.imageId;

        card.innerHTML = `
            <div class="property-image-error">
                <span>${this.escapeHtml(message)}</span>
                <button type="button" data-image-retry>Réessayer</button>
            </div>
        `;

        const retryButton = card.querySelector('[data-image-retry]');

        if (retryButton) {
            retryButton.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();

                this.retryCard(card, file);
            });
        }
    }

    retryCard(card, file) {
        const previewUrl = card.dataset.previewUrl || URL.createObjectURL(file);

        card.dataset.previewUrl = previewUrl;
        card.classList.remove('is-error');
        card.classList.add('is-uploading');
        card.innerHTML = `
            <img src="${previewUrl}" alt="Photo du bien">
            <div class="property-image-uploading">
                <span class="property-image-uploading-spinner"></span>
            </div>
            ${this.imageActionsHtml}
        `;

        this.uploadQueue.push({ card, file });
        this.processQueue();
    }

    revokePreview(card) {
        if (card.dataset.previewUrl) {
            URL.revokeObjectURL(card.dataset.previewUrl);
            delete card.dataset.previewUrl;
        }
    }

    // ------------------------------------------------------------------
    // Sélecteurs de cartes
    // ------------------------------------------------------------------

    getPreviewCards() {
        return Array.from(this.element.querySelectorAll('.property-preview-card')).filter((card) => {
            return !card.classList.contains('property-browse-card');
        });
    }

    getFilledCards() {
        return this.getPreviewCards().filter((card) => card.classList.contains('is-filled'));
    }

    getUploadingCards() {
        return this.getPreviewCards().filter((card) => card.classList.contains('is-uploading'));
    }

    getErrorCards() {
        return this.getPreviewCards().filter((card) => card.classList.contains('is-error'));
    }

    getEmptyCards() {
        return this.getPreviewCards().filter((card) => card.classList.contains('is-empty'));
    }

    getBusyCards() {
        return this.getPreviewCards().filter((card) => {
            return !card.classList.contains('is-empty');
        });
    }

    // ------------------------------------------------------------------
    // Grille / positions
    // ------------------------------------------------------------------

    closeAllMenus() {
        this.element.querySelectorAll('.property-image-actions-menu').forEach((menu) => {
            menu.classList.add('d-none');
        });

        this.element.querySelectorAll('.property-preview-card').forEach((card) => {
            card.classList.remove('is-menu-open');
        });
    }

    updatePositions() {
        this.getPreviewCards().forEach((card, index) => {
            card.dataset.position = index + 1;
        });

        this.getFilledCards().forEach((card, index) => {
            card.classList.toggle('is-cover', index === 0);
        });
    }

    normalizeGrid() {
        this.getBusyCards().forEach((card) => {
            this.element.insertBefore(card, this.browseCardTarget);
        });

        this.getEmptyCards().forEach((card) => {
            this.element.insertBefore(card, this.browseCardTarget);
        });

        this.element.appendChild(this.browseCardTarget);

        this.updatePositions();
    }

    createEmptyCard() {
        const card = document.createElement('div');

        card.className = 'property-preview-card is-empty js-property-empty-card';
        card.dataset.position = this.getPreviewCards().length + 1;
        card.innerHTML = this.emptyCardHtml;

        this.element.insertBefore(card, this.browseCardTarget);

        this.enableEmptyCard(card);
        this.updatePositions();

        return card;
    }

    ensureMinimumCards() {
        const totalCards = this.getPreviewCards().length;

        if (totalCards >= this.minimumVisibleCardsValue) {
            return;
        }

        for (let i = totalCards; i < this.minimumVisibleCardsValue; i++) {
            this.createEmptyCard();
        }

        this.updatePositions();
    }

    ensureOneEmptyCardIfPossible() {
        if (this.getPreviewCards().length >= this.maxImagesValue) {
            return;
        }

        if (this.getEmptyCards().length > 0) {
            return;
        }

        this.createEmptyCard();
    }

    openFileInputForCard(card) {
        if (!card || !card.classList.contains('is-empty')) {
            return;
        }

        this.openFilePicker();
    }

    // ------------------------------------------------------------------
    // Réordonnancement
    // ------------------------------------------------------------------

    moveCardToCover(card) {
        const firstFilledCard = this.getFilledCards()[0];

        if (firstFilledCard && firstFilledCard !== card) {
            this.element.insertBefore(card, firstFilledCard);
        }

        this.normalizeGrid();
        this.notifySubmitController();
        this.schedulePersistOrder();
    }

    moveCardForward(card) {
        const filledCards = this.getFilledCards();
        const currentIndex = filledCards.indexOf(card);
        const previousCard = filledCards[currentIndex - 1];

        if (!previousCard) {
            return;
        }

        this.element.insertBefore(card, previousCard);
        this.normalizeGrid();
        this.notifySubmitController();
        this.schedulePersistOrder();
    }

    moveCardBackward(card) {
        const filledCards = this.getFilledCards();
        const currentIndex = filledCards.indexOf(card);
        const nextCard = filledCards[currentIndex + 1];

        if (!nextCard) {
            return;
        }

        this.element.insertBefore(card, nextCard.nextSibling);
        this.normalizeGrid();
        this.notifySubmitController();
        this.schedulePersistOrder();
    }

    deleteCard(card) {
        const imageId = card.dataset.imageId;

        if (!imageId) {
            this.revokePreview(card);
            card.remove();

            this.ensureMinimumCards();
            this.ensureOneEmptyCardIfPossible();
            this.normalizeGrid();
            this.notifySubmitController();

            return;
        }

        card.classList.add('is-uploading');

        fetch(this.deleteUrlValue.replace('__ID__', encodeURIComponent(imageId)), {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ csrfToken: this.csrfTokenValue }),
        })
            .then((response) => response.json().catch(() => ({ success: false })))
            .then((result) => {
                if (!result.success) {
                    throw new Error(result.message || 'La suppression a échoué.');
                }

                this.revokePreview(card);
                card.remove();

                this.ensureMinimumCards();
                this.ensureOneEmptyCardIfPossible();
                this.normalizeGrid();
                this.notifySubmitController();
                this.schedulePersistOrder();
            })
            .catch((error) => {
                card.classList.remove('is-uploading');
                window.alert(error.message || 'La suppression a échoué.');
            });
    }

    schedulePersistOrder() {
        if (this.persistOrderTimeout) {
            clearTimeout(this.persistOrderTimeout);
        }

        this.persistOrderTimeout = setTimeout(() => {
            this.persistOrderTimeout = null;
            this.persistOrder();
        }, 400);
    }

    persistOrder() {
        const order = this.getFilledCards()
            .map((card) => card.dataset.imageId)
            .filter(Boolean);

        if (order.length === 0) {
            return;
        }

        fetch(this.reorderUrlValue, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                csrfToken: this.csrfTokenValue,
                order,
            }),
        })
            .then((response) => response.json().catch(() => ({ success: false })))
            .then((result) => {
                if (!result.success) {
                    // eslint-disable-next-line no-console
                    console.warn('[mes-biens--images] réordonnancement non enregistré :', result.message);
                }
            })
            .catch(() => {
                // eslint-disable-next-line no-console
                console.warn('[mes-biens--images] réordonnancement : erreur réseau.');
            });
    }

    // ------------------------------------------------------------------
    // Menu d'actions
    // ------------------------------------------------------------------

    bindActions(card) {
        if (card.dataset.actionsEnabled === '1') {
            return;
        }

        card.dataset.actionsEnabled = '1';

        const actionsButton = card.querySelector('.js-property-image-actions-button');
        const actionsMenu = card.querySelector('.property-image-actions-menu');

        if (!actionsButton || !actionsMenu) {
            return;
        }

        actionsButton.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();

            const isOpen = !actionsMenu.classList.contains('d-none');

            this.closeAllMenus();

            if (!isOpen) {
                card.classList.add('is-menu-open');
                actionsMenu.classList.remove('d-none');
            }
        });

        actionsMenu.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();

            const button = event.target.closest('[data-image-action]');

            if (!button) {
                return;
            }

            const action = button.dataset.imageAction;

            if (action === 'cover') {
                this.moveCardToCover(card);
            }

            if (action === 'forward') {
                this.moveCardForward(card);
            }

            if (action === 'backward') {
                this.moveCardBackward(card);
            }

            if (action === 'delete') {
                this.deleteCard(card);
            }

            this.closeAllMenus();
        });
    }

    // ------------------------------------------------------------------
    // Drag & drop
    // ------------------------------------------------------------------

    createDragGhost(card, event) {
        this.removeDragGhost();

        this.dragGhost = card.cloneNode(true);

        const rectangle = card.getBoundingClientRect();

        this.dragGhost.classList.add('property-preview-card-drag-ghost');
        this.dragGhost.style.width = `${rectangle.width}px`;
        this.dragGhost.style.height = `${rectangle.height}px`;

        document.body.appendChild(this.dragGhost);

        const offsetX = event.clientX - rectangle.left;
        const offsetY = event.clientY - rectangle.top;

        if (event.dataTransfer) {
            event.dataTransfer.setDragImage(this.dragGhost, offsetX, offsetY);
        }
    }

    removeDragGhost() {
        if (this.dragGhost) {
            this.dragGhost.remove();
            this.dragGhost = null;
        }
    }

    enableFilledCard(card) {
        this.bindActions(card);
        this.enableFilledCardDragAndDrop(card);
    }

    enableFilledCardDragAndDrop(card) {
        if (!card.classList.contains('is-filled')) {
            return;
        }

        if (card.dataset.dragEnabled === '1') {
            return;
        }

        card.dataset.dragEnabled = '1';
        card.setAttribute('draggable', 'true');

        card.addEventListener('dragstart', (event) => {
            if (event.target.closest('.property-image-actions')) {
                event.preventDefault();
                return;
            }

            this.draggedCard = card;

            this.closeAllMenus();
            this.createDragGhost(card, event);

            card.classList.add('is-dragging');

            if (event.dataTransfer) {
                event.dataTransfer.effectAllowed = 'move';
            }
        });

        card.addEventListener('dragend', () => {
            this.draggedCard = null;

            card.classList.remove('is-dragging');

            this.removeDragGhost();
            this.normalizeGrid();
            this.notifySubmitController();
            this.schedulePersistOrder();
        });

        card.addEventListener('dragover', (event) => {
            if (!this.draggedCard || this.draggedCard === card) {
                return;
            }

            event.preventDefault();

            const rectangle = card.getBoundingClientRect();
            const middle = rectangle.height / 2;
            const mousePosition = event.clientY - rectangle.top;

            if (mousePosition > middle) {
                this.element.insertBefore(this.draggedCard, card.nextSibling);
            } else {
                this.element.insertBefore(this.draggedCard, card);
            }

            this.updatePositions();
        });

        card.addEventListener('drop', (event) => {
            if (!this.draggedCard) {
                return;
            }

            event.preventDefault();
            this.normalizeGrid();
            this.notifySubmitController();
            this.schedulePersistOrder();
        });
    }

    enableEmptyCard(card) {
        if (!card.classList.contains('is-empty')) {
            return;
        }

        if (card.dataset.emptyEnabled === '1') {
            return;
        }

        card.dataset.emptyEnabled = '1';

        card.addEventListener('click', () => {
            this.openFileInputForCard(card);
        });

        card.addEventListener('dragenter', (event) => {
            event.preventDefault();

            if (this.draggedCard) {
                return;
            }

            card.classList.add('is-drag-over');
        });

        card.addEventListener('dragover', (event) => {
            if (this.draggedCard) {
                return;
            }

            event.preventDefault();

            if (event.dataTransfer) {
                event.dataTransfer.dropEffect = 'copy';
            }

            card.classList.add('is-drag-over');
        });

        card.addEventListener('dragleave', () => {
            card.classList.remove('is-drag-over');
        });

        card.addEventListener('drop', (event) => {
            if (this.draggedCard) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();

            card.classList.remove('is-drag-over');

            if (!event.dataTransfer || !event.dataTransfer.files) {
                return;
            }

            this.enqueueFiles(event.dataTransfer.files);
        });
    }

    enableAllCards() {
        this.getFilledCards().forEach((card) => {
            this.enableFilledCard(card);
        });

        this.getEmptyCards().forEach((card) => {
            this.enableEmptyCard(card);
        });
    }

    escapeHtml(value) {
        const div = document.createElement('div');

        div.textContent = String(value);

        return div.innerHTML;
    }
}
