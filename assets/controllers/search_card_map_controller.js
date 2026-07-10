import { Controller } from '@hotwired/stimulus';

const MAPBOX_CSS_URLS = [
    'https://api.mapbox.com/mapbox-gl-js/v3.25.0/mapbox-gl.css',
    'https://cdn.jsdelivr.net/npm/mapbox-gl@3.25.0/dist/mapbox-gl.css',
];

const MAPBOX_JS_URLS = [
    'https://api.mapbox.com/mapbox-gl-js/v3.25.0/mapbox-gl.js',
    'https://cdn.jsdelivr.net/npm/mapbox-gl@3.25.0/dist/mapbox-gl.js',
];

export default class extends Controller {
    static targets = [
        'mapbox',
        'error',
        'resultsGrid',
        'totalResults',
        'title',

        'preview',
        'previewLink',
        'previewMedia',
        'previewTitle',
        'previewPrice',
        'previewPeriod',
        'previewBadge',
        'previewDescription',
        'previewDetails',
        'previewAddress',
        'previewReference',
        'previewMapbox',
        'previewEnergy',

        'layout',
        'listColumn',
        'mapColumn',
        'mapPanel',
        'expandButton',
        'expandIcon',
        'expandLabel',
    ];

    static values = {
        token: String,
        boundsUrl: String,
    };

    connect() {
        this.map = null;
        this.currentProperties = [];

        this.markerObjects = new Map();
        this.markerElements = new Map();

        this.boundsRequestController = null;
        this.boundsRequestTimeout = null;
        this.mapResizeTimeout = null;

        this.initialMapFitDone = false;
        this.mapExpanded = false;

        this.loadCssOnce(MAPBOX_CSS_URLS[0]);

        this.loadScriptWithFallback(MAPBOX_JS_URLS)
            .then(() => {
                this.initSearchMap();
            })
            .catch((error) => {
                console.error('[Mapbox]', error);

                this.showMapError(
                    'Mapbox GL JS ne charge pas. Vérifie les CDN ou la Content-Security-Policy.'
                );
            });
    }

    disconnect() {
        window.clearTimeout(this.boundsRequestTimeout);
        window.clearTimeout(this.mapResizeTimeout);

        if (this.boundsRequestController) {
            this.boundsRequestController.abort();
            this.boundsRequestController = null;
        }

        this.clearMarkers();

        if (this.map) {
            this.map.remove();
            this.map = null;
        }
    }

    /* ================================================================ */
    /* Agrandir / réduire                                                */
    /* ================================================================ */

    openMapModal(event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (this.mapExpanded) {
            this.shrinkMap();

            return;
        }

        this.expandMap();
    }

    expandMap() {
        if (
            !this.hasListColumnTarget ||
            !this.hasMapColumnTarget ||
            !this.hasMapPanelTarget ||
            !this.hasTitleTarget
        ) {
            console.error(
                '[search-card-map] Une ou plusieurs targets sont manquantes.'
            );

            return;
        }

        this.mapExpanded = true;

        /*
         * Masque le H1.
         */
        this.titleTarget.classList.add('d-none');
        this.titleTarget.hidden = true;
        this.titleTarget.style.setProperty(
            'display',
            'none',
            'important'
        );

        /*
         * Masque la colonne des logements.
         */
        this.listColumnTarget.classList.add('d-none');
        this.listColumnTarget.hidden = true;
        this.listColumnTarget.style.setProperty(
            'display',
            'none',
            'important'
        );

        /*
         * Passe la carte en pleine largeur.
         */
        this.mapColumnTarget.classList.remove('col-lg-6');
        this.mapColumnTarget.classList.add('col-lg-12');

        this.mapColumnTarget.style.setProperty(
            'width',
            '100%',
            'important'
        );

        this.mapColumnTarget.style.setProperty(
            'max-width',
            '100%',
            'important'
        );

        this.mapColumnTarget.style.setProperty(
            'flex',
            '0 0 100%',
            'important'
        );

        this.element.classList.add(
            'search-card-map-results--expanded'
        );

        this.mapPanelTarget.classList.add(
            'search-card-map-panel--expanded'
        );

        if (this.hasLayoutTarget) {
            this.layoutTarget.classList.add(
                'search-card-map-layout--expanded'
            );
        }

        this.updateExpandButton(true);
        this.hidePreview();
        this.resizeMapAfterLayoutChange();
    }

