import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    static targets = ['bar', 'item'];

    connect() {
        window.addEventListener('profile:field-saved', this.handleFieldSaved);
        this.updateProgress();
    }

    disconnect() {
        window.removeEventListener('profile:field-saved', this.handleFieldSaved);
    }

    handleFieldSaved = (event) => {
        const field = event.detail.field;
        const value = event.detail.value;

        const item = this.itemTargets.find((element) => {
            return element.dataset.field === field;
        });

        if (!item) {
            return;
        }

        const isDone = this.isValueFilled(value);

        item.dataset.done = isDone ? 'true' : 'false';
        item.classList.toggle('active', isDone);

        const iconWrapper = item.querySelector('[data-profile-progress-target="icon"]');

        if (iconWrapper) {
            iconWrapper.innerHTML = isDone ? '<i class="icon-check mr-8"></i>' : '';
        }

        this.updateProgress();
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
    }

    isValueFilled(value) {
        if (value === null || value === undefined) {
            return false;
        }

        if (typeof value === 'string') {
            return value.trim() !== '';
        }

        if (typeof value === 'object') {
            return Object.values(value).some((item) => {
                return String(item ?? '').trim() !== '';
            });
        }

        return true;
    }
}
