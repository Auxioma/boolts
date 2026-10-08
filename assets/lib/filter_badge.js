// assets/lib/filter_badge.js

/**
 * Compte les champs de la modale de filtres remplis par l'utilisateur.
 * Un champ multiple (ex. typeDePropriete[], dpe[]) compte pour 1.
 * Les champs de saisie d'auto-complétion (*Search) et les valeurs vides
 * (ex. nature « Tous », localisation « [] ») sont ignorés.
 */
export function countActiveFilters(searchParams) {
    const fields = new Set();

    searchParams.forEach((value, key) => {
        const match = key.match(/^modal_filter\[([^\]]+)\]/);

        if (!match || match[1].endsWith('Search')) {
            return;
        }

        const trimmed = String(value).trim();

        if (trimmed === '' || trimmed === '[]') {
            return;
        }

        fields.add(match[1]);
    });

    return fields.size;
}

/**
 * Passe le bouton de filtres en état actif et affiche la pastille du nombre de filtres.
 */
export function renderFilterBadge(button, count) {
    let badge = button.querySelector('.boolts-filter-badge');

    if (count > 0) {
        if (!badge) {
            badge = document.createElement('span');
            badge.className = 'boolts-filter-badge';
            badge.setAttribute('aria-hidden', 'true');
            button.prepend(badge);
        }

        badge.textContent = count;
    } else if (badge) {
        badge.remove();
    }

    button.classList.toggle('boolts-filter-active', count > 0);
}
