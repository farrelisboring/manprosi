import QRCode from 'qrcode';

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

const setupPollers = () => {
    const pollers = document.querySelectorAll('[data-poller]');

    pollers.forEach((poller) => {
        const toggle = poller.querySelector('[data-poll-toggle]');
        const refreshUrl = poller.getAttribute('data-refresh-url');
        const refreshContainer = poller.getAttribute('data-refresh-container');
        const container = refreshContainer ? document.querySelector(refreshContainer) : null;
        const isDisabled = poller.getAttribute('data-poll-disabled') === 'true';

        if (!toggle || !container || !refreshUrl || isDisabled) {
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

const setupQrLabelPreviews = () => {
    const previews = document.querySelectorAll('[data-qr-preview]');

    previews.forEach((preview) => {
        const shortUrl = preview.getAttribute('data-short-url');
        const canvasHost = preview.querySelector('[data-qr-canvas-host]');
        const emptyState = preview.querySelector('[data-qr-empty-state]');

        if (!canvasHost) {
            return;
        }

        canvasHost.innerHTML = '';

        if (!shortUrl) {
            if (emptyState) {
                emptyState.classList.remove('hidden');
            }

            return;
        }

        if (emptyState) {
            emptyState.classList.add('hidden');
        }

        const canvas = document.createElement('canvas');
        canvas.className = 'h-44 w-44';
        canvas.setAttribute('aria-label', 'QR code preview');
        canvasHost.appendChild(canvas);

        QRCode.toCanvas(canvas, shortUrl, {
            margin: 1,
            width: 176,
            color: {
                dark: '#111827',
                light: '#F9FAFB',
            },
        }).catch(() => {
            canvas.remove();

            if (emptyState) {
                emptyState.classList.remove('hidden');
                emptyState.textContent = 'QR preview could not be rendered.';
            }
        });
    });
};

document.addEventListener('DOMContentLoaded', () => {
    setupMapDependentAssetForms();
    setupPollers();
    setupQrLabelPreviews();
});
