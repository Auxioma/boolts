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
        this.initialMapFitDone = false;

        this.loadCssOnce(MAPBOX_CSS_URLS[0]);

        this.loadScriptWithFallback(MAPBOX_JS_URLS)
            .then(() => {
                this.initSearchMap();
            })
            .catch((error) => {
                console.error(error);

                this.showMapError(
                    'Mapbox GL JS ne charge pas. Si le CDN est bloqué, installe Mapbox en local dans public/vendor/mapbox.'
                );
            });
    }

    disconnect() {
        window.clearTimeout(this.boundsRequestTimeout);

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

    initSearchMap() {
        if (!this.hasMapboxTarget) {
            return;
        }

        const token = this.tokenValue || '';

        if (!token || !token.startsWith('pk.')) {
            this.showMapError('Token Mapbox manquant ou invalide. Il doit commencer par pk.');
            return;
        }

        if (!window.mapboxgl) {
            this.showMapError('Mapbox GL JS ne charge pas. Vérifie le CDN ou la Content-Security-Policy.');
            return;
        }

        window.mapboxgl.accessToken = token;

        this.currentProperties = this.getPropertiesFromCards().filter((property) => {
            return this.isValidMarkerProperty(property);
        });

        const center = this.currentProperties.length > 0
            ? [this.currentProperties[0].lng, this.currentProperties[0].lat]
            : [2.3522, 48.8566];

        try {
            this.map = new window.mapboxgl.Map({
                container: this.mapboxTarget,
                style: 'mapbox://styles/mapbox/streets-v12',
                center: center,
                zoom: this.currentProperties.length > 0 ? 11 : 5,
            });
        } catch (error) {
            this.showMapError('Erreur pendant l’initialisation de Mapbox : ' + error.message);
            return;
        }

        this.map.addControl(
            new window.mapboxgl.NavigationControl({
                showCompass: false,
            }),
            'top-right'
        );

        if (this.hasPreviewTarget) {
            this.previewTarget.hidden = true;
        }

        this.map.on('load', () => {
            this.map.resize();

            this.currentProperties = this.getPropertiesFromCards().filter((property) => {
                return this.isValidMarkerProperty(property);
            });

            this.renderMarkers(this.currentProperties);

            if (this.currentProperties.length > 0) {
                this.fitPropertiesOnMap(this.currentProperties, false);
            }

            this.hidePreview();
            this.initialMapFitDone = true;
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
            if (event && event.error && event.error.message) {
                console.error(event.error.message);
            }
        });
    }

    showMapError(message) {
        if (!this.hasErrorTarget) {
            console.error(message);
            return;
        }

        this.errorTarget.hidden = false;
        this.errorTarget.textContent = message;
    }

    loadCssOnce(url) {
        const existing = document.querySelector('link[data-mapbox-gl-css="true"]');

        if (existing) {
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

            if (!urls[index]) {
                reject(new Error('Mapbox GL JS ne charge pas.'));
                return;
            }

            const existingScript = document.querySelector('script[data-mapbox-gl-js="true"]');

            if (existingScript) {
                existingScript.addEventListener('load', () => {
                    if (window.mapboxgl) {
                        resolve(window.mapboxgl);
                    } else {
                        reject(new Error('Mapbox GL JS ne charge pas.'));
                    }
                });

                existingScript.addEventListener('error', () => {
                    this.loadScriptWithFallback(urls, index + 1)
                        .then(resolve)
                        .catch(reject);
                });

                return;
            }

            const script = document.createElement('script');
            script.src = urls[index];
            script.async = false;
            script.dataset.mapboxGlJs = 'true';

            script.onload = () => {
                if (window.mapboxgl) {
                    resolve(window.mapboxgl);
                    return;
                }

                script.remove();

                this.loadScriptWithFallback(urls, index + 1)
                    .then(resolve)
                    .catch(reject);
            };

            script.onerror = () => {
                script.remove();

                this.loadScriptWithFallback(urls, index + 1)
                    .then(resolve)
                    .catch(reject);
            };

            document.head.appendChild(script);
        });
    }

    parseMapProperty(card) {
        const rawValue = card.dataset.mapProperty || '';

        if (!rawValue) {
            return null;
        }

        try {
            const property = JSON.parse(rawValue);

            return {
                ...property,
                id: String(property.id),
                lat: property.lat !== null && property.lat !== '' ? Number(property.lat) : null,
                lng: property.lng !== null && property.lng !== '' ? Number(property.lng) : null,
                card: card,
            };
        } catch (error) {
            console.error('[Mapbox] JSON invalide sur la card :', card, error);
            return null;
        }
    }

    getPropertiesFromCards() {
        const cards = this.element.querySelectorAll('.search-card-item[data-map-property]');
        const properties = [];

        cards.forEach((card) => {
            const property = this.parseMapProperty(card);

            if (property) {
                properties.push(property);
            }
        });

        return properties;
    }

    isValidMarkerProperty(property) {
        return property && Number.isFinite(property.lat) && Number.isFinite(property.lng);
    }

    formatResultsCount(total) {
        const count = Number(total) || 0;

        return `${count} logement${count > 1 ? 's' : ''} trouvé${count > 1 ? 's' : ''}`;
    }

    clearActiveState() {
        this.markerElements.forEach((element) => {
            element.classList.remove('is-active');
        });

        this.element.querySelectorAll('.search-card-item.is-map-active').forEach((card) => {
            card.classList.remove('is-map-active');
        });
    }

    hidePreview() {
        if (this.hasPreviewTarget) {
            this.previewTarget.hidden = true;
        }

        this.clearActiveState();
    }

    showPreview(property) {
        if (!this.hasPreviewTarget) {
            return;
        }

        this.previewTarget.hidden = false;

        if (this.hasPreviewLinkTarget) {
            this.previewLinkTarget.href = property.url || '#';
        }

        if (this.hasPreviewTitleTarget) {
            this.previewTitleTarget.textContent = property.title || '';
        }

        if (this.hasPreviewPriceTarget) {
            this.previewPriceTarget.textContent = property.price || '';
        }

        if (this.hasPreviewPeriodTarget) {
            this.previewPeriodTarget.textContent = property.period || '';
        }

        if (this.hasPreviewBadgeTarget) {
            this.previewBadgeTarget.textContent = property.badge || '';
            this.previewBadgeTarget.hidden = !property.badge;
        }

        if (this.hasPreviewDescriptionTarget) {
            this.previewDescriptionTarget.textContent = property.description || '';
            this.previewDescriptionTarget.hidden = !property.description;
        }

        if (this.hasPreviewDetailsTarget) {
            this.previewDetailsTarget.textContent = property.details || '';
            this.previewDetailsTarget.hidden = !property.details;
        }

        if (this.hasPreviewAddressTarget) {
            this.previewAddressTarget.textContent = property.address || '';
            this.previewAddressTarget.hidden = !property.address;
        }

        if (this.hasPreviewReferenceTarget) {
            this.previewReferenceTarget.textContent = property.referenceInterne
                ? `Référence : ${property.referenceInterne}`
                : '';

            this.previewReferenceTarget.hidden = !property.referenceInterne;
        }

        if (this.hasPreviewMapboxTarget) {
            const parts = [];

            if (property.mapboxId) {
                parts.push(`Mapbox : ${property.mapboxId}`);
            }

            if (property.featureType) {
                parts.push(`Type : ${property.featureType}`);
            }

            if (property.codePostal || property.ville) {
                parts.push(`${property.codePostal || ''} ${property.ville || ''}`.trim());
            }

            this.previewMapboxTarget.textContent = parts.join(' · ');
            this.previewMapboxTarget.hidden = parts.length === 0;
        }

        if (this.hasPreviewEnergyTarget) {
            const parts = [];

            if (property.surfaceTotal) {
                parts.push(`${property.surfaceTotal}m²`);
            }

            if (property.chambres) {
                parts.push(`${property.chambres} chambre${Number(property.chambres) > 1 ? 's' : ''}`);
            }

            if (property.salleDeBains) {
                parts.push(`${property.salleDeBains} salle${Number(property.salleDeBains) > 1 ? 's' : ''} de bains`);
            }

            if (property.dpeLettre) {
                parts.push(`DPE ${property.dpeLettre}`);
            }

            if (property.gesLettre) {
                parts.push(`GES ${property.gesLettre}`);
            }

            this.previewEnergyTarget.textContent = parts.join(' · ');
            this.previewEnergyTarget.hidden = parts.length === 0;
        }

        if (this.hasPreviewMediaTarget) {
            this.previewMediaTarget.style.backgroundImage = property.image
                ? `url("${property.image}")`
                : '';
        }
    }

    activateProperty(property) {
        this.clearActiveState();

        const markerElement = this.markerElements.get(property.id);

        if (markerElement) {
            markerElement.classList.add('is-active');
        }

        if (property.card) {
            property.card.classList.add('is-map-active');
        }

        this.showPreview(property);
    }

    clearMarkers() {
        this.markerObjects.forEach((marker) => {
            marker.remove();
        });

        this.markerObjects.clear();
        this.markerElements.clear();
    }

    renderMarkers(properties) {
        this.clearMarkers();

        if (!this.map) {
            return;
        }

        properties.forEach((property) => {
            const markerElement = document.createElement('button');

            markerElement.type = 'button';
            markerElement.className = 'search-card-mapbox-marker';
            markerElement.textContent = property.price || 'Voir';
            markerElement.setAttribute('aria-label', property.title || 'Voir le logement');

            markerElement.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();

                this.activateProperty(property);
            });

            const marker = new window.mapboxgl.Marker({
                element: markerElement,
                anchor: 'bottom',
            })
                .setLngLat([property.lng, property.lat])
                .addTo(this.map);

            this.markerObjects.set(property.id, marker);
            this.markerElements.set(property.id, markerElement);
        });
    }

    fitMap(event) {
        if (event) {
            event.preventDefault();
        }

        if (!this.map) {
            return;
        }

        this.currentProperties = this.getPropertiesFromCards().filter((property) => {
            return this.isValidMarkerProperty(property);
        });

        this.fitPropertiesOnMap(this.currentProperties, true);
        this.hidePreview();
    }

    fitPropertiesOnMap(properties, animate = true) {
        if (!this.map || !properties || properties.length === 0) {
            return;
        }

        if (properties.length === 1) {
            if (animate) {
                this.map.flyTo({
                    center: [properties[0].lng, properties[0].lat],
                    zoom: 13,
                    speed: 0.8,
                    curve: 1.2,
                    essential: true,
                });
            } else {
                this.map.setCenter([properties[0].lng, properties[0].lat]);
                this.map.setZoom(13);
            }

            return;
        }

        const bounds = new window.mapboxgl.LngLatBounds();

        properties.forEach((property) => {
            bounds.extend([property.lng, property.lat]);
        });

        this.map.fitBounds(bounds, {
            padding: 80,
            maxZoom: 13,
            duration: animate ? 700 : 0,
        });
    }

    refreshCardsFromCurrentMapBoundsDebounced() {
        window.clearTimeout(this.boundsRequestTimeout);

        this.boundsRequestTimeout = window.setTimeout(() => {
            this.refreshCardsFromCurrentMapBounds(1);
        }, 350);
    }

    refreshCardsFromCurrentMapBounds(page = 1) {
        if (!this.map) {
            return;
        }

        if (!this.hasResultsGridTarget || !this.hasTotalResultsTarget) {
            return;
        }

        const mapBoundsUrl = this.boundsUrlValue || '';

        if (!mapBoundsUrl) {
            return;
        }

        const bounds = this.map.getBounds();

        if (!bounds) {
            return;
        }

        const url = new URL(mapBoundsUrl, window.location.origin);

        url.searchParams.set('north', bounds.getNorth());
        url.searchParams.set('south', bounds.getSouth());
        url.searchParams.set('east', bounds.getEast());
        url.searchParams.set('west', bounds.getWest());
        url.searchParams.set('page', page);

        if (this.boundsRequestController) {
            this.boundsRequestController.abort();
        }

        this.boundsRequestController = new AbortController();

        this.resultsGridTarget.classList.add('search-card-grid-is-loading');

        fetch(url.toString(), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
            },
            signal: this.boundsRequestController.signal,
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error('Erreur pendant le chargement des logements visibles.');
                }

                return response.json();
            })
            .then((payload) => {
                if (!payload.success) {
                    throw new Error(payload.message || 'Erreur pendant le chargement des logements visibles.');
                }

                this.totalResultsTarget.textContent = this.formatResultsCount(payload.total);
                this.resultsGridTarget.innerHTML = payload.html || '';

                this.replacePagination(payload.pagination || '');

                this.hidePreview();

                this.currentProperties = this.getPropertiesFromCards().filter((property) => {
                    return this.isValidMarkerProperty(property);
                });

                this.renderMarkers(this.currentProperties);
            })
            .catch((error) => {
                if (error.name === 'AbortError') {
                    return;
                }

                console.error(error);
            })
            .finally(() => {
                this.resultsGridTarget.classList.remove('search-card-grid-is-loading');
            });
    }

    replacePagination(html) {
        const paginationElement = document.getElementById('search-card-pagination');

        if (!paginationElement) {
            return;
        }

        paginationElement.outerHTML = html || '<nav id="search-card-pagination" class="search-card-pagination" aria-label="Pagination"></nav>';
    }
}
