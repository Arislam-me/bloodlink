import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';
import { renderIcons, watchIcons } from './icons';
import locationPicker from './components/location-picker';
import { mapPicker, mapView } from './components/map';

Alpine.plugin(focus);

Alpine.data('locationPicker', locationPicker);
Alpine.data('mapPicker', mapPicker);
Alpine.data('mapView', mapView);

/** Disable a form's submit button and show a spinner while submitting (prevents double posts). */
Alpine.data('submitting', () => ({
    busy: false,
    submit(e) {
        if (this.busy) { e.preventDefault(); return; }
        this.busy = true;
    },
}));

/** Auto-dismissing flash toast. */
Alpine.data('toast', (timeout = 6000) => ({
    show: true,
    init() { if (timeout) setTimeout(() => (this.show = false), timeout); },
}));

window.Alpine = Alpine;

// Swap icon placeholders for SVGs *before* Alpine binds, so directives on icons stay live.
renderIcons();
Alpine.start();
watchIcons();
