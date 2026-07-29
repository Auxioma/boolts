import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['countryStep', 'documentsStep', 'country', 'selectedCountry'];

    continue() {
        if (!this.countryTarget.value) {
            this.countryTarget.focus();

            return;
        }

        this.selectedCountryTarget.textContent = this.countryTarget.selectedOptions[0]?.textContent?.trim() ?? '';
        this.countryStepTarget.hidden = true;
        this.documentsStepTarget.hidden = false;
    }

    finishDocuments() {
        this.documentsStepTarget.hidden = true;
    }
}
