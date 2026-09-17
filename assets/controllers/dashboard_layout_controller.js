import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['header', 'footer'];

    connect() {
        this.resizeObserver = new ResizeObserver(() => this.updateLayout());
        this.handleTurboLoad = () => this.updateLayout();

        document.addEventListener('turbo:load', this.handleTurboLoad);
        this.resizeObserver.observe(this.element);

        if (this.hasHeaderTarget) {
            this.resizeObserver.observe(this.headerTarget);
        }

        if (this.hasFooterTarget) {
            this.resizeObserver.observe(this.footerTarget);
        }

        this.updateLayout();
    }

    disconnect() {
        document.removeEventListener('turbo:load', this.handleTurboLoad);
        this.resizeObserver?.disconnect();
    }

    updateLayout() {
        const dashboard = document.querySelector('#dashboard');

        if (!dashboard) {
            return;
        }

        const headerHeight = this.hasHeaderTarget
            ? this.headerTarget.getBoundingClientRect().height
            : 0;
        const footerHeight = this.hasFooterTarget
            ? this.footerTarget.getBoundingClientRect().height
            : 0;
        const stickyTop = Math.ceil(headerHeight);
        const availableHeight = Math.max(window.innerHeight - stickyTop - 16, 160);

        dashboard.style.setProperty('--dashboard-sticky-top', `${stickyTop}px`);
        dashboard.style.setProperty('--dashboard-sidebar-max-height', `${availableHeight}px`);
        dashboard.style.setProperty('--dashboard-footer-height', `${Math.ceil(footerHeight)}px`);
    }
}