    shrinkMap() {
        if (
            !this.hasListColumnTarget ||
            !this.hasMapColumnTarget ||
            !this.hasMapPanelTarget ||
            !this.hasTitleTarget
        ) {
            return;
        }

        this.mapExpanded = false;

        /*
         * Réaffiche le H1.
         */
        this.titleTarget.classList.remove('d-none');
        this.titleTarget.hidden = false;
        this.titleTarget.style.removeProperty('display');

        /*
         * Réaffiche la colonne des logements.
         */
        this.listColumnTarget.classList.remove('d-none');
        this.listColumnTarget.hidden = false;
        this.listColumnTarget.style.removeProperty('display');

        /*
         * La carte revient en col-lg-6.
         */
        this.mapColumnTarget.classList.remove('col-lg-12');
        this.mapColumnTarget.classList.add('col-lg-6');

        this.mapColumnTarget.style.removeProperty('width');
        this.mapColumnTarget.style.removeProperty('max-width');
        this.mapColumnTarget.style.removeProperty('flex');

        this.element.classList.remove(
            'search-card-map-results--expanded'
        );

        this.mapPanelTarget.classList.remove(
            'search-card-map-panel--expanded'
        );

        if (this.hasLayoutTarget) {
            this.layoutTarget.classList.remove(
                'search-card-map-layout--expanded'
            );
        }

        this.updateExpandButton(false);
        this.hidePreview();
        this.resizeMapAfterLayoutChange();
    }

    updateExpandButton(isExpanded) {
        if (this.hasExpandButtonTarget) {
            this.expandButtonTarget.setAttribute(
                'aria-label',
                isExpanded
                    ? ''
                    : ''
            );

            this.expandButtonTarget.setAttribute(
                'aria-expanded',
                isExpanded ? 'true' : 'false'
            );

            this.expandButtonTarget.classList.toggle(
                'search-card-map-fit--expanded',
                isExpanded
            );
        }

        if (this.hasExpandLabelTarget) {
            this.expandLabelTarget.textContent = isExpanded
                ? ''
                : '';
        }

        if (this.hasExpandIconTarget) {
            this.expandIconTarget.classList.remove(
                'icon-maximize-2',
                'icon-minimize-2',
                'icon-x'
            );

            this.expandIconTarget.classList.add(
                isExpanded
                    ? 'icon-x'
                    : 'icon-maximize-2'
            );
        }
    }

    resizeMapAfterLayoutChange() {
        window.clearTimeout(this.mapResizeTimeout);

        if (!this.map) {
            return;
        }

        this.map.resize();

        window.requestAnimationFrame(() => {
            if (this.map) {
                this.map.resize();
            }
        });

        this.mapResizeTimeout = window.setTimeout(() => {
            if (this.map) {
                this.map.resize();
            }
        }, 400);
    }

    /* ================================================================ */
    /* Initialisation Mapbox                                             */
    /* ================================================================ */

