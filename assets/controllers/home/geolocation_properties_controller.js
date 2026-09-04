import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['content'];

    static values = {
        url: String,
    };

    connect() {
        console.log('[Stimulus] Géolocalisation de la page d’accueil connectée.');

        this.abortController = null;
        this.detectAndRefresh();
    }

    disconnect() {
        this.abortController?.abort();
    }

    async detectAndRefresh() {
        try {
            const coordinates = await this.getUserCoordinates();
            const location = await this.reverseGeocode(
                coordinates.latitude,
                coordinates.longitude
            );

            if (!location.city) {
                console.info('Aucune ville détectée, retour à la requête initiale.');
                await this.resetToInitial();
                return;
            }

            console.log('Ville détectée :', location.city);

            await this.refreshProperties({
                city: location.city,
                country: location.country,
                countryCode: location.countryCode,
                latitude: coordinates.latitude,
                longitude: coordinates.longitude,
            });
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }

            console.info(
                'Géolocalisation indisponible, retour à la requête initiale :',
                error.message
            );

            await this.resetToInitial();
        }
    }

    /**
     * L'utilisateur a refusé / désactivé la géolocalisation du navigateur :
     * on demande au serveur d'oublier la ville mémorisée en session et de
     * renvoyer les biens de la requête initiale (localisés via l'adresse IP).
     */
    async resetToInitial() {
        try {
            this.abortController?.abort();
            this.abortController = new AbortController();

            const url = new URL(this.urlValue, window.location.origin);

            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    Accept: 'text/html',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: this.abortController.signal,
            });

            if (!response.ok) {
                return;
            }

            this.contentTarget.innerHTML = await response.text();
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }

            console.info(
                'Impossible de restaurer la requête initiale :',
                error.message
            );
        }
    }

    getUserCoordinates() {
        return new Promise((resolve, reject) => {
            if (!navigator.geolocation) {
                reject(
                    new Error(
                        'La géolocalisation n’est pas disponible sur ce navigateur.'
                    )
                );

                return;
            }

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    resolve({
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude,
                    });
                },
                (error) => {
                    switch (error.code) {
                        case error.PERMISSION_DENIED:
                            reject(
                                new Error(
                                    'Vous avez refusé l’accès à votre position.'
                                )
                            );
                            break;

                        case error.POSITION_UNAVAILABLE:
                            reject(
                                new Error(
                                    'Votre position est actuellement indisponible.'
                                )
                            );
                            break;

                        case error.TIMEOUT:
                            reject(
                                new Error(
                                    'La détection de votre position a expiré.'
                                )
                            );
                            break;

                        default:
                            reject(
                                new Error(
                                    'Impossible de détecter votre position.'
                                )
                            );
                    }
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 300000,
                }
            );
        });
    }

    async reverseGeocode(latitude, longitude) {
        const url = new URL(
            'https://nominatim.openstreetmap.org/reverse'
        );

        url.searchParams.set('format', 'jsonv2');
        url.searchParams.set('lat', latitude.toString());
        url.searchParams.set('lon', longitude.toString());
        url.searchParams.set('addressdetails', '1');
        url.searchParams.set('accept-language', 'fr');

        const response = await fetch(url, {
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error(
                'Impossible de convertir votre position en ville.'
            );
        }

        const data = await response.json();
        const address = data.address ?? {};

        return {
            city:
                address.city ??
                address.town ??
                address.village ??
                address.municipality ??
                address.city_district ??
                null,

            country: address.country ?? null,

            countryCode: address.country_code
                ? address.country_code.toUpperCase()
                : null,
        };
    }

    async refreshProperties(location) {
        this.abortController?.abort();
        this.abortController = new AbortController();

        const url = new URL(this.urlValue, window.location.origin);

        url.searchParams.set('city', location.city);

        if (location.country) {
            url.searchParams.set('country', location.country);
        }

        if (location.countryCode) {
            url.searchParams.set(
                'countryCode',
                location.countryCode
            );
        }

        url.searchParams.set(
            'latitude',
            location.latitude.toString()
        );

        url.searchParams.set(
            'longitude',
            location.longitude.toString()
        );

        const response = await fetch(url, {
            method: 'GET',
            headers: {
                Accept: 'text/html',
                'X-Requested-With': 'XMLHttpRequest',
            },
            signal: this.abortController.signal,
        });

        if (!response.ok) {
            throw new Error(
                `Impossible de charger les biens de ${location.city}.`
            );
        }

        const html = await response.text();

        this.contentTarget.innerHTML = html;

        this.contentTarget.dispatchEvent(
            new CustomEvent('home:properties-updated', {
                bubbles: true,
                detail: location,
            })
        );
    }
}