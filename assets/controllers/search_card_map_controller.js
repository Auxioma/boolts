import { Controller } from '@hotwired/stimulus';

const MAPBOX_CSS_URLS = [
    'https://api.mapbox.com/mapbox-gl-js/v3.25.0/mapbox-gl.css',
    'https://cdn.jsdelivr.net/npm/mapbox-gl@3.25.0/dist/mapbox-gl.css',
];

const MAPBOX_JS_URLS = [
    'https://api.mapbox.com/mapbox-gl-js/v3.25.0/mapbox-gl.js',
    'https://cdn.jsdelivr.net/npm/mapbox-gl@3.25.0/dist/mapbox-gl.js',
];

/* Marge (px) entre la carte et la barre de recherche, et entre la carte et le bas de l'écran */
const MAP_GAP = 32;
const MAP_MIN_MOBILE_HEIGHT = 320;

export default class extends Controller {
    static targets = [
        'mapbox',
        'error',
        'resultsGrid',
        'totalResults',
        'title',

        'preview',
        'previewCard',

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
        labels: Object,
    };

    label(key, params = {}, fallback = '') {
        let text = (this.hasLabelsValue && this.labelsValue[key]) || fallback;

        Object.entries(params).forEach(([name, value]) => {
            text = text.split(`%${name}%`).join(String(value));
        });

        return text;
    }

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

        this.onStickyResize = this.updateStickyTop.bind(this);
        window.addEventListener('resize', this.onStickyResize);
        this.updateStickyTop();

        this.mapTop = 0;
        this.scrollBeforeExpand = 0;
        this.mapHeightFrame = null;
        this.onMapHeightResize = this.updateMapHeight.bind(this);
        this.onMapHeightScroll = this.scheduleMapHeight.bind(this);
        window.addEventListener('resize', this.onMapHeightResize);
        window.addEventListener('scroll', this.onMapHeightScroll, { passive: true });
        this.updateMapHeight();

        this.loadCssOnce(MAPBOX_CSS_URLS[0]);

