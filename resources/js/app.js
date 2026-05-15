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

document.addEventListener('DOMContentLoaded', setupMapDependentAssetForms);
