const setupMapDependentAssetForms = () => {
    const forms = document.querySelectorAll('[data-map-dependent-form]');

    forms.forEach((form) => {
        const locationSelect = form.querySelector('[data-location-select]');
        const mapSelect = form.querySelector('[data-map-select]');

        if (!locationSelect || !mapSelect) {
            return;
        }

        const rawOptions = form.getAttribute('data-map-options') ?? '{}';
        const mapOptions = JSON.parse(rawOptions);
        const initialSelectedMap = mapSelect.dataset.selectedMap ?? '';

        const renderMapOptions = (locationId, selectedMapId = '') => {
            const maps = mapOptions[locationId] ?? [];
            const hasLocation = locationId !== '';
            const hasMaps = maps.length > 0;
            const placeholder = !hasLocation
                ? 'Choose a location first'
                : hasMaps
                    ? 'No map placement'
                    : 'No maps available for this location';

            mapSelect.innerHTML = '';

            const placeholderOption = document.createElement('option');
            placeholderOption.value = '';
            placeholderOption.textContent = placeholder;
            mapSelect.appendChild(placeholderOption);

            maps.forEach((map) => {
                const option = document.createElement('option');
                option.value = `${map.id}`;
                option.textContent = map.name;

                if (`${selectedMapId}` === `${map.id}`) {
                    option.selected = true;
                }

                mapSelect.appendChild(option);
            });

            mapSelect.disabled = !hasLocation || !hasMaps;
        };

        renderMapOptions(locationSelect.value, initialSelectedMap);

        locationSelect.addEventListener('change', () => {
            renderMapOptions(locationSelect.value);
        });
    });
};

const setupTrackingPollers = () => {
    const pollers = document.querySelectorAll('[data-tracking-poller]');

    pollers.forEach((poller) => {
        const toggle = poller.querySelector('[data-poll-toggle]');
        const container = document.querySelector('[data-tracking-panel-container]');
        const refreshUrl = poller.getAttribute('data-refresh-url');

        if (!toggle || !container || !refreshUrl) {
            return;
        }

        let intervalId = null;

        const refreshPanel = async () => {
            const url = new URL(refreshUrl, window.location.origin);
            url.search = window.location.search;

            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                return;
            }

            container.innerHTML = await response.text();
        };

        const stopPolling = () => {
            if (intervalId !== null) {
                window.clearInterval(intervalId);
                intervalId = null;
            }

            toggle.textContent = 'Off';
            toggle.setAttribute('aria-pressed', 'false');
        };

        const startPolling = () => {
            stopPolling();

            toggle.textContent = 'On';
            toggle.setAttribute('aria-pressed', 'true');
            intervalId = window.setInterval(refreshPanel, 5000);
        };

        toggle.addEventListener('click', () => {
            if (intervalId !== null) {
                stopPolling();
                return;
            }

            startPolling();
        });
    });
};

document.addEventListener('DOMContentLoaded', () => {
    setupMapDependentAssetForms();
    setupTrackingPollers();
});