    initSearchMap() {
        if (!this.hasMapboxTarget) {
            console.error(
                '[search-card-map] Target mapbox manquante.'
            );

            return;
        }

        const token = this.tokenValue || '';

        if (!token || !token.startsWith('pk.')) {
            this.showMapError(
                'Token Mapbox manquant ou invalide. Il doit commencer par pk.'
            );

            return;
        }

        if (!window.mapboxgl) {
            this.showMapError(
                'Mapbox GL JS ne charge pas. Vérifie le CDN ou la Content-Security-Policy.'
            );

            return;
        }

        window.mapboxgl.accessToken = token;

        this.currentProperties =
            this.getValidPropertiesFromCards();

        const center = this.currentProperties.length > 0
            ? [
                this.currentProperties[0].lng,
                this.currentProperties[0].lat,
            ]
            : [2.3522, 48.8566];

        try {
            this.map = new window.mapboxgl.Map({
                container: this.mapboxTarget,
                style: 'mapbox://styles/mapbox/streets-v12',
                center,
                zoom: this.currentProperties.length > 0 ? 11 : 5,
            });
        } catch (error) {
            console.error(
                '[Mapbox initialisation]',
                error
            );

            this.showMapError(
                `Erreur pendant l’initialisation de Mapbox : ${error.message}`
            );

            return;
        }

        this.map.addControl(
            new window.mapboxgl.NavigationControl({
                showCompass: false,
                showZoom: true,
            }),
            'top-right'
        );

        if (this.hasPreviewTarget) {
            this.previewTarget.hidden = true;
        }

        this.map.on('load', () => {
            if (!this.map) {
                return;
            }

            this.map.resize();

            this.currentProperties =
                this.getValidPropertiesFromCards();

            this.renderMarkers(
                this.currentProperties
            );

            if (this.currentProperties.length > 0) {
                this.fitPropertiesOnMap(
                    this.currentProperties,
                    false
                );
            }

            this.hidePreview();

            window.setTimeout(() => {
                this.initialMapFitDone = true;
            }, 250);
        });

        this.map.on('moveend', () => {
            if (!this.initialMapFitDone) {
                return;
            }

            this.refreshCardsFromCurrentMapBoundsDebounced();
        });

        this.map.on('click', () => {
            this.hidePreview();
        });

        this.map.on('error', (event) => {
            if (event?.error?.message) {
                console.error(
                    '[Mapbox]',
                    event.error.message
                );
            }
        });
    }

    /* ================================================================ */
    /* Chargement des ressources Mapbox                                  */
    /* ================================================================ */

    loadCssOnce(url) {
        const existingStylesheet = document.querySelector(
            'link[data-mapbox-gl-css="true"]'
        );

        if (existingStylesheet) {
            return;
        }

        const link = document.createElement('link');

        link.rel = 'stylesheet';
        link.href = url;
        link.dataset.mapboxGlCss = 'true';

        document.head.appendChild(link);
    }

    loadScriptWithFallback(urls, index = 0) {
        return new Promise((resolve, reject) => {
            if (window.mapboxgl) {
                resolve(window.mapboxgl);

                return;
            }

            const currentUrl = urls[index];

            if (!currentUrl) {
                reject(
                    new Error(
                        'Impossible de charger Mapbox GL JS depuis les CDN.'
                    )
                );

                return;
            }

            const existingScript = document.querySelector(
                'script[data-mapbox-gl-js="true"]'
            );

            if (existingScript) {
                if (
                    existingScript.dataset.loaded === 'true' &&
                    window.mapboxgl
                ) {
                    resolve(window.mapboxgl);

                    return;
                }

                existingScript.addEventListener(
                    'load',
                    () => {
                        if (window.mapboxgl) {
                            resolve(window.mapboxgl);

                            return;
                        }

                        reject(
                            new Error(
                                'Le script Mapbox est chargé mais window.mapboxgl est indisponible.'
                            )
                        );
                    },
                    {
                        once: true,
                    }
                );

                existingScript.addEventListener(
                    'error',
                    () => {
                        existingScript.remove();

                        this.loadScriptWithFallback(
                            urls,
                            index + 1
                        )
                            .then(resolve)
                            .catch(reject);
                    },
                    {
                        once: true,
                    }
                );

                return;
            }

            const script = document.createElement('script');

            script.src = currentUrl;
            script.async = true;
            script.dataset.mapboxGlJs = 'true';

            script.onload = () => {
                script.dataset.loaded = 'true';

                if (window.mapboxgl) {
                    resolve(window.mapboxgl);

                    return;
                }

                script.remove();

                this.loadScriptWithFallback(
                    urls,
                    index + 1
                )
                    .then(resolve)
                    .catch(reject);
            };

            script.onerror = () => {
                script.remove();

                this.loadScriptWithFallback(
                    urls,
                    index + 1
                )
                    .then(resolve)
                    .catch(reject);
            };

            document.head.appendChild(script);
        });
    }

    /* ================================================================ */
    /* Erreur                                                             */
    /* ================================================================ */

