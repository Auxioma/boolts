import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this.element.querySelectorAll('[data-opening-hours-day]').forEach((day) => {
            this.closeDay(day, false);
        });
    }

    toggle(event) {
        const day = event.currentTarget.closest('[data-opening-hours-day]');

        if (!day) {
            return;
        }

        if (event.currentTarget.checked) {
            this.openDay(day);
            return;
        }

        this.closeDay(day, true);
    }

    add(event) {
        const day = event.currentTarget.closest('[data-opening-hours-day]');

        if (!day) {
            return;
        }

        const secondSlot = day.querySelector('.js-opening-second');
        const addButton = day.querySelector('.js-opening-add');
        const removeButton = day.querySelector('.js-opening-remove');

        if (!secondSlot) {
            return;
        }

        secondSlot.hidden = false;

        secondSlot.querySelectorAll('input[type="time"]').forEach((input) => {
            input.disabled = false;
        });

        if (addButton) {
            addButton.hidden = true;
        }

        if (removeButton) {
            removeButton.hidden = false;
        }
    }

    remove(event) {
        const day = event.currentTarget.closest('[data-opening-hours-day]');

        if (!day) {
            return;
        }

        const secondSlot = day.querySelector('.js-opening-second');
        const addButton = day.querySelector('.js-opening-add');
        const removeButton = day.querySelector('.js-opening-remove');

        if (!secondSlot) {
            return;
        }

        secondSlot.hidden = true;

        secondSlot.querySelectorAll('input[type="time"]').forEach((input) => {
            input.value = '';
            input.disabled = true;
        });

        if (addButton) {
            addButton.hidden = false;
        }

        if (removeButton) {
            removeButton.hidden = true;
        }
    }

    openDay(day) {
        const status = day.querySelector('.js-opening-status');
        const firstSlot = day.querySelector('.js-opening-first');
        const secondSlot = day.querySelector('.js-opening-second');
        const actions = day.querySelector('.opening-day__actions');
        const addButton = day.querySelector('.js-opening-add');
        const removeButton = day.querySelector('.js-opening-remove');

        day.classList.remove('is-closed');

        if (status) {
            status.textContent = 'Ouvert';
        }

        if (firstSlot) {
            firstSlot.hidden = false;

            firstSlot.querySelectorAll('input[type="time"]').forEach((input) => {
                input.disabled = false;
            });
        }

        if (secondSlot) {
            secondSlot.hidden = true;

            secondSlot.querySelectorAll('input[type="time"]').forEach((input) => {
                input.value = '';
                input.disabled = true;
            });
        }

        if (actions) {
            actions.hidden = false;
        }

        if (addButton) {
            addButton.hidden = false;
        }

        if (removeButton) {
            removeButton.hidden = true;
        }
    }

    closeDay(day, clearValues = true) {
        const toggle = day.querySelector('.js-opening-toggle');
        const status = day.querySelector('.js-opening-status');
        const firstSlot = day.querySelector('.js-opening-first');
        const secondSlot = day.querySelector('.js-opening-second');
        const actions = day.querySelector('.opening-day__actions');
        const addButton = day.querySelector('.js-opening-add');
        const removeButton = day.querySelector('.js-opening-remove');

        day.classList.add('is-closed');

        if (toggle) {
            toggle.checked = false;
        }

        if (status) {
            status.textContent = 'Fermé';
        }

        if (firstSlot) {
            firstSlot.hidden = true;

            firstSlot.querySelectorAll('input[type="time"]').forEach((input) => {
                if (clearValues) {
                    input.value = '';
                }

                input.disabled = true;
            });
        }

        if (secondSlot) {
            secondSlot.hidden = true;

            secondSlot.querySelectorAll('input[type="time"]').forEach((input) => {
                if (clearValues) {
                    input.value = '';
                }

                input.disabled = true;
            });
        }

        if (actions) {
            actions.hidden = true;
        }

        if (addButton) {
            addButton.hidden = false;
        }

        if (removeButton) {
            removeButton.hidden = true;
        }
    }
}
