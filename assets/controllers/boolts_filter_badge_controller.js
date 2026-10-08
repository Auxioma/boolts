// assets/controllers/boolts_filter_badge_controller.js

import { Controller } from '@hotwired/stimulus';
import { countActiveFilters, renderFilterBadge } from '../lib/filter_badge.js';

/**
 * À poser sur un bouton qui ouvre la modale de filtres :
 * lit les filtres appliqués dans l'URL et met à jour l'état du bouton.
 */
export default class extends Controller {
    connect() {
        const params = new URLSearchParams(window.location.search);

        renderFilterBadge(this.element, countActiveFilters(params));
    }
}