    showMapError(message) {
        console.error(
            '[search-card-map]',
            message
        );

        if (!this.hasErrorTarget) {
            return;
        }

        this.errorTarget.hidden = false;
        this.errorTarget.textContent = message;
    }

    /* ================================================================ */
    /* Lecture des logements                                              */
    /* ================================================================ */

    parseMapProperty(card) {
        const rawValue = card.dataset.mapProperty || '';

        if (!rawValue) {
            return null;
        }

        try {
            const property = JSON.parse(rawValue);

            const latitude =
                property.lat !== null &&
                property.lat !== undefined &&
                property.lat !== ''
                    ? Number(property.lat)
                    : null;

            const longitude =
                property.lng !== null &&
                property.lng !== undefined &&
                property.lng !== ''
                    ? Number(property.lng)
                    : null;

            return {
                ...property,
                id: String(property.id),
                lat: latitude,
                lng: longitude,
                card,
            };
        } catch (error) {
            console.error(
                '[Mapbox] JSON invalide dans data-map-property :',
                card,
                error
            );

            return null;
        }
    }

    getPropertiesFromCards() {
        const cards = this.element.querySelectorAll(
            '.search-card-item[data-map-property]'
        );

        const properties = [];

        cards.forEach((card) => {
            const property =
                this.parseMapProperty(card);

            if (property) {
                properties.push(property);
            }
        });

        return properties;
    }

    getValidPropertiesFromCards() {
        return this.getPropertiesFromCards().filter(
            (property) => {
                return this.isValidMarkerProperty(property);
            }
        );
    }

    isValidMarkerProperty(property) {
        return Boolean(
            property &&
            Number.isFinite(property.lat) &&
            Number.isFinite(property.lng) &&
            property.lat >= -90 &&
            property.lat <= 90 &&
            property.lng >= -180 &&
            property.lng <= 180
        );
    }

    formatResultsCount(total) {
        const count =
            Number.parseInt(total, 10) || 0;

        return `${count} logement${count > 1 ? 's' : ''} trouvé${count > 1 ? 's' : ''}`;
    }

    /* ================================================================ */
    /* Aperçu                                                             */
    /* ================================================================ */

    clearActiveState() {
        this.markerElements.forEach((element) => {
            element.classList.remove('is-active');
        });

        this.element
            .querySelectorAll('.search-card-item.is-map-active')
            .forEach((card) => {
                card.classList.remove('is-map-active');
            });
    }

    hidePreview(event = null) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (this.hasPreviewTarget) {
            this.previewTarget.hidden = true;
        }

