import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this.whatsappSwitch = this.element.querySelector('#whatsapp-switch');
        this.checkboxWrapper = this.element.querySelector('#whatsapp-checkbox-wrapper');
        this.sameNumberCheckbox = this.element.querySelector('#same-number-checkbox');
        this.whatsappPhoneWrapper = this.element.querySelector('#whatsapp-phone-wrapper');
        this.whatsappPhoneInput = this.element.querySelector('#whatsapp-phone');
        this.telephoneInput = this.element.querySelector('#telephone-input');

        if (
            !this.whatsappSwitch
            || !this.checkboxWrapper
            || !this.sameNumberCheckbox
            || !this.whatsappPhoneWrapper
            || !this.whatsappPhoneInput
            || !this.telephoneInput
        ) {
            return;
        }

        this.switchListener = () => {
            this.toggleWhatsapp();
        };

        this.sameNumberListener = () => {
            this.toggleSameNumber();
        };

        this.telephoneListener = () => {
            this.syncTelephone();
        };

        this.whatsappSwitch.addEventListener('change', this.switchListener);
        this.sameNumberCheckbox.addEventListener('change', this.sameNumberListener);
        this.telephoneInput.addEventListener('input', this.telephoneListener);
    }

    disconnect() {
        if (this.whatsappSwitch && this.switchListener) {
            this.whatsappSwitch.removeEventListener('change', this.switchListener);
        }

        if (this.sameNumberCheckbox && this.sameNumberListener) {
            this.sameNumberCheckbox.removeEventListener('change', this.sameNumberListener);
        }

        if (this.telephoneInput && this.telephoneListener) {
            this.telephoneInput.removeEventListener('input', this.telephoneListener);
        }
    }

    toggleWhatsapp() {
        if (this.whatsappSwitch.checked) {
            this.checkboxWrapper.classList.remove('d-none');
            this.sameNumberCheckbox.checked = true;
            this.whatsappPhoneWrapper.classList.add('d-none');
            this.whatsappPhoneInput.value = this.telephoneInput.value;
        } else {
            this.checkboxWrapper.classList.add('d-none');
            this.whatsappPhoneWrapper.classList.add('d-none');
            this.sameNumberCheckbox.checked = false;
            this.whatsappPhoneInput.value = '';
        }
    }

    toggleSameNumber() {
        if (this.sameNumberCheckbox.checked) {
            this.whatsappPhoneWrapper.classList.add('d-none');
            this.whatsappPhoneInput.value = this.telephoneInput.value;
        } else {
            this.whatsappPhoneWrapper.classList.remove('d-none');
            this.whatsappPhoneInput.value = '';
            this.whatsappPhoneInput.focus();
        }
    }

    syncTelephone() {
        if (this.whatsappSwitch.checked && this.sameNumberCheckbox.checked) {
            this.whatsappPhoneInput.value = this.telephoneInput.value;
        }
    }
}
