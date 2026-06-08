import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'tab',
        'panel',
        'track',
    ];

    changeTab(event) {
        const activeTabName = event.currentTarget.dataset.tabName;

        this.tabTargets.forEach((tab) => {
            tab.classList.toggle('active', tab.dataset.tabName === activeTabName);
        });

        this.panelTargets.forEach((panel) => {
            panel.classList.toggle('is-active', panel.dataset.tabPanel === activeTabName);
        });
    }

    slide(event) {
        const trackName = event.params.track;
        const direction = event.params.direction || 'next';

        const track = this.trackTargets.find((item) => {
            return item.dataset.sliderTrack === trackName;
        });

        if (!track) {
            return;
        }

        const card = track.querySelector('.property-card');

        if (!card) {
            return;
        }

        const gap = this.getTrackGap(track);
        const scrollAmount = card.getBoundingClientRect().width + gap;

        track.scrollBy({
            left: direction === 'prev' ? -scrollAmount : scrollAmount,
            behavior: 'smooth',
        });
    }

    getTrackGap(track) {
        const styles = window.getComputedStyle(track);
        const gap = parseFloat(styles.columnGap || styles.gap || '20');

        return Number.isNaN(gap) ? 20 : gap;
    }
}