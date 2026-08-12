import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'countryInput',
        'cityInput',
        'districtInput',

        'countryHidden',
        'cityHidden',
        'districtHidden',

        'countryList',
        'cityList',
        'districtList',
    ];

    static values = {
        countryUrl: String,
        cityUrl: String,
        districtUrl: String,
    };

    connect() {
        this.minChars = 2;
        this.debounceDelay = 150;
        this.maxResults = 10;

        this.selectedCountries = this.parseJsonValue(
            this.hasCountryHiddenTarget ? this.countryHiddenTarget.value : '[]'
        );

        this.selectedCities = this.parseJsonValue(
            this.hasCityHiddenTarget ? this.cityHiddenTarget.value : '[]'
        );

        this.selectedDistricts = this.parseJsonValue(
            this.hasDistrictHiddenTarget ? this.districtHiddenTarget.value : '[]'
        );

        this.timers = {};
        this.cache = new Map();
        this.dropdowns = new Map();
        this.abortControllers = new Map();

        this.injectStyle();
        this.bindNativeEvents();
        this.refreshUi();

        document.addEventListener('click', this.closeOnOutsideClick);
    }

    disconnect() {
        document.removeEventListener('click', this.closeOnOutsideClick);

        Object.values(this.timers).forEach((timer) => {
            clearTimeout(timer);
        });

        this.abortControllers.forEach((controller) => {
            controller.abort();
        });

        this.abortControllers.clear();
    }

    bindNativeEvents() {
        if (this.hasCountryInputTarget) {
            this.countryInputTarget.addEventListener('input', () => this.searchCountry());
            this.countryInputTarget.addEventListener('keydown', (event) => this.keyboardCountry(event));
        }

        if (this.hasCityInputTarget) {
            this.cityInputTarget.addEventListener('input', () => this.searchCity());
            this.cityInputTarget.addEventListener('keydown', (event) => this.keyboardCity(event));
        }

        if (this.hasDistrictInputTarget) {
            this.districtInputTarget.addEventListener('input', () => this.searchDistrict());
            this.districtInputTarget.addEventListener('keydown', (event) => this.keyboardDistrict(event));
        }
    }

    closeOnOutsideClick = (event) => {
        if (!this.element.contains(event.target)) {
            this.closeAllDropdowns();
        }
    };

    // ============================================================
    // PAYS
    // ============================================================

    searchCountry() {
        const query = this.countryInputTarget.value.trim();

        this.debounce('country', async () => {
            if (query.length < this.minChars) {
                this.closeDropdown(this.countryInputTarget);
                return;
            }

            const results = await this.fetchResults('country', this.countryUrlValue, {
                q: query,
            });

            if (this.countryInputTarget.value.trim() !== query) {
                return;
            }

            this.renderDropdown(
                this.countryInputTarget,
                results,
                (item) => this.getCountryLabel(item),
                (item) => this.getCountrySubtitle(item),
                (item) => this.addCountry(item)
            );
        });
    }

    keyboardCountry(event) {
        if (event.key !== 'Enter') {
            return;
        }

        event.preventDefault();

        const value = this.countryInputTarget.value.trim();

        if (!value) {
            return;
        }

        this.addCountry({
            label: value,
            name: value,
            country_name: value,
            code: value,
            country_code: value,
        });
    }

    addCountry(item) {
        const normalized = {
            label: this.getCountryLabel(item),
            code: this.getCountryCode(item),
            country_code: this.getCountryCode(item),
            country_name: this.getCountryLabel(item),
            raw: item,
        };

        if (!normalized.label) {
            return;
        }

        const key = this.normalizeKey(normalized.code || normalized.label);

        const alreadyExists = this.selectedCountries.some((country) => {
            return this.normalizeKey(country.code || country.country_code || country.label) === key;
        });

        if (alreadyExists) {
            this.countryInputTarget.value = '';
            this.closeDropdown(this.countryInputTarget);
            return;
        }

        this.selectedCountries.push(normalized);

        this.countryInputTarget.value = '';
        this.closeDropdown(this.countryInputTarget);

        this.refreshUi();
    }

    removeCountry(index) {
        const removedCountry = this.selectedCountries[index];

        this.selectedCountries.splice(index, 1);

        const removedCountryKey = this.normalizeKey(
            removedCountry.code || removedCountry.country_code || removedCountry.label
        );

        this.selectedCities = this.selectedCities.filter((city) => {
            return this.normalizeKey(city.country_code || city.countryCode || '') !== removedCountryKey;
        });

        this.selectedDistricts = this.selectedDistricts.filter((district) => {
            return this.normalizeKey(district.country_code || district.countryCode || '') !== removedCountryKey;
        });

        this.refreshUi();
    }

    // ============================================================
    // VILLE
    // ============================================================

    searchCity() {
        const query = this.cityInputTarget.value.trim();

        this.debounce('city', async () => {
            if (query.length < this.minChars) {
                this.closeDropdown(this.cityInputTarget);
                return;
            }

            if (this.selectedCountries.length === 0) {
                this.closeDropdown(this.cityInputTarget);
                return;
            }

            const requests = this.selectedCountries
                .map((country) => {
                    const countryCode = country.code || country.country_code || '';

                    if (!countryCode) {
                        return null;
                    }

                    return this.fetchResults(`city:${countryCode}`, this.cityUrlValue, {
                        q: query,
                        country_code: countryCode,
                        country_name: country.label || country.country_name || '',
                    }).then((results) => {
                        return results.map((item) => ({
                            ...item,
                            country_code: item.country_code || countryCode,
                            country_name: item.country_name || country.label || country.country_name || '',
                        }));
                    });
                })
                .filter(Boolean);

            const responses = await Promise.allSettled(requests);

            const allResults = responses
                .filter((response) => response.status === 'fulfilled')
                .flatMap((response) => response.value);

            if (this.cityInputTarget.value.trim() !== query) {
                return;
            }

            const uniqueResults = this.uniqueBy(allResults, (item) => {
                return [
                    item.city_name || item.name || item.label || '',
                    item.country_code || '',
                    item.admin_name_1 || '',
                ].join('|');
            });

            this.renderDropdown(
                this.cityInputTarget,
                uniqueResults,
                (item) => this.getCityLabel(item),
                (item) => this.getCitySubtitle(item),
                (item) => this.addCity(item)
            );
        });
    }

    keyboardCity(event) {
        if (event.key !== 'Enter') {
            return;
        }

        event.preventDefault();

        const value = this.cityInputTarget.value.trim();

        if (!value || this.selectedCountries.length === 0) {
            return;
        }

        const firstCountry = this.selectedCountries[0];

        this.addCity({
            city_name: value,
            name: value,
            country_code: firstCountry.code || firstCountry.country_code || '',
            country_name: firstCountry.label || firstCountry.country_name || '',
        });
    }

    addCity(item) {
        const normalized = {
            ...item,
            city_name: this.getCityLabel(item),
            country_code: item.country_code || item.countryCode || '',
            country_name: item.country_name || item.countryName || '',
        };

        if (!normalized.city_name) {
            return;
        }

        const key = this.cityKey(normalized);

        const alreadyExists = this.selectedCities.some((city) => {
            return this.cityKey(city) === key;
        });

        if (alreadyExists) {
            this.cityInputTarget.value = '';
            this.closeDropdown(this.cityInputTarget);
            return;
        }

        this.selectedCities.push(normalized);

        this.cityInputTarget.value = '';
        this.closeDropdown(this.cityInputTarget);

        this.refreshUi();
    }

    removeCity(index) {
        const removedCity = this.selectedCities[index];

        this.selectedCities.splice(index, 1);

        const removedCityKey = this.cityKey(removedCity);

        this.selectedDistricts = this.selectedDistricts.filter((district) => {
            const districtCityKey = this.cityKey({
                city_name: district.city_name || district.cityName || '',
                country_code: district.country_code || district.countryCode || '',
            });

            return districtCityKey !== removedCityKey;
        });

        this.refreshUi();
    }

    // ============================================================
    // QUARTIER
    // ============================================================

    searchDistrict() {
        const query = this.districtInputTarget.value.trim();

        this.debounce('district', async () => {
            if (query.length < this.minChars) {
                this.closeDropdown(this.districtInputTarget);
                return;
            }

            if (this.selectedCities.length === 0) {
                this.closeDropdown(this.districtInputTarget);
                return;
            }

            const requests = this.selectedCities
                .map((city) => {
                    if (!city.city_name || !city.country_code) {
                        return null;
                    }

                    return this.fetchResults(`district:${city.country_code}:${city.city_name}`, this.districtUrlValue, {
                        q: query,
                        city_name: city.city_name,
                        country_code: city.country_code,
                        city_lat: city.lat || city.latitude || '',
                        city_lng: city.lng || city.lon || city.longitude || '',
                        admin_code_1: city.admin_code_1 || '',
                        admin_code_2: city.admin_code_2 || '',
                        admin_code_3: city.admin_code_3 || '',
                    }).then((results) => {
                        return results.map((item) => ({
                            ...item,
                            city_name: item.city_name || city.city_name,
                            country_code: item.country_code || city.country_code,
                            country_name: item.country_name || city.country_name,
                        }));
                    });
                })
                .filter(Boolean);

            const responses = await Promise.allSettled(requests);

            const allResults = responses
                .filter((response) => response.status === 'fulfilled')
                .flatMap((response) => response.value);

            if (this.districtInputTarget.value.trim() !== query) {
                return;
            }

            const uniqueResults = this.uniqueBy(allResults, (item) => {
                return [
                    item.name || item.district_name || item.label || '',
                    item.city_name || '',
                    item.country_code || '',
                ].join('|');
            });

            this.renderDropdown(
                this.districtInputTarget,
                uniqueResults,
                (item) => this.getDistrictLabel(item),
                (item) => this.getDistrictSubtitle(item),
                (item) => this.addDistrict(item)
            );
        });
    }

    keyboardDistrict(event) {
        if (event.key !== 'Enter') {
            return;
        }

        event.preventDefault();

        const value = this.districtInputTarget.value.trim();

        if (!value || this.selectedCities.length === 0) {
            return;
        }

        const firstCity = this.selectedCities[0];

        this.addDistrict({
            name: value,
            district_name: value,
            city_name: firstCity.city_name,
            country_code: firstCity.country_code,
            country_name: firstCity.country_name,
        });
    }

    addDistrict(item) {
        const normalized = {
            ...item,
            name: this.getDistrictLabel(item),
            city_name: item.city_name || '',
            country_code: item.country_code || '',
            country_name: item.country_name || '',
        };

        if (!normalized.name) {
            return;
        }

        const key = this.districtKey(normalized);

        const alreadyExists = this.selectedDistricts.some((district) => {
            return this.districtKey(district) === key;
        });

        if (alreadyExists) {
            this.districtInputTarget.value = '';
            this.closeDropdown(this.districtInputTarget);
            return;
        }

        this.selectedDistricts.push(normalized);

        this.districtInputTarget.value = '';
        this.closeDropdown(this.districtInputTarget);

        this.refreshUi();
    }

    removeDistrict(index) {
        this.selectedDistricts.splice(index, 1);
        this.refreshUi();
    }

    // ============================================================
    // FETCH
    // ============================================================

    async fetchResults(channel, url, params = {}) {
        if (!url) {
            console.warn(`[boolts-location] URL manquante pour ${channel}`);
            return [];
        }

        const requestUrl = new URL(url, window.location.origin);

        Object.entries(params).forEach(([key, value]) => {
            if (value !== null && value !== undefined && String(value).trim() !== '') {
                requestUrl.searchParams.set(key, value);
            }
        });

        const cacheKey = this.cacheKey(requestUrl);

        if (this.cache.has(cacheKey)) {
            return this.cache.get(cacheKey);
        }

        this.abortRequest(channel);

        const controller = new AbortController();
        this.abortControllers.set(channel, controller);

        try {
            const response = await fetch(requestUrl.toString(), {
                method: 'GET',
                signal: controller.signal,
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                console.warn(`[boolts-location] Erreur HTTP ${response.status}`, requestUrl.toString());
                return [];
            }

            const data = await response.json();

            let results = [];

            if (Array.isArray(data)) {
                results = data;
            } else if (Array.isArray(data.results)) {
                results = data.results;
            }

            console.log(`[boolts-location] ${channel}`, requestUrl.toString(), results);

            this.cache.set(cacheKey, results);

            return results;
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error(`[boolts-location] Erreur fetch ${channel}`, error);
            }

            return [];
        } finally {
            if (this.abortControllers.get(channel) === controller) {
                this.abortControllers.delete(channel);
            }
        }
    }

    cacheKey(url) {
        const params = Array.from(url.searchParams.entries())
            .sort(([a], [b]) => a.localeCompare(b));

        return `${url.pathname}?${new URLSearchParams(params).toString()}`;
    }

    abortRequest(channel) {
        const controller = this.abortControllers.get(channel);

        if (controller) {
            controller.abort();
            this.abortControllers.delete(channel);
        }
    }

    debounce(key, callback) {
        if (this.timers[key]) {
            clearTimeout(this.timers[key]);
        }

        this.timers[key] = setTimeout(callback, this.debounceDelay);
    }

    // ============================================================
    // UI
    // ============================================================

    refreshUi() {
        this.cityInputTarget.disabled = this.selectedCountries.length === 0;
        this.districtInputTarget.disabled = this.selectedCities.length === 0;

        this.renderChips(
            this.countryListTarget,
            this.selectedCountries,
            (item) => item.label || item.country_name || '',
            (index) => this.removeCountry(index)
        );

        this.renderChips(
            this.cityListTarget,
            this.selectedCities,
            (item) => item.city_name || this.getCityLabel(item),
            (index) => this.removeCity(index)
        );

        this.renderChips(
            this.districtListTarget,
            this.selectedDistricts,
            (item) => item.name || this.getDistrictLabel(item),
            (index) => this.removeDistrict(index)
        );

        this.syncHiddenFields();
    }

    renderChips(container, items, labelCallback, removeCallback) {
        container.innerHTML = '';

        if (items.length === 0) {
            container.hidden = true;
            return;
        }

        container.hidden = false;

        const fragment = document.createDocumentFragment();

        items.forEach((item, index) => {
            const label = labelCallback(item);

            if (!label) {
                return;
            }

            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'boolts-selected-chip';

            const text = document.createElement('span');
            text.className = 'boolts-selected-chip-text';
            text.textContent = label;

            const close = document.createElement('span');
            close.className = 'boolts-selected-chip-close';
            close.setAttribute('aria-hidden', 'true');
            close.innerHTML = '&times;';

            button.appendChild(text);
            button.appendChild(close);

            button.addEventListener('click', () => {
                removeCallback(index);
            });

            fragment.appendChild(button);
        });

        container.appendChild(fragment);
    }

    renderDropdown(input, items, titleCallback, subtitleCallback, selectCallback) {
        const dropdown = this.getDropdown(input);

        dropdown.innerHTML = '';

        if (!items.length) {
            this.closeDropdown(input);
            return;
        }

        const fragment = document.createDocumentFragment();

        items.slice(0, this.maxResults).forEach((item) => {
            const titleText = titleCallback(item);

            if (!titleText) {
                return;
            }

            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'boolts-location-suggestion';

            const title = document.createElement('strong');
            title.textContent = titleText;

            const subtitleText = subtitleCallback(item);

            button.appendChild(title);

            if (subtitleText) {
                const subtitle = document.createElement('small');
                subtitle.textContent = subtitleText;
                button.appendChild(subtitle);
            }

            button.addEventListener('click', () => {
                selectCallback(item);
            });

            fragment.appendChild(button);
        });

        dropdown.appendChild(fragment);
        dropdown.hidden = dropdown.children.length === 0;
    }

    getDropdown(input) {
        if (this.dropdowns.has(input)) {
            return this.dropdowns.get(input);
        }

        const dropdown = document.createElement('div');
        dropdown.className = 'boolts-location-suggestions';
        dropdown.hidden = true;

        const parent = input.closest('.boolts-search-control') || input.parentElement;

        parent.insertAdjacentElement('afterend', dropdown);

        this.dropdowns.set(input, dropdown);

        return dropdown;
    }

    closeDropdown(input) {
        const dropdown = this.dropdowns.get(input);

        if (dropdown) {
            dropdown.hidden = true;
            dropdown.innerHTML = '';
        }
    }

    closeAllDropdowns() {
        this.dropdowns.forEach((dropdown) => {
            dropdown.hidden = true;
            dropdown.innerHTML = '';
        });
    }

    syncHiddenFields() {
        if (this.hasCountryHiddenTarget) {
            this.countryHiddenTarget.value = JSON.stringify(
                this.selectedCountries
            );
        }

        if (this.hasCityHiddenTarget) {
            this.cityHiddenTarget.value = JSON.stringify(
                this.selectedCities
            );
        }

        if (this.hasDistrictHiddenTarget) {
            this.districtHiddenTarget.value = JSON.stringify(
                this.selectedDistricts
            );
        }

        window.dispatchEvent(
            new CustomEvent('boolts-location:changed', {
                detail: {
                    countries: this.selectedCountries,
                    cities: this.selectedCities,
                    districts: this.selectedDistricts,
                },
            })
        );
    }

    // ============================================================
    // LABELS
    // ============================================================

    getCountryLabel(item) {
        return item.label
            || item.name
            || item.country_name
            || item.countryName
            || item.nom
            || '';
    }

    getCountryCode(item) {
        return String(
            item.code
            || item.country_code
            || item.countryCode
            || item.iso
            || item.iso2
            || this.getCountryLabel(item)
        ).toUpperCase();
    }

    getCountrySubtitle(item) {
        const code = this.getCountryCode(item);
        const label = this.getCountryLabel(item);

        return code && code !== label ? code : '';
    }

    getCityLabel(item) {
        return item.city_name
            || item.cityName
            || item.name
            || item.label
            || item.nom
            || '';
    }

    getCitySubtitle(item) {
        return [
            item.admin_name_2,
            item.admin_name_1,
            item.country_name || item.country_code,
        ].filter(Boolean).join(' — ');
    }

    getDistrictLabel(item) {
        return item.name
            || item.district_name
            || item.districtName
            || item.neighbourhood_name
            || item.neighborhood_name
            || item.label
            || '';
    }

    getDistrictSubtitle(item) {
        return [
            item.city_name,
            item.admin_name_2,
            item.admin_name_1,
            item.country_name || item.country_code,
        ].filter(Boolean).join(' — ');
    }

    // ============================================================
    // KEYS
    // ============================================================

    cityKey(item) {
        return this.normalizeKey([
            item.city_name || item.cityName || item.name || '',
            item.country_code || item.countryCode || '',
            item.admin_name_1 || item.adminName1 || '',
        ].join('|'));
    }

    districtKey(item) {
        return this.normalizeKey([
            item.name || item.district_name || item.districtName || '',
            item.city_name || item.cityName || '',
            item.country_code || item.countryCode || '',
        ].join('|'));
    }

    normalizeKey(value) {
        return String(value || '')
            .trim()
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/\s+/g, ' ');
    }

    uniqueBy(items, keyCallback) {
        const map = new Map();

        items.forEach((item) => {
            const key = this.normalizeKey(keyCallback(item));

            if (!key) {
                return;
            }

            if (!map.has(key)) {
                map.set(key, item);
            }
        });

        return Array.from(map.values());
    }

    parseJsonValue(value) {
        if (!value) {
            return [];
        }

        try {
            const parsed = JSON.parse(value);

            return Array.isArray(parsed) ? parsed : [];
        } catch (error) {
            return [];
        }
    }

    reset() {
        this.selectedCountries = [];
        this.selectedCities = [];
        this.selectedDistricts = [];

        if (this.hasCountryInputTarget) {
            this.countryInputTarget.value = '';
        }

        if (this.hasCityInputTarget) {
            this.cityInputTarget.value = '';
        }

        if (this.hasDistrictInputTarget) {
            this.districtInputTarget.value = '';
        }

        this.closeAllDropdowns();

        this.refreshUi();
    }

    resetFromEvent(event) {
        event.preventDefault();

        this.reset();
    }

    injectStyle() {
        if (document.querySelector('[data-boolts-location-style]')) {
            return;
        }

        const style = document.createElement('style');
        style.setAttribute('data-boolts-location-style', 'true');

        style.textContent = `
            .boolts-location-suggestions {
                position: relative;
                z-index: 9999;
                margin-top: 8px;
                border: 1px solid rgba(15, 23, 42, .08);
                border-radius: 14px;
                background: #fff;
                box-shadow: 0 18px 45px rgba(15, 23, 42, .12);
                overflow: hidden;
            }

            .boolts-location-suggestion {
                width: 100%;
                display: flex;
                flex-direction: column;
                gap: 3px;
                padding: 11px 14px;
                border: 0;
                border-bottom: 1px solid rgba(15, 23, 42, .06);
                background: #fff;
                text-align: left;
                cursor: pointer;
            }

            .boolts-location-suggestion:hover {
                background: #f8fafc;
            }

            .boolts-location-suggestion strong {
                font-size: 14px;
                font-weight: 700;
                color: #111827;
            }

            .boolts-location-suggestion small {
                font-size: 12px;
                color: #6b7280;
            }

            .boolts-selected-list[hidden] {
                display: none !important;
            }

            .boolts-selected-list {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                margin-top: 10px;
            }

            .boolts-selected-chip {
                display: flex;
                height: 44px;
                padding: 10px 16px;
                justify-content: center;
                align-items: center;
                gap: 10px;
                border-radius: 50px;
                border: 2px solid #5D00FF;
                background: #FFF;
            }

            .boolts-selected-chip:hover {
                background: rgba(93, 0, 255, .12);
            }

            .boolts-selected-chip-close {
                font-size: 16px;
                line-height: 1;
            }

            .boolts-search-control input:disabled {
                opacity: .55;
                cursor: not-allowed;
                background-color: #f8fafc;
            }
        `;

        document.head.appendChild(style);
    }
}
