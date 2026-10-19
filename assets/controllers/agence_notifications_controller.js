import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['filterButton', 'card', 'unreadCount'];
    static values = { csrfToken: String };

    filter(event) {
        const current = event.currentTarget;

        this.filterButtonTargets.forEach((button) => {
            button.classList.toggle('active', button === current);
        });

        this.applyFilter(current.dataset.filter);
    }

    markRead(event) {
        const card = event.currentTarget;
        const url = card.dataset.markReadUrl;

        if (!url || card.dataset.read === 'true' || card.dataset.marking === 'true') {
            return;
        }

        card.dataset.marking = 'true';

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': this.csrfTokenValue,
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then((response) => (response.ok ? response.json() : Promise.reject(response)))
            .then((data) => {
                card.dataset.read = 'true';
                card.classList.add('notification-card--read');
                card.querySelector('.js-notification-dot')?.remove();

                this.setUnreadCount(data?.unreadCount);

                if (this.activeFilter() === 'unread') {
                    card.hidden = true;
                }
            })
            .catch(() => {})
            .finally(() => {
                delete card.dataset.marking;
            });
    }

    activeFilter() {
        const active = this.filterButtonTargets.find((button) => button.classList.contains('active'));

        return active ? active.dataset.filter : 'all';
    }

    applyFilter(filter) {
        this.cardTargets.forEach((card) => {
            card.hidden = filter === 'unread' && card.dataset.read === 'true';
        });
    }

    setUnreadCount(count) {
        if (this.hasUnreadCountTarget && typeof count === 'number' && !Number.isNaN(count)) {
            this.unreadCountTarget.textContent = String(Math.max(0, count));
        }
    }
}
