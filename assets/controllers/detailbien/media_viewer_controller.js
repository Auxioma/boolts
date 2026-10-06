import { Controller } from '@hotwired/stimulus';

/**
 * Médias de la page détail d'un bien : grille photos (modale 1) et MediaViewer (modale 2).
 *
 * Grille, en conservant toujours l'ordre d'upload. Les images sont traitées par
 * séquences contiguës de même orientation (détectée via les dimensions natives :
 * largeur >= hauteur → paysage, sinon portrait ; image illisible → paysage) :
 * - paysages : alternance pleine largeur / paire côte à côte (1, 2, 1, 2…),
 *   une image restante seule passe en pleine largeur.
 *   2 → 1 + 1, 3 → 1 + 2, 4 → 1 + 2 + 1, 6 → 1 + 2 + 1 + 2 ;
 * - portraits : groupes de trois (un grand à gauche, deux empilés à droite),
 *   reste de 1 ou 2 sur une rangée (côte à côte s'il y en a deux).
 *
 * MediaViewer : l'image garde exactement son ratio natif. Sa hauteur est la hauteur
 * disponible de la zone du slider, sa largeur en découle ; si elle dépasse l'espace
 * entre les flèches, les deux sont réduites en gardant le ratio.
 * Ex. : 1200 × 800 avec 720 px de haut disponibles → 1080 × 720.
 *
 * Les déclencheurs ([data-open-slider], [data-open-gallery]) sont dans les partials
 * d'images et dans la grille générée : ils sont gérés par délégation sur l'élément racine.
 */
export default class extends Controller {
    static targets = ['gridModal', 'sliderModal', 'grid', 'stage', 'sliderImage', 'counter', 'prevButton', 'nextButton'];

    static values = {
        photos: { type: Array, default: [] },
    };

    connect() {
        this.currentIndex = 0;
        this.touchStartX = 0;

        this.renderGrid();
        this.updateSlider();
    }

    disconnect() {
        this.finishPush();
        document.body.classList.remove('bt-modal-open');
    }

    // ---------- Déclencheurs ----------

    handleClick(event) {
        const openGallery = event.target.closest('[data-open-gallery]');

        if (openGallery) {
            event.preventDefault();
            event.stopPropagation();
            this.openGrid();
            return;
        }

        const openSlider = event.target.closest('[data-open-slider]');

        if (openSlider) {
            event.preventDefault();
            this.openSlider(openSlider.dataset.openSlider);
        }
    }

    handleKeydown(event) {
        const trigger = event.target.closest ? event.target.closest('[data-open-gallery], [data-open-slider]') : null;

        if (trigger && this.element.contains(trigger) && (event.key === 'Enter' || event.key === ' ')) {
            event.preventDefault();

            if (trigger.hasAttribute('data-open-gallery')) {
                this.openGrid();
                return;
            }

            this.openSlider(trigger.dataset.openSlider);
            return;
        }

        if (event.key === 'Escape') {
            this.closeGrid();
            this.closeSlider();
        }

        if (this.isOpen(this.sliderModalTarget)) {
            if (event.key === 'ArrowRight') {
                this.next();
            }

            if (event.key === 'ArrowLeft') {
                this.prev();
            }
        }
    }

    touchStart(event) {
        this.touchStartX = event.changedTouches[0].clientX;
    }

    touchEnd(event) {
        const diff = event.changedTouches[0].clientX - this.touchStartX;

        if (Math.abs(diff) > 50) {
            diff < 0 ? this.next() : this.prev();
        }
    }

    // ---------- Modales ----------

    openGrid() {
        if (!this.hasPhotos() || !this.hasGridModalTarget) {
            return;
        }

        this.openModal(this.gridModalTarget);
        this.gridModalTarget.scrollTop = 0;
    }

    closeGrid(event) {
        event?.preventDefault();

        if (this.hasGridModalTarget) {
            this.closeModal(this.gridModalTarget);
        }
    }

    openSlider(index) {
        if (!this.hasPhotos() || !this.hasSliderModalTarget) {
            return;
        }

        const total = this.photosValue.length;

        this.currentIndex = Math.min(Math.max(Number(index) || 0, 0), total - 1);

        this.updateSlider();
        this.closeGrid();
        this.openModal(this.sliderModalTarget);

        // La modale vient de passer en display: block : sa zone n'est mesurable qu'à la frame suivante.
        requestAnimationFrame(() => this.fitSliderImage());
    }

    closeSlider(event) {
        event?.preventDefault();

        this.pushToken = (this.pushToken ?? 0) + 1;
        this.finishPush();

        if (this.hasSliderModalTarget) {
            this.closeModal(this.sliderModalTarget);
        }
    }

