import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
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

        this.enhanceTimeSelects();
    }

    disconnect() {
        document.removeEventListener('click', this.onDocumentClick);
        document.removeEventListener('keydown', this.onDocumentKeydown);
    }

    toggle(event) {
        const day = event.currentTarget.closest('[data-opening-hours-day]');

        if (!day) {
            return;
        }

        if (event.currentTarget.checked) {
            this.openDay(day, true);
        } else {
            this.closeDay(day, true);
        }

        this.syncTimeTriggers(day);
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

        secondSlot.querySelectorAll('input[type="time"], select.js-opening-time').forEach((input) => {
            input.disabled = false;
        });

        if (addButton) {
            addButton.hidden = true;
        }

        if (removeButton) {
            removeButton.hidden = false;
        }

        this.syncTimeTriggers(day);
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

        secondSlot.querySelectorAll('input[type="time"], select.js-opening-time').forEach((input) => {
            input.value = '';
            input.disabled = true;
        });

        if (addButton) {
            addButton.hidden = false;
        }

        if (removeButton) {
            removeButton.hidden = true;
        }

        this.syncTimeTriggers(day);
    }

    // ============================================================
    // LISTE D'HEURES (même design que les résultats de recherche de la homepage)
    // Le <select> reste la source des valeurs ; il est masqué et piloté par la liste.
    // ============================================================

    enhanceTimeSelects() {
        const selects = this.element.querySelectorAll('select.js-opening-time');

        if (selects.length === 0) {
            return;
        }

        selects.forEach((select) => {
            const wrapper = document.createElement('div');
            wrapper.className = 'opening-time';

            const trigger = document.createElement('button');
            trigger.type = 'button';
            trigger.className = 'opening-time__trigger';
            trigger.addEventListener('click', () => this.toggleTimeList(wrapper));

            const list = document.createElement('ul');
            list.className = 'opening-time__results';
            list.hidden = true;

            select.parentNode.insertBefore(wrapper, select);
            wrapper.append(select, trigger, list);
        });

        this.onDocumentClick = (event) => {
            if (!event.target.closest('.opening-time')) {
                this.closeTimeLists();
            }
        };

        this.onDocumentKeydown = (event) => {
            if (event.key === 'Escape') {
                this.closeTimeLists();
            }
        };

        document.addEventListener('click', this.onDocumentClick);
        document.addEventListener('keydown', this.onDocumentKeydown);

        this.syncTimeTriggers(this.element);
    }

    toggleTimeList(wrapper) {
        const list = wrapper.querySelector('.opening-time__results');
        const isOpen = !list.hidden;

        this.closeTimeLists();

        if (isOpen) {
            return;
        }

        const select = wrapper.querySelector('select');

        list.replaceChildren(...Array.from(select.options)
            .filter((option) => option.value !== '')
            .map((option) => {
                const item = document.createElement('li');
                item.className = 'opening-time__result';
                item.textContent = option.text;
                item.classList.toggle('is-selected', option.value === select.value);

                item.addEventListener('click', () => {
                    select.value = option.value;
                    select.dispatchEvent(new Event('input', { bubbles: true }));
                    select.dispatchEvent(new Event('change', { bubbles: true }));

                    this.syncTimeTriggers(wrapper);
                    this.closeTimeLists();
                });

                return item;
            }));

        list.hidden = false;

        // Fait défiler la liste (pas la page) jusqu'à l'heure sélectionnée.
        const selected = list.querySelector('.is-selected');

        if (selected) {
            list.scrollTop = selected.offsetTop - (list.clientHeight - selected.offsetHeight) / 2;
        }
    }

    closeTimeLists() {
        this.element.querySelectorAll('.opening-time__results').forEach((list) => {
            list.hidden = true;
        });
    }

    syncTimeTriggers(root) {
        root.querySelectorAll('.opening-time').forEach((wrapper) => {
            const select = wrapper.querySelector('select');
            const trigger = wrapper.querySelector('.opening-time__trigger');

            trigger.textContent = select.value || '--:--';
            trigger.disabled = select.disabled;
        });
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

            firstSlot.querySelectorAll('input[type="time"], select.js-opening-time').forEach((input) => {
                input.disabled = false;
            });
        }

        if (secondSlot) {
            secondSlot.hidden = !showSecondSlot;

            secondSlot.querySelectorAll('input[type="time"], select.js-opening-time').forEach((input) => {
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

            firstSlot.querySelectorAll('input[type="time"], select.js-opening-time').forEach((input) => {
                if (clearValues) {
                    input.value = '';
                }

                input.disabled = true;
            });
        }

        if (secondSlot) {
            secondSlot.hidden = true;

            secondSlot.querySelectorAll('input[type="time"], select.js-opening-time').forEach((input) => {
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
        const timeInputs = day.querySelectorAll('input[type="time"], select.js-opening-time');

        if (toggle) {
            toggle.checked = this.isTruthy(values.isOpen);
        }

        this.setTimeValue(timeInputs[0], values.ouvertureMatin);
        this.setTimeValue(timeInputs[1], values.fermetureMatin);
        this.setTimeValue(timeInputs[2], values.ouvertureApresMidi);
        this.setTimeValue(timeInputs[3], values.fermetureApresMidi);
    }

    setTimeValue(input, value) {
        if (!input) {
            return;
        }

        const time = value || '';

        // Une heure enregistrée hors pas de 30 min (ex. 09:15) est ajoutée au select pour ne pas être perdue.
        if (input.tagName === 'SELECT' && time && !Array.from(input.options).some((option) => option.value === time)) {
            input.add(new Option(time, time));
        }

        input.value = time;
    }

    refreshDayState(day) {
        const toggle = day.querySelector('.js-opening-toggle');
        const timeInputs = day.querySelectorAll('input[type="time"], select.js-opening-time');
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
