// assets/lib/trackpad_swipe.js

/**
 * Balayage horizontal au trackpad (Mac) ou Maj + molette : appelle onSwipe(1) pour
 * avancer, onSwipe(-1) pour reculer. Un geste = un seul déplacement, inertie comprise.
 * Le défilement vertical n'est pas intercepté (la page continue de scroller).
 * Le tactile (iPhone) reste géré par le slider lui-même.
 *
 * Retourne une fonction qui retire l'écouteur.
 */
export function bindTrackpadSwipe(element, onSwipe, { threshold = 30, idleDelay = 180 } = {}) {
    let accumulated = 0;
    let locked = false;
    let idleTimer = null;

    const handleWheel = (event) => {
        if (Math.abs(event.deltaX) <= Math.abs(event.deltaY)) {
            return;
        }

        // Bloque aussi le geste « page précédente / suivante » du navigateur.
        event.preventDefault();

        // Le geste (et son inertie) est terminé quand les événements cessent.
        clearTimeout(idleTimer);
        idleTimer = setTimeout(() => {
            accumulated = 0;
            locked = false;
        }, idleDelay);

        if (locked) {
            return;
        }

        accumulated += event.deltaX;

        if (Math.abs(accumulated) >= threshold) {
            locked = true;
            onSwipe(accumulated > 0 ? 1 : -1);
        }
    };

    element.addEventListener('wheel', handleWheel, { passive: false });

    return () => {
        clearTimeout(idleTimer);
        element.removeEventListener('wheel', handleWheel);
    };
}
