/*
 * Traductions côté JavaScript.
 *
 * Le dictionnaire est injecté par templates/composant/_js_translations.html.twig
 * (balise <script id="boolts-js-translations" type="application/json">) dans base.html.twig,
 * déjà traduit dans la langue de la page. Les paramètres s'écrivent %nom%.
 */
let dictionary = null;

function load() {
    if (dictionary !== null) {
        return dictionary;
    }

    try {
        const element = document.getElementById('boolts-js-translations');
        dictionary = element ? JSON.parse(element.textContent || '{}') : {};
    } catch (error) {
        dictionary = {};
    }

    return dictionary;
}

export function t(key, params = {}) {
    let text = load()[key] ?? key;

    Object.entries(params).forEach(([name, value]) => {
        text = text.split(`%${name}%`).join(String(value));
    });

    return text;
}
