import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this.continueButton = this.element.querySelector('[data-documents-continue]');
        this.documentConfirmation = this.element.querySelector('[data-document-confirmation]');
        this.documentConfirmationLink = this.element.querySelector('[data-document-confirmation-link]');
        this.documentLimitMessage = this.element.querySelector('[data-document-limit-message]');
        this.forms = [...this.element.querySelectorAll('[data-document-upload-form]')];
        this.cleanups = [];

        if (!this.continueButton || !this.forms.length) {
            return;
        }

        this.bindConfirmationLink();
        this.bindForms();
        this.bindContinueButton();
        this.updateContinueButton();
    }

    disconnect() {
        this.cleanups?.forEach((cleanup) => cleanup());
        this.cleanups = [];
    }

    bindConfirmationLink() {
        if (!this.documentConfirmationLink) {
            return;
        }

        const listener = (event) => {
            event.preventDefault();
            this.restoreEditableUploadedDocuments();
        };

        this.documentConfirmationLink.addEventListener('click', listener);
        this.cleanups.push(() => this.documentConfirmationLink.removeEventListener('click', listener));
    }

    restoreEditableUploadedDocuments() {
        this.forms.forEach((form) => {
            if (form.dataset.uploaded !== 'true') {
                return;
            }

            const elements = this.getFormElements(form);

            form.dataset.uploaded = 'false';
            form.dataset.submissionStatus = '';
            form.dataset.submittedFileName = '';
            elements.input.disabled = false;
            elements.status.textContent = 'Téléverser';
            elements.formats.textContent = elements.input.files[0]?.name || elements.formats.dataset.defaultFormats;
            elements.formats.hidden = false;
            elements.uploadLabel.hidden = Boolean(elements.input.files.length);
            elements.removeFile.hidden = !elements.input.files.length;
            this.setSubmittedFileDisplay(elements.removeFile, 'Supprimer');
            elements.removeFile.classList.remove('finish-setup__remove-file--uploaded');
            elements.approvedStatus.hidden = true;
            elements.rejectedStatus.hidden = true;
        });

        this.continueButton.hidden = false;
        this.documentConfirmation?.replaceWith(this.continueButton);
        this.updateContinueButton();
        this.forms[0]?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    bindForms() {
        this.forms.forEach((form) => {
            const elements = this.getFormElements(form);

            const changeListener = () => {
                this.handleInputChange(form, elements);
            };

            const removeListener = () => {
                this.handleRemoveFile(elements);
            };

            elements.input.addEventListener('change', changeListener);
            elements.removeFile.addEventListener('click', removeListener);

            this.cleanups.push(() => elements.input.removeEventListener('change', changeListener));
            this.cleanups.push(() => elements.removeFile.removeEventListener('click', removeListener));
        });
    }

    handleInputChange(form, elements) {
        elements.errorFlash.hidden = true;

        if (elements.input.files.length) {
            form.dataset.uploaded = 'false';
            elements.formats.textContent = elements.input.files[0].name;
            elements.formats.hidden = false;
            elements.uploadLabel.hidden = true;
            elements.removeFile.hidden = false;
            this.setSubmittedFileDisplay(elements.removeFile, 'Supprimer');
            elements.removeFile.classList.remove('finish-setup__remove-file--uploaded');
            elements.approvedStatus.hidden = true;
            elements.rejectedStatus.hidden = true;
        } else {
            const submittedFileName = form.dataset.submittedFileName || '';
            const submissionStatus = form.dataset.submissionStatus || '';

            elements.status.textContent = submittedFileName && submissionStatus !== 'rejected'
                ? 'Envoyé'
                : 'Téléverser';
            elements.formats.textContent = elements.formats.dataset.defaultFormats;
            elements.formats.hidden = Boolean(submittedFileName && submissionStatus !== 'rejected');
            elements.uploadLabel.hidden = Boolean(submittedFileName && submissionStatus !== 'rejected');
            elements.removeFile.hidden = !submittedFileName;
            this.setSubmittedFileDisplay(
                elements.removeFile,
                submittedFileName || 'Supprimer',
                Boolean(submittedFileName && submissionStatus === 'pending')
            );
            elements.removeFile.classList.toggle('finish-setup__remove-file--uploaded', Boolean(submittedFileName));
            elements.approvedStatus.hidden = submissionStatus !== 'approved';
            elements.rejectedStatus.hidden = submissionStatus !== 'rejected';
        }

        this.updateContinueButton();
    }

    handleRemoveFile(elements) {
        if (
            elements.input.disabled
            || (elements.removeFile.classList.contains('finish-setup__remove-file--uploaded') && !elements.input.files.length)
        ) {
            return;
        }

        elements.input.value = '';
        elements.input.dispatchEvent(new Event('change'));
    }

    bindContinueButton() {
        const listener = async () => {
            await this.handleContinue();
        };

        this.continueButton.addEventListener('click', listener);
        this.cleanups.push(() => this.continueButton.removeEventListener('click', listener));
    }

    async handleContinue() {
        const formsToUpload = this.forms.filter((form) => {
            const input = form.querySelector('[data-document-upload]');

            return input.files.length && form.dataset.uploaded !== 'true';
        });

        if (!formsToUpload.length) {
            return;
        }

        this.continueButton.disabled = true;
        this.continueButton.textContent = 'Téléversement en cours…';

        try {
            const adminNotificationDocumentIds = [...new Set(this.forms
                .filter((form) => {
                    const input = form.querySelector('[data-document-upload]');

                    return (input.files.length && form.dataset.uploaded !== 'true')
                        || (form.dataset.uploaded === 'true' && form.dataset.submissionStatus === 'pending');
                })
                .map((form) => this.getRequiredDocumentId(form))
                .filter(Boolean)
            )];

            for (const [index, form] of formsToUpload.entries()) {
                const result = await this.uploadForm(form, {
                    notifyAdmin: index === formsToUpload.length - 1,
                    adminNotificationDocumentIds,
                });

                this.continueButton.dataset.documentsComplete = result.documentsComplete ? 'true' : 'false';
            }

            if (this.continueButton.dataset.documentsComplete === 'true') {
                this.element.remove();
                return;
            }

            if (this.documentConfirmation) {
                this.documentConfirmation.hidden = false;
                this.continueButton.replaceWith(this.documentConfirmation);
            } else {
                this.continueButton.remove();
            }
        } catch (error) {
            if (error.submissionLimitReached && this.documentLimitMessage) {
                this.documentLimitMessage.hidden = false;
            }
        } finally {
            this.continueButton.textContent = 'Continuer';
            this.updateContinueButton();
        }
    }

    async uploadForm(form, options = {}) {
        const elements = this.getFormElements(form);
        const formData = new FormData(form);
        const originalFileName = elements.input.files[0]?.name ?? '';

        if (options.notifyAdmin === true) {
            formData.append('notifyAdmin', '1');

            (options.adminNotificationDocumentIds || []).forEach((requiredDocumentId) => {
                formData.append('adminNotificationRequiredDocumentIds[]', requiredDocumentId);
            });
        }

        elements.input.disabled = true;
        elements.status.textContent = 'Téléversement en cours…';

        try {
            const response = await fetch(form.action, {
                method: form.method,
                body: formData,
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const result = await response.json().catch(() => ({
                success: false,
                message: 'Le serveur n’a pas pu traiter ce document. Veuillez réessayer.'
            }));

            if (!response.ok || !result.success) {
                const uploadError = new Error(result.message || 'Le téléversement a échoué.');
                uploadError.submissionLimitReached = Boolean(result.submissionLimitReached);

                throw uploadError;
            }

            form.dataset.uploaded = 'true';
            form.dataset.submissionStatus = 'pending';

            const uploadedFileName = result.originalFileName || originalFileName;

            form.dataset.submittedFileName = uploadedFileName;
            elements.status.textContent = 'Envoyé';
            elements.formats.hidden = true;
            elements.errorFlash.hidden = true;
            this.setSubmittedFileDisplay(elements.removeFile, uploadedFileName, true);
            elements.removeFile.hidden = false;
            elements.removeFile.classList.add('finish-setup__remove-file--uploaded');
            elements.approvedStatus.hidden = true;
            elements.rejectedStatus.hidden = true;

            return result;
        } catch (error) {
            elements.input.disabled = false;
            elements.status.textContent = error.message || 'Erreur de téléversement';
            elements.errorFlash.textContent = error.message || 'Erreur de téléversement';
            elements.errorFlash.hidden = false;

            throw error;
        }
    }

    updateContinueButton() {
        const allRequiredDocumentsSelected = this.forms
            .filter((form) => form.dataset.requiredDocument === 'true')
            .every((form) => this.isSatisfied(form));

        this.continueButton.disabled = !allRequiredDocumentsSelected;
    }

    isSatisfied(form) {
        const input = form.querySelector('[data-document-upload]');

        return form.dataset.uploaded === 'true' || Boolean(input?.files?.length);
    }

    getRequiredDocumentId(form) {
        return form.querySelector('[name="ask_documents[requiredDocument]"]')?.value || '';
    }

    getFormElements(form) {
        return {
            input: form.querySelector('[data-document-upload]'),
            status: form.querySelector('[data-upload-status]'),
            formats: form.parentElement.querySelector('[data-document-formats]'),
            uploadLabel: form.querySelector('.finish-setup__upload'),
            removeFile: form.querySelector('[data-remove-file]'),
            errorFlash: form.parentElement.querySelector('[data-document-upload-error]'),
            approvedStatus: form.querySelector('.finish-setup__document-approved'),
            rejectedStatus: form.querySelector('.finish-setup__document-rejected'),
        };
    }

    setSubmittedFileDisplay(removeFile, fileName, sent = false) {
        const submittedFileName = removeFile.querySelector('[data-submitted-file-name]');
        const sentStatus = removeFile.querySelector('[data-sent-status]');

        if (submittedFileName) {
            submittedFileName.textContent = fileName || 'Supprimer';
        } else {
            removeFile.textContent = fileName || 'Supprimer';
        }

        if (sentStatus) {
            sentStatus.hidden = !sent;
        }
    }
}