        this.loadScriptWithFallback(MAPBOX_JS_URLS)
            .then(() => {
                this.initSearchMap();
            })
            .catch((error) => {
                console.error('[Mapbox]', error);

                this.showMapError(
                    this.label('errorLoad', {}, 'Mapbox GL JS ne charge pas. Vérifie les CDN ou la Content-Security-Policy.')
                );
            });
    }

    disconnect() {
        window.clearTimeout(this.boundsRequestTimeout);
        window.clearTimeout(this.mapResizeTimeout);
        window.removeEventListener('resize', this.onStickyResize);
        window.removeEventListener('resize', this.onMapHeightResize);
        window.removeEventListener('scroll', this.onMapHeightScroll);
        window.cancelAnimationFrame(this.mapHeightFrame);
        window.cancelAnimationFrame(this.previewFrame);
        this.previewFrame = null;
        this.mapColumnTarget.style.removeProperty('padding-top');
        this.mapPanelTarget.style.removeProperty('--search-card-map-sticky-top');
        this.mapPanelTarget.style.removeProperty('--search-card-map-top');
        this.mapPanelTarget.style.removeProperty('--search-card-map-height');

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
         * Mémorise la position dans les annonces pour y revenir à la réduction.
         */
        this.scrollBeforeExpand = window.scrollY;

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
        this.updateMapHeight();

        /*
         * La liste masquée raccourcit la page : sans ça, la carte s'ouvrirait
         * plus ou moins haut selon l'endroit où l'on était dans les annonces.
         */
        this.scrollMapIntoPlace();
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
        this.updateMapHeight();

        /*
         * Retour à l'endroit où l'on était dans les annonces avant l'agrandissement.
         */
        this.scrollToInstantly(this.scrollBeforeExpand || 0);
    }

    /*
     * Place la carte agrandie toujours au même endroit de l'écran :
     * son bord haut à 32px sous la barre de recherche.
     */
    scrollMapIntoPlace() {
        const panelTop = this.mapPanelTarget.getBoundingClientRect().top + window.scrollY;

        this.scrollToInstantly(panelTop - (this.mapTop || 0));
    }

    /*
     * Scroll sans animation (html a scroll-behavior: smooth dans global.css),
     * puis recalcul de la hauteur de la carte à sa nouvelle position.
     */
    scrollToInstantly(top) {
        window.scrollTo({
            top: Math.max(Math.round(top), 0),
            behavior: 'instant',
        });

        if (
            window.matchMedia('(min-width: 992px)').matches &&
            this.applyMapHeight() &&
            this.map
        ) {
            this.map.resize();
        }
    }

    updateStickyTop() {
        const column = this.mapColumnTarget;
        const paddingTop = Number.parseFloat(window.getComputedStyle(column).paddingTop) || 0;
        const initialTop = column.getBoundingClientRect().top + window.scrollY + paddingTop;
        this.mapPanelTarget.style.setProperty('--search-card-map-sticky-top', `${initialTop}px`);
    }

    /*
     * Écrans >= 992px, carte normale ou agrandie :
     * - haut de la carte à 32px sous la barre de recherche (fixe) ;
     * - bas de la carte à 32px du bas de l'écran.
     * La carte est placée par un padding-top sur sa colonne (le sticky seul ne
     * suffit pas quand la colonne n'a pas de place, ex. carte agrandie), et sa
     * hauteur est calculée sur sa position réelle à l'écran, y compris au scroll.
     * En dessous de 992px : la carte prend la hauteur de l'écran restante
     * sous le HTML qui la précède (recalculée au resize, ex. barre d'adresse mobile).
     */
    updateMapHeight() {
        if (!this.hasMapPanelTarget || !this.hasMapColumnTarget) {
            return;
        }

        if (!window.matchMedia('(min-width: 992px)').matches) {
            this.mapColumnTarget.style.removeProperty('padding-top');
            this.mapPanelTarget.style.removeProperty('--search-card-map-top');

            /* Hauteur de l'écran moins le HTML au-dessus de la carte (navbar, barre de recherche...) */
            const panelTop = this.mapPanelTarget.getBoundingClientRect().top + window.scrollY;
            const height = Math.max(Math.round(window.innerHeight - panelTop), MAP_MIN_MOBILE_HEIGHT);

            this.mapPanelTarget.style.setProperty('--search-card-map-height', `${height}px`);
            this.resizeMapAfterLayoutChange();

            return;
        }

        const toolbar = document.querySelector('.search-card-toolbar');
        this.mapTop = toolbar
            ? Math.round(toolbar.getBoundingClientRect().bottom) + MAP_GAP
            : MAP_GAP;

        this.mapPanelTarget.style.setProperty('--search-card-map-top', `${this.mapTop}px`);

        /* Position de la colonne dans la page, sans son padding actuel */
        this.mapColumnTarget.style.removeProperty('padding-top');
        const columnTop = this.mapColumnTarget.getBoundingClientRect().top + window.scrollY;
        const paddingTop = Math.max(Math.round(this.mapTop - columnTop), 0);

        if (paddingTop > 0) {
            this.mapColumnTarget.style.setProperty('padding-top', `${paddingTop}px`, 'important');
        }

        this.applyMapHeight();
        this.resizeMapAfterLayoutChange();
    }

    scheduleMapHeight() {
        if (this.mapHeightFrame) {
            return;
        }

        this.mapHeightFrame = window.requestAnimationFrame(() => {
            this.mapHeightFrame = null;

            if (!window.matchMedia('(min-width: 992px)').matches) {
                return;
            }

            if (this.applyMapHeight() && this.map) {
                this.map.resize();
            }
        });
    }

    /*
     * Hauteur = hauteur de l'écran - haut visible de la carte - 32px.
     * Quand la carte remonte au-dessus de sa position (fin de colonne au scroll),
     * on garde la hauteur calculée à sa position normale.
     * Retourne true si la hauteur a changé.
     */
    applyMapHeight() {
        const top = Math.max(
            this.mapPanelTarget.getBoundingClientRect().top,
            this.mapTop || 0
        );
        const height = `${Math.max(Math.round(window.innerHeight - top - MAP_GAP), 0)}px`;

        if (this.mapPanelTarget.style.getPropertyValue('--search-card-map-height') === height) {
            return false;
        }

        this.mapPanelTarget.style.setProperty('--search-card-map-height', height);

        return true;
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
                this.label('errorToken', {}, 'Token Mapbox manquant ou invalide. Il doit commencer par pk.')
            );

            return;
        }

        if (!window.mapboxgl) {
            this.showMapError(
                this.label('errorLoad', {}, 'Mapbox GL JS ne charge pas. Vérifie le CDN ou la Content-Security-Policy.')
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
                this.label('errorInit', { message: error.message }, `Erreur pendant l’initialisation de Mapbox : ${error.message}`)
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

        // La preview suit son marqueur pendant les déplacements / zooms (comme Airbnb)
        this.map.on('move', () => {
            this.schedulePreviewPosition();
        });

        this.map.on('resize', () => {
            this.schedulePreviewPosition();
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

        return this.label(
            count > 1 ? 'resultsOther' : 'resultsOne',
            { count },
            `${count} logement${count > 1 ? 's' : ''} trouvé${count > 1 ? 's' : ''}`
        );
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
            this.resetPreviewPosition();
        }

        this.activePropertyId = null;
        this.activeProperty = null;

        this.clearActiveState();
    }

    showPreview(property) {
        if (!this.hasPreviewTarget || !this.hasPreviewCardTarget || !property) {
            return;
        }

        const card =
            property.card?.querySelector('.property-card-link');

        if (!card) {
            this.hidePreview();

            return;
        }

        this.previewCardTarget.replaceChildren(
            card.cloneNode(true)
        );

        this.previewTarget.hidden = false;

        this.positionPreview();

        // La hauteur de la card change quand l'image clonée finit de charger
        this.previewCardTarget
            .querySelectorAll('img')
            .forEach((image) => {
                if (!image.complete) {
                    image.addEventListener('load', () => this.positionPreview(), { once: true });
                }
            });
    }

    /* ================================================================ */
    /* Positionnement dynamique de la preview (desktop / tablette)        */
    /* ================================================================ */

    isFloatingPreviewEnabled() {
        return window.matchMedia('(min-width: 768px)').matches;
    }

    schedulePreviewPosition() {
        if (!this.activeProperty || this.previewFrame) {
            return;
        }

        this.previewFrame = window.requestAnimationFrame(() => {
            this.previewFrame = null;
            this.positionPreview();
        });
    }

    resetPreviewPosition() {
        const preview = this.previewTarget;

        preview.classList.remove('is-floating');
        preview.style.removeProperty('top');
        preview.style.removeProperty('left');
        preview.style.removeProperty('right');
        preview.style.removeProperty('bottom');
        preview.style.removeProperty('visibility');
    }

    /*
     * Place la card à côté du marqueur actif : au-dessus, sinon en dessous,
     * sinon à droite, sinon à gauche ; puis la garde dans les limites de la carte.
     */
    positionPreview() {
        if (!this.hasPreviewTarget || this.previewTarget.hidden || !this.map || !this.activeProperty) {
            return;
        }

        const preview = this.previewTarget;

        if (!this.isFloatingPreviewEnabled()) {
            this.resetPreviewPosition();

            return;
        }

        const panel = this.hasMapPanelTarget ? this.mapPanelTarget : preview.offsetParent;

        if (!panel) {
            return;
        }

        const markerElement = this.markerElements.get(String(this.activeProperty.id));
        const point = this.map.project([this.activeProperty.lng, this.activeProperty.lat]);
        const mapElement = this.map.getContainer();

        // Coordonnées du point d'ancrage du marqueur (bas-centre) dans le panneau
        const x = point.x + mapElement.offsetLeft;
        const y = point.y + mapElement.offsetTop;

        const panelWidth = panel.clientWidth;
        const panelHeight = panel.clientHeight;

        preview.classList.add('is-floating');
        preview.style.right = 'auto';
        preview.style.bottom = 'auto';

        // Marqueur sorti de la zone visible : on masque sans fermer
        if (x < 0 || y < 0 || x > panelWidth || y > panelHeight) {
            preview.style.visibility = 'hidden';

            return;
        }

        preview.style.visibility = '';

        const padding = 16;
        const gap = 8;
        const cardWidth = preview.offsetWidth;
        const cardHeight = preview.offsetHeight;
        const markerWidth = markerElement ? markerElement.offsetWidth : 0;
        const markerHeight = markerElement ? markerElement.offsetHeight : 0;

        const placements = [
            {
                left: x - cardWidth / 2,
                top: y - markerHeight - gap - cardHeight,
                space: y - markerHeight - gap - padding - cardHeight,
            },
            {
                left: x - cardWidth / 2,
                top: y + gap,
                space: panelHeight - padding - (y + gap + cardHeight),
            },
            {
                left: x + markerWidth / 2 + gap,
                top: y - markerHeight / 2 - cardHeight / 2,
                space: panelWidth - padding - (x + markerWidth / 2 + gap + cardWidth),
            },
            {
                left: x - markerWidth / 2 - gap - cardWidth,
                top: y - markerHeight / 2 - cardHeight / 2,
                space: x - markerWidth / 2 - gap - padding - cardWidth,
            },
        ];

        const placement =
            placements.find((candidate) => candidate.space >= 0) ||
            placements.reduce((best, candidate) => (candidate.space > best.space ? candidate : best));

        const clamp = (value, min, max) => Math.max(min, Math.min(value, Math.max(min, max)));

        preview.style.left = `${Math.round(clamp(placement.left, padding, panelWidth - cardWidth - padding))}px`;
        preview.style.top = `${Math.round(clamp(placement.top, padding, panelHeight - cardHeight - padding))}px`;
    }

    activateProperty(property) {
        this.activePropertyId = String(property.id);
        this.activeProperty = property;

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
                property.price || this.label('markerView', {}, 'Voir');

            markerElement.setAttribute(
                'aria-label',
                property.title
                    ? this.label('markerViewTitle', { title: property.title }, `Voir ${property.title}`)
                    : this.label('markerViewDefault', {}, 'Voir le logement')
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
                    this.label('errorHttp', { status: response.status }, `Erreur HTTP ${response.status}.`)
                );
            }

            const payload =
                await response.json();

            if (!payload.success) {
                throw new Error(
                    payload.message ||
                    this.label('errorFetch', {}, 'Erreur pendant le chargement.')
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

            const previousActiveId = this.activePropertyId;

            this.currentProperties =
                this.getValidPropertiesFromCards();

            this.renderMarkers(
                this.currentProperties
            );

            // Le bien sélectionné est toujours dans la zone : on garde sa preview ouverte
            const stillVisible = previousActiveId
                ? this.currentProperties.find((property) => String(property.id) === previousActiveId)
                : null;

            if (stillVisible) {
                this.activateProperty(stillVisible);
            } else {
                this.hidePreview();
            }
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
