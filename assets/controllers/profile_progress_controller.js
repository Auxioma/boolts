import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    static targets = ['bar', 'item'];

    connect() {
        this.boundRefreshFromSave = this.handleFieldSaved.bind(this);

        window.addEventListener('profile:field-saved', this.boundRefreshFromSave);
        this.refreshFromPage();
    }

    disconnect() {
        window.removeEventListener('profile:field-saved', this.boundRefreshFromSave);
    }

    handleFieldSaved(event) {
        const field = event.detail.field;
        const value = event.detail.value;

        const previewLink = this.getPreviewLink();

        if (previewLink && Object.prototype.hasOwnProperty.call(event.detail, 'publicProfileUrl')) {
            previewLink.dataset.enabledHref = event.detail.publicProfileUrl || '';
        }

        const item = this.itemTargets.find((element) => this.matchesItemField(element, field));

        if (!item) {
            this.refreshFromPage();
            return;
        }

        const isDone = item.dataset.requiredSelector
            ? this.isItemFilledFromPage(item)
            : this.isValueFilled(value);

        this.setItemState(item, isDone);
        this.updateProgress();
    }

    refreshFromPage() {
        this.itemTargets.forEach((item) => {
            if (!item.dataset.requiredSelector) {
                return;
            }

            this.setItemState(item, this.isItemFilledFromPage(item));
        });

        this.updateProgress();
    }

    matchesItemField(item, field) {
        if (item.dataset.field === field) {
            return true;
        }

        return String(item.dataset.fieldAliases ?? '')
            .split(',')
            .map((alias) => alias.trim())
            .filter(Boolean)
            .includes(field);
    }

    updateProgress() {
        const requiredItems = this.itemTargets.filter((item) => {
            return item.dataset.optional !== 'true';
        });

        const doneItems = requiredItems.filter((item) => {
            return item.dataset.done === 'true';
        });

        const progress = requiredItems.length > 0
            ? Math.round((doneItems.length / requiredItems.length) * 100)
            : 100;

        this.barTarget.style.width = `${progress}%`;
        this.barTarget.setAttribute('aria-valuenow', progress);
        this.updatePreviewLink(requiredItems.length === doneItems.length);
        this.element.hidden = requiredItems.length === doneItems.length;
    }

    isValueFilled(value) {
        if (value === null || value === undefined) {
            return false;
        }

        if (typeof value === 'string') {
            return value.trim() !== '';
        }

        if (Array.isArray(value)) {
            return value.some((item) => {
                return String(item ?? '').trim() !== '';
            });
        }

        if (typeof value === 'object') {
            if (
                Object.prototype.hasOwnProperty.call(value, 'adresseContact')
                || Object.prototype.hasOwnProperty.call(value, 'codePostalContact')
                || Object.prototype.hasOwnProperty.call(value, 'villeContact')
                || Object.prototype.hasOwnProperty.call(value, 'paysContact')
            ) {
                return ['adresseContact', 'codePostalContact', 'villeContact', 'paysContact'].every((key) => {
                    return String(value[key] ?? '').trim() !== '';
                });
            }

            return Object.values(value).some((item) => {
                return String(item ?? '').trim() !== '';
            });
        }

        return true;
    }

    isItemFilledFromPage(item) {
        const inputs = this.getItemInputs(item);

        if (inputs.length === 0) {
            return item.dataset.done === 'true';
        }

        if (item.dataset.requiredMode === 'all') {
            return inputs.every((input) => {
                return this.isInputFilled(input);
            });
        }

        return inputs.some((input) => {
            return this.isInputFilled(input);
        });
    }

    getItemInputs(item) {
        const selector = item.dataset.requiredSelector;

        if (!selector) {
            return [];
        }

        try {
            return Array.from(document.querySelectorAll(selector));
        } catch (error) {
            console.error(error);

            return [];
        }
    }

    isInputFilled(input) {
        if (input.tagName === 'SELECT' && input.multiple) {
            return input.selectedOptions.length > 0;
        }

        if (input.type === 'checkbox' || input.type === 'radio') {
            return input.checked;
        }

        return String(input.dataset.fullPhoneValue ?? input.value ?? '').trim() !== '';
    }

    setItemState(item, isDone) {
        item.dataset.done = isDone ? 'true' : 'false';
        item.classList.toggle('active', isDone);

        const iconWrapper = item.querySelector('[data-profile-progress-target="icon"]');

        if (iconWrapper) {
            iconWrapper.innerHTML = isDone ? '<i class="icon-check mr-8"></i>' : '';
        }
    }

    updatePreviewLink(requiredFieldsDone) {
        const previewLink = this.getPreviewLink();

        if (!previewLink) {
            return;
        }

        const enabledHref = previewLink.dataset.enabledHref || '';
        const isReady = requiredFieldsDone && enabledHref.trim() !== '';

        previewLink.classList.toggle('is-disabled', !isReady);

        if (isReady) {
            previewLink.href = enabledHref;
            previewLink.target = '_blank';
            previewLink.rel = 'noopener';
            previewLink.removeAttribute('aria-disabled');
            previewLink.removeAttribute('tabindex');
        } else {
            previewLink.removeAttribute('href');
            previewLink.removeAttribute('target');
            previewLink.removeAttribute('rel');
            previewLink.setAttribute('aria-disabled', 'true');
            previewLink.setAttribute('tabindex', '-1');
        }

        const previewHelp = document.querySelector('[data-profile-preview-help]');

        if (previewHelp) {
            previewHelp.textContent = isReady
                ? previewHelp.dataset.readyText
                : previewHelp.dataset.disabledText;
        }
    }

    getPreviewLink() {
        return document.querySelector('[data-profile-preview-link]');
    }
}