    openModal(modal) {
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('bt-modal-open');
    }

    closeModal(modal) {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');

        const isGridOpen = this.hasGridModalTarget && this.isOpen(this.gridModalTarget);
        const isSliderOpen = this.hasSliderModalTarget && this.isOpen(this.sliderModalTarget);

        if (!isGridOpen && !isSliderOpen) {
            document.body.classList.remove('bt-modal-open');
        }
    }

    isOpen(modal) {
        return modal.classList.contains('is-open');
    }

    // ---------- MediaViewer ----------

    next(event) {
        event?.preventDefault();

        if (!this.hasPhotos()) {
            return;
        }

        this.goTo((this.currentIndex + 1) % this.photosValue.length, 1);
    }

    prev(event) {
        event?.preventDefault();

        if (!this.hasPhotos()) {
            return;
        }

        const total = this.photosValue.length;

        this.goTo((this.currentIndex - 1 + total) % total, -1);
    }

    /**
     * Change d'image. Sous 768 px, animation « push » : une copie figée de l'image courante
     * sort du côté opposé pendant que la nouvelle entre (direction 1 = suivante, -1 = précédente).
     */
    goTo(index, direction) {
        const ghost = this.shouldAnimatePush() ? this.createPushGhost() : null;

        this.currentIndex = index;
        this.updateSlider();

        if (ghost) {
            this.playPush(ghost, direction);
        }
    }

    shouldAnimatePush() {
        return this.photosValue.length > 1
            && this.hasSliderImageTarget
            && this.hasSliderModalTarget
            && this.isOpen(this.sliderModalTarget)
            && window.matchMedia('(max-width: 767.98px)').matches
            && !window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    }

    createPushGhost() {
        this.finishPush();

        const image = this.sliderImageTarget;
        const rect = image.getBoundingClientRect();

        if (!rect.width || !rect.height) {
            return null;
        }

        const ghost = image.cloneNode(false);

        ['id', 'data-action', 'data-detailbien--media-viewer-target'].forEach((name) => ghost.removeAttribute(name));
        ghost.alt = '';
        ghost.setAttribute('aria-hidden', 'true');
        ghost.className = 'bt-slider-stage__ghost';
        Object.assign(ghost.style, {
            left: `${rect.left}px`,
            top: `${rect.top}px`,
            width: `${rect.width}px`,
            height: `${rect.height}px`,
        });

        this.sliderModalTarget.appendChild(ghost);

        return ghost;
    }

    async playPush(ghost, direction) {
        const image = this.sliderImageTarget;
        const incoming = image.closest('.bt-slider-stage__image') ?? image;
        const token = this.pushToken = (this.pushToken ?? 0) + 1;

        this.pushGhost = ghost;
        this.pushIncoming = incoming;

        // L'ancienne image (copie) reste affichée tant que la nouvelle n'est pas décodée.
        incoming.style.visibility = 'hidden';

        try {
            await image.decode();
        } catch {
            // Image illisible : on anime quand même.
        }

        if (token !== this.pushToken) {
            return;
        }

        this.fitSliderImage();
        incoming.style.visibility = '';

        const offset = window.innerWidth;
        const options = { duration: 320, easing: 'cubic-bezier(.22, .61, .36, 1)' };

        this.pushAnimations = [
            ghost.animate(
                [{ transform: 'translateX(0)' }, { transform: `translateX(${-direction * offset}px)` }],
                { ...options, fill: 'forwards' },
            ),
            incoming.animate(
                [{ transform: `translateX(${direction * offset}px)` }, { transform: 'translateX(0)' }],
                options,
            ),
        ];

        Promise.all(this.pushAnimations.map((animation) => animation.finished))
            .then(() => {
                if (token === this.pushToken) {
                    this.finishPush();
                }
            })
            .catch(() => {});
    }

    finishPush() {
        this.pushAnimations?.forEach((animation) => animation.cancel());
        this.pushAnimations = null;
        this.pushGhost?.remove();
        this.pushGhost = null;

        if (this.pushIncoming) {
            this.pushIncoming.style.visibility = '';
            this.pushIncoming = null;
        }
    }

    updateSlider() {
        if (!this.hasPhotos() || !this.hasSliderImageTarget || !this.hasCounterTarget) {
            return;
        }

        const photo = this.photosValue[this.currentIndex];
        const image = this.sliderImageTarget;

        image.removeAttribute('style');
        image.src = photo.src;
        image.alt = photo.alt;

        // Image déjà en cache (ou même src) : l'événement load peut ne pas se déclencher.
        if (image.complete) {
            this.fitSliderImage();
        }

        this.counterTarget.textContent = `${this.currentIndex + 1}/${this.photosValue.length}`;
    }

