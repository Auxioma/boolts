// assets/lib/trackpad_swipe.js

import Splide from '@splidejs/splide';

/**
 * Balayage horizontal au trackpad (Mac) ou Maj + molette : appelle onSwipe(1) pour
 * avancer, onSwipe(-1) pour reculer. Un geste = un seul déplacement, inertie comprise.
 * Le défilement vertical n'est pas intercepté (la page continue de scroller).
 * Le tactile (iPhone) reste géré par le slider lui-même.
 *
 * onSwipe peut retourner false si le slider n'a pas pu bouger (transition en cours) :
 * le geste reste alors actif et réessaie aux événements suivants.
 *
 * Retourne une fonction qui retire l'écouteur.
 */
export function bindTrackpadSwipe(element, onSwipe, { threshold = 30, idleDelay = 180 } = {}) {
    let accumulated = 0;
    let locked = false;
    let lockedDirection = 0;
    let peak = 0;
    let trough = Infinity;
    let decaying = false;
    let idleTimer = null;

    const reset = () => {
        accumulated = 0;
        locked = false;
        lockedDirection = 0;
        peak = 0;
        trough = Infinity;
        decaying = false;
    };

    // L'inertie macOS envoie des deltas décroissants pendant 1 à 2 s : sans pause, le
    // délai d'inactivité ne suffit pas à séparer deux gestes. Un nouveau geste se
    // reconnaît à un delta qui remonte nettement après la décroissance, ou à un
    // changement de sens.
    const isNewGesture = (delta) => {
        const size = Math.abs(delta);

        if (Math.sign(delta) !== lockedDirection && size >= 4) {
            return true;
        }

        peak = Math.max(peak, size);

        if (!decaying) {
            decaying = size < peak * 0.5;
            trough = size;
            return false;
        }

        if (size > trough * 2 && size - trough >= 8) {
            return true;
        }

        trough = Math.min(trough, size);

        return false;
    };

    const handleWheel = (event) => {
        if (Math.abs(event.deltaX) <= Math.abs(event.deltaY)) {
            return;
        }

        // Bloque aussi le geste « page précédente / suivante » du navigateur.
        event.preventDefault();

        // Le geste (et son inertie) est terminé quand les événements cessent.
        clearTimeout(idleTimer);
        idleTimer = setTimeout(reset, idleDelay);

        if (locked) {
            if (!isNewGesture(event.deltaX)) {
                return;
            }

            reset();
        }

        accumulated += event.deltaX;

        if (Math.abs(accumulated) >= threshold) {
            const direction = accumulated > 0 ? 1 : -1;

            if (onSwipe(direction) === false) {
                return;
            }

            locked = true;
            lockedDirection = direction;
        }
    };

    element.addEventListener('wheel', handleWheel, { passive: false });

    return () => {
        clearTimeout(idleTimer);
        element.removeEventListener('wheel', handleWheel);
    };
}

/**
 * Callback onSwipe pour un slider Splide : Splide ignore go() pendant une transition,
 * on le signale pour que le geste ne soit pas perdu.
 */
export function splideSwipe(splide) {
    return (direction) => {
        if (splide.state.is([Splide.STATES.MOVING, Splide.STATES.SCROLLING])) {
            return false;
        }

        splide.go(direction > 0 ? '>' : '<');
    };
}
