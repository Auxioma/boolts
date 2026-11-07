import { Controller } from '@hotwired/stimulus';
import { getComponent } from '@symfony/ux-live-component';

/*
 * Compagnon JS du Live Component Messagerie:Thread.
 *
 * - garde le fil défilé en bas quand de nouveaux messages arrivent ;
 * - affiche l'aperçu de l'image choisie ;
 * - vide le champ image après un envoi réussi.
 */
export default class extends Controller {
    static targets = ['messages', 'file', 'preview', 'previewImage'];

    async connect() {
        this.scrollToBottom(true);

        this.lastMessageId = this.currentLastMessageId();

        const root = this.element.closest('[data-controller~="live"]');
        if (!root) {
            return;
        }

        this.component = await getComponent(root);
        this.onRender = () => this.afterRender();
        this.component.on('render:finished', this.onRender);
    }

    disconnect() {
        if (this.component && this.onRender) {
            this.component.off('render:finished', this.onRender);
        }

        this.revokePreview();
    }

    afterRender() {
        // Live Component vide le champ fichier après l'envoi : on retire l'aperçu.
        if (this.hasFileTarget && !this.fileTarget.files.length) {
            this.clearImage();
        }

        const lastId = this.currentLastMessageId();

        if (lastId !== this.lastMessageId) {
            this.lastMessageId = lastId;
            this.scrollToBottom(false);
        }
    }

    preview() {
        const file = this.fileTarget.files[0];

        this.revokePreview();

        if (!file) {
            this.previewTarget.classList.add('d-none');
            return;
        }

        this.previewUrl = URL.createObjectURL(file);
        this.previewImageTarget.src = this.previewUrl;
        this.previewTarget.classList.remove('d-none');
    }

    clearImage() {
        if (this.hasFileTarget) {
            this.fileTarget.value = '';
        }

        this.revokePreview();

        if (this.hasPreviewTarget) {
            this.previewTarget.classList.add('d-none');
        }
    }

    revokePreview() {
        if (this.previewUrl) {
            URL.revokeObjectURL(this.previewUrl);
            this.previewUrl = null;
        }
    }

    currentLastMessageId() {
        if (!this.hasMessagesTarget) {
            return null;
        }

        const messages = this.messagesTarget.querySelectorAll('[id^="message-"]');

        return messages.length ? messages[messages.length - 1].id : null;
    }

    scrollToBottom(instant) {
        if (!this.hasMessagesTarget) {
            return;
        }

        this.messagesTarget.scrollTo({
            top: this.messagesTarget.scrollHeight,
            behavior: instant ? 'auto' : 'smooth',
        });
    }
}