    fitSliderImage() {
        if (!this.hasStageTarget || !this.hasSliderImageTarget) {
            return;
        }

        const image = this.sliderImageTarget;

        if (!image.naturalWidth || !image.naturalHeight) {
            return;
        }

        const availableHeight = this.stageTarget.clientHeight;
        let availableWidth = this.stageTarget.clientWidth;

        if (!availableHeight || !availableWidth) {
            return;
        }

        // Flèches visibles (desktop) : l'image tient entre elles, avec 16 px de marge de chaque côté.
        if (this.hasPrevButtonTarget && this.hasNextButtonTarget) {
            const prevRect = this.prevButtonTarget.getBoundingClientRect();
            const nextRect = this.nextButtonTarget.getBoundingClientRect();

            if (prevRect.width && nextRect.width) {
                availableWidth = Math.min(availableWidth, nextRect.left - prevRect.right - 32);
            }
        }

        const ratio = image.naturalWidth / image.naturalHeight;
        let height = availableHeight;
        let width = height * ratio;

        if (width > availableWidth) {
            width = availableWidth;
            height = width / ratio;
        }

        image.style.width = `${Math.round(width)}px`;
        image.style.height = `${Math.round(height)}px`;
        image.style.maxWidth = 'none';
        image.style.maxHeight = 'none';
    }

    // ---------- Grille ----------

    async renderGrid() {
        if (!this.hasPhotos() || !this.hasGridTarget) {
            return;
        }

        const orientations = await Promise.all(this.photosValue.map((photo) => this.loadOrientation(photo)));
        const sequences = [];

        orientations.forEach((orientation, index) => {
            const current = sequences[sequences.length - 1];

            if (current && current.orientation === orientation) {
                current.indexes.push(index);
            } else {
                sequences.push({ orientation, indexes: [index] });
            }
        });

        const rows = sequences.flatMap((sequence) => (sequence.orientation === 'landscape'
            ? this.renderLandscapeSequence(sequence.indexes)
            : this.renderPortraitSequence(sequence.indexes)));

        this.gridTarget.innerHTML = rows.join('');
    }

    loadOrientation(photo) {
        return new Promise((resolve) => {
            const image = new Image();

            image.onload = () => resolve(image.naturalWidth >= image.naturalHeight ? 'landscape' : 'portrait');
            image.onerror = () => resolve('landscape');
            image.src = photo.src;
        });
    }

    renderLandscapeSequence(indexes) {
        const rows = [];
        let cursor = 0;

        while (cursor < indexes.length) {
            rows.push(this.createRow('one', [this.createItem(indexes[cursor++], 'landscape')]));

            if (cursor < indexes.length) {
                const pair = indexes.slice(cursor, cursor + 2);
                cursor += pair.length;

                rows.push(this.createRow(
                    pair.length === 2 ? 'two' : 'one',
                    pair.map((index) => this.createItem(index, 'landscape')),
                ));
            }
        }

        return rows;
    }

    renderPortraitSequence(indexes) {
        const rows = [];
        let cursor = 0;

        for (; cursor + 3 <= indexes.length; cursor += 3) {
            rows.push(this.createRow('trio', [
                this.createItem(indexes[cursor], 'portrait', 'big'),
                this.createItem(indexes[cursor + 1], 'portrait', 'stacked'),
                this.createItem(indexes[cursor + 2], 'portrait', 'stacked'),
            ]));
        }

        const rest = indexes.slice(cursor);

        if (rest.length > 0) {
            rows.push(this.createRow(
                rest.length === 2 ? 'two' : 'one',
                rest.map((index) => this.createItem(index, 'portrait')),
            ));
        }

        return rows;
    }

    createRow(type, items) {
        return `<div class="detail-bien-modal-mensouri-row detail-bien-modal-mensouri-row--${type}">${items.join('')}</div>`;
    }

    createItem(index, orientation, modifier = null) {
        const photo = this.photosValue[index];
        const modifierClass = modifier ? ` detail-bien-modal-mensouri-item--${modifier}` : '';
        const src = this.escape(photo.src);
        const alt = this.escape(photo.alt);

        return `
            <button
                type="button"
                class="detail-bien-modal-mensouri-item detail-bien-modal-mensouri-item--${orientation}${modifierClass}"
                data-open-slider="${index}"
                aria-label="Ouvrir ${alt}"
            >
                <img src="${src}" alt="${alt}" loading="lazy">
            </button>
        `;
    }

    // ---------- Utilitaires ----------

    hasPhotos() {
        return this.photosValue.length > 0;
    }

    escape(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }
}
