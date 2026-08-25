import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        url: String,
        csrf: String
    };

    connect() {
        this.changeImageButton = this.element.querySelector('#change-image-button');
        this.imageInput = this.element.querySelector('.js-image-input');
        this.avatarPreview = this.element.querySelector('.js-avatar-preview');

        if (!this.changeImageButton || !this.imageInput) {
            return;
        }

        this.clickListener = () => {
            this.imageInput.click();
        };

        this.changeListener = (event) => {
            this.upload(event);
        };

        this.changeImageButton.addEventListener('click', this.clickListener);
        this.imageInput.addEventListener('change', this.changeListener);
    }

    disconnect() {
        if (this.changeImageButton && this.clickListener) {
            this.changeImageButton.removeEventListener('click', this.clickListener);
        }

        if (this.imageInput && this.changeListener) {
            this.imageInput.removeEventListener('change', this.changeListener);
        }
    }

    async upload(event) {
        const file = event.target.files[0];

        if (!file) {
            return;
        }

        if (this.avatarPreview) {
            this.avatarPreview.src = URL.createObjectURL(file);
        }

        const formData = new FormData();

        formData.append('imageFile', file);
        formData.append('_token', this.csrfValue);

        try {
            this.changeImageButton.textContent = '...';
            this.changeImageButton.disabled = true;

            const response = await fetch(this.urlValue, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            const data = await response.json();

            this.changeImageButton.textContent = data.success ? 'Modifier' : 'Erreur';
        } catch (error) {
            console.error(error);
            this.changeImageButton.textContent = 'Erreur';
        } finally {
            this.changeImageButton.disabled = false;
        }
    }
}
