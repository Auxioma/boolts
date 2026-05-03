import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        type: String,
        src: String,
        poster: String,
    }

    connect() {
        if (!this.srcValue) {
            return;
        }

        if (this.typeValue === 'video') {
            this.createVideo();
            return;
        }

        if (this.typeValue === 'image') {
            this.createImage();
        }
    }

    createVideo() {
        const video = document.createElement('video');

        video.src = this.srcValue;
        video.autoplay = true;
        video.muted = true;
        video.loop = true;
        video.playsInline = true;

        if (this.hasPosterValue) {
            video.poster = this.posterValue;
        }

        video.classList.add('hero-media');

        this.element.prepend(video);

        video.play().catch(() => {
            // autoplay bloqué
        });
    }

    createImage() {
        this.element.style.backgroundImage = `url("${this.srcValue}")`;
        this.element.style.backgroundSize = 'cover';
        this.element.style.backgroundPosition = 'center';
        this.element.style.backgroundRepeat = 'no-repeat';
    }
}
