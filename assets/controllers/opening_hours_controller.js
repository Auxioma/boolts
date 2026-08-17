import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        initial: Object
    }

    connect() {
        const days = [
            'lundi',
            'mardi',
            'mercredi',
            'jeudi',
            'vendredi',
            'samedi',
            'dimanche',
        ];

        this.element.querySelectorAll('[data-opening-hours-day]').forEach((day, index) => {
            const dayKey = days[index];

            if (dayKey && this.hasInitialValue && this.initialValue[dayKey]) {
                this.applyDayValues(day, this.initialValue[dayKey]);
            }

            this.refreshDayState(day);
        });
    }

    toggle(event) {
        const day = event.currentTarget.closest('[data-opening-hours-day]');

        if (!day) {
            return;
        }

        if (event.currentTarget.checked) {
            this.openDay(day, true);
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

    openDay(day, clearHiddenSecondSlot = true) {
        this.showDayOpen(day, false, clearHiddenSecondSlot);
    }

    showDayOpen(day, showSecondSlot = false, clearHiddenSecondSlot = false) {
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
            secondSlot.hidden = !showSecondSlot;

            secondSlot.querySelectorAll('input[type="time"]').forEach((input) => {
                if (!showSecondSlot && clearHiddenSecondSlot) {
                    input.value = '';
                }

                input.disabled = !showSecondSlot;
            });
        }

        if (actions) {
            actions.hidden = false;
        }

        if (addButton) {
            addButton.hidden = showSecondSlot;
        }

        if (removeButton) {
            removeButton.hidden = !showSecondSlot;
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

    applyDayValues(day, values) {
        const toggle = day.querySelector('.js-opening-toggle');
        const timeInputs = day.querySelectorAll('input[type="time"]');

        if (toggle) {
            toggle.checked = this.isTruthy(values.isOpen);
        }

        if (timeInputs[0]) {
            timeInputs[0].value = values.ouvertureMatin || '';
        }

        if (timeInputs[1]) {
            timeInputs[1].value = values.fermetureMatin || '';
        }

        if (timeInputs[2]) {
            timeInputs[2].value = values.ouvertureApresMidi || '';
        }

        if (timeInputs[3]) {
            timeInputs[3].value = values.fermetureApresMidi || '';
        }
    }

    refreshDayState(day) {
        const toggle = day.querySelector('.js-opening-toggle');
        const timeInputs = day.querySelectorAll('input[type="time"]');
        const hasAnyTime = Array.from(timeInputs).some((input) => input.value.trim() !== '');
        const hasSecondSlot = Boolean(
            timeInputs[2]?.value.trim() || timeInputs[3]?.value.trim()
        );

        if ((toggle && toggle.checked) || hasAnyTime) {
            if (toggle) {
                toggle.checked = true;
            }

            this.showDayOpen(day, hasSecondSlot, false);
            return;
        }

        this.closeDay(day, false);
    }

    isTruthy(value) {
        return value === true
            || value === 1
            || value === '1'
            || value === 'true';
    }
}