        this.clearActiveState();
    }

    showPreview(property) {
        if (!this.hasPreviewTarget || !property) {
            return;
        }

        this.previewTarget.hidden = false;

        if (this.hasPreviewLinkTarget) {
            this.previewLinkTarget.href =
                property.url || '#';
        }

        if (this.hasPreviewTitleTarget) {
            this.previewTitleTarget.textContent =
                property.title || '';
        }

        if (this.hasPreviewPriceTarget) {
            this.previewPriceTarget.textContent =
                property.price || '';
        }

        if (this.hasPreviewPeriodTarget) {
            this.previewPeriodTarget.textContent =
                property.period || '';

            this.previewPeriodTarget.hidden =
                !property.period;
        }

        if (this.hasPreviewBadgeTarget) {
            this.previewBadgeTarget.textContent =
                property.badge || '';

            this.previewBadgeTarget.hidden =
                !property.badge;
        }

        if (this.hasPreviewDescriptionTarget) {
            this.previewDescriptionTarget.textContent =
                property.description || '';

            this.previewDescriptionTarget.hidden =
                !property.description;
        }

        if (this.hasPreviewDetailsTarget) {
            this.previewDetailsTarget.textContent =
                property.details || '';

            this.previewDetailsTarget.hidden =
                !property.details;
        }

        if (this.hasPreviewAddressTarget) {
            this.previewAddressTarget.textContent =
                property.address || '';

            this.previewAddressTarget.hidden =
                !property.address;
        }

        if (this.hasPreviewReferenceTarget) {
            const reference =
                property.referenceInterne
                    ? `Référence : ${property.referenceInterne}`
                    : '';

            this.previewReferenceTarget.textContent =
                reference;

            this.previewReferenceTarget.hidden =
                !reference;
        }

        if (this.hasPreviewMapboxTarget) {
            const parts = [];

            if (property.mapboxId) {
                parts.push(
                    `Mapbox : ${property.mapboxId}`
                );
            }

            if (property.featureType) {
                parts.push(
                    `Type : ${property.featureType}`
                );
            }

            if (
                property.codePostal ||
                property.ville
            ) {
                parts.push(
                    `${property.codePostal || ''} ${property.ville || ''}`.trim()
                );
            }

            this.previewMapboxTarget.textContent =
                parts.join(' · ');

            this.previewMapboxTarget.hidden =
                parts.length === 0;
        }

        if (this.hasPreviewEnergyTarget) {
            const parts = [];

            if (property.surfaceTotal) {
                parts.push(
                    `${property.surfaceTotal} m²`
                );
            }

            if (property.chambres) {
                const bedrooms =
                    Number(property.chambres);

                parts.push(
                    `${property.chambres} chambre${bedrooms > 1 ? 's' : ''}`
                );
            }

            if (property.salleDeBains) {
                const bathrooms =
                    Number(property.salleDeBains);

                parts.push(
                    `${property.salleDeBains} salle${bathrooms > 1 ? 's' : ''} de bains`
                );
            }

            if (property.dpeLettre) {
                parts.push(
                    `DPE ${property.dpeLettre}`
                );
            }

            if (property.gesLettre) {
                parts.push(
                    `GES ${property.gesLettre}`
                );
            }

            this.previewEnergyTarget.textContent =
                parts.join(' · ');

            this.previewEnergyTarget.hidden =
                parts.length === 0;
        }

        if (this.hasPreviewMediaTarget) {
            this.previewMediaTarget.style.backgroundImage =
                property.image
                    ? `url("${property.image}")`
                    : '';
        }
    }

    activateProperty(property) {
        this.clearActiveState();

        const propertyId =
            String(property.id);

        const markerElement =
            this.markerElements.get(propertyId);

        if (markerElement) {
            markerElement.classList.add('is-active');
        }

        if (property.card) {
            property.card.classList.add(
                'is-map-active'
            );
        }

        this.showPreview(property);
    }

    /* ================================================================ */
    /* Marqueurs                                                          */
    /* ================================================================ */

    clearMarkers() {
        this.markerObjects.forEach((marker) => {
            marker.remove();
        });

        this.markerObjects.clear();
        this.markerElements.clear();
    }

    renderMarkers(properties) {
        this.clearMarkers();

        if (!this.map || !Array.isArray(properties)) {
            return;
        }

        properties.forEach((property) => {
            if (!this.isValidMarkerProperty(property)) {
                return;
            }

            const propertyId =
                String(property.id);

            const markerElement =
                document.createElement('button');

            markerElement.type = 'button';
            markerElement.className =
                'search-card-mapbox-marker';

            markerElement.textContent =
                property.price || 'Voir';

            markerElement.setAttribute(
                'aria-label',
                property.title
                    ? `Voir ${property.title}`
                    : 'Voir le logement'
            );

            markerElement.addEventListener(
                'click',
                (event) => {
                    event.preventDefault();
                    event.stopPropagation();

                    this.activateProperty(property);
                }
            );

            const marker =
                new window.mapboxgl.Marker({
                    element: markerElement,
                    anchor: 'bottom',
                })
                    .setLngLat([
                        property.lng,
                        property.lat,
                    ])
                    .addTo(this.map);

            this.markerObjects.set(
                propertyId,
                marker
            );

            this.markerElements.set(
                propertyId,
                markerElement
            );
        });
    }

    /* ================================================================ */
    /* Recentrage                                                         */
    /* ================================================================ */

    fitMap(event) {
        if (event) {
            event.preventDefault();
        }

        if (!this.map) {
            return;
        }

        this.currentProperties =
            this.getValidPropertiesFromCards();

        this.fitPropertiesOnMap(
            this.currentProperties,
            true
        );

        this.hidePreview();
    }

    fitPropertiesOnMap(properties, animate = true) {
        if (
            !this.map ||
            !Array.isArray(properties) ||
            properties.length === 0
        ) {
            return;
        }

        if (properties.length === 1) {
            const property = properties[0];

            if (animate) {
                this.map.flyTo({
                    center: [
                        property.lng,
                        property.lat,
                    ],
                    zoom: 13,
                    speed: 0.8,
                    curve: 1.2,
                    essential: true,
                });
            } else {
                this.map.jumpTo({
                    center: [
                        property.lng,
                        property.lat,
                    ],
                    zoom: 13,
                });
            }

            return;
        }

        const bounds =
            new window.mapboxgl.LngLatBounds();

        properties.forEach((property) => {
            if (
                this.isValidMarkerProperty(property)
            ) {
                bounds.extend([
                    property.lng,
                    property.lat,
                ]);
            }
        });

        if (bounds.isEmpty()) {
            return;
        }

        this.map.fitBounds(bounds, {
            padding: {
                top: 80,
                right: 80,
                bottom: 80,
                left: 80,
            },
            maxZoom: 13,
            duration: animate ? 700 : 0,
        });
    }

    /* ================================================================ */
    /* AJAX                                                               */
    /* ================================================================ */

    refreshCardsFromCurrentMapBoundsDebounced() {
        window.clearTimeout(
            this.boundsRequestTimeout
        );

        this.boundsRequestTimeout =
            window.setTimeout(() => {
                this.refreshCardsFromCurrentMapBounds(1);
            }, 350);
    }

    async refreshCardsFromCurrentMapBounds(page = 1) {
        if (
            !this.map ||
            !this.hasResultsGridTarget ||
            !this.hasTotalResultsTarget
        ) {
            return;
        }

        const mapBoundsUrl =
            this.boundsUrlValue || '';

        if (!mapBoundsUrl) {
            return;
        }

        const bounds = this.map.getBounds();

        if (!bounds) {
            return;
        }

        const url = new URL(
            mapBoundsUrl,
            window.location.origin
        );

        url.searchParams.set(
            'north',
            String(bounds.getNorth())
        );

        url.searchParams.set(
            'south',
            String(bounds.getSouth())
        );

        url.searchParams.set(
            'east',
            String(bounds.getEast())
        );

        url.searchParams.set(
            'west',
            String(bounds.getWest())
        );

        url.searchParams.set(
            'page',
            String(page)
        );

        if (this.boundsRequestController) {
            this.boundsRequestController.abort();
        }

        this.boundsRequestController =
            new AbortController();

        this.resultsGridTarget.classList.add(
            'search-card-grid-is-loading'
        );

        try {
            const response = await fetch(
                url.toString(),
                {
                    method: 'GET',
                    headers: {
                        'X-Requested-With':
                            'XMLHttpRequest',
                        Accept:
                            'application/json',
                    },
                    signal:
                    this.boundsRequestController.signal,
                }
            );

            if (!response.ok) {
                throw new Error(
                    `Erreur HTTP ${response.status}.`
                );
            }

            const payload =
                await response.json();

            if (!payload.success) {
                throw new Error(
                    payload.message ||
                    'Erreur pendant le chargement.'
                );
            }

            this.totalResultsTarget.textContent =
                this.formatResultsCount(
                    payload.total
                );

            this.resultsGridTarget.innerHTML =
                payload.html || '';

            this.replacePagination(
                payload.pagination || ''
            );

            this.hidePreview();

            this.currentProperties =
                this.getValidPropertiesFromCards();

            this.renderMarkers(
                this.currentProperties
            );
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }

            console.error(
                '[search-card-map AJAX]',
                error
            );
        } finally {
            this.resultsGridTarget.classList.remove(
                'search-card-grid-is-loading'
            );
        }
    }

    replacePagination(html) {
        const paginationElement =
            document.getElementById(
                'search-card-pagination'
            );

        if (!paginationElement) {
            return;
        }

        paginationElement.outerHTML =
            html ||
            '<nav id="search-card-pagination" class="search-card-pagination" aria-label="Pagination"></nav>';
    }
}
