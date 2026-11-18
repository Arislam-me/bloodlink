/**
 * OpenStreetMap (Leaflet) components. Leaflet is lazy-loaded only on pages that
 * render a map, keeping the main bundle small.
 *
 * Privacy: maps are only used for HOSPITAL locations of emergency requests.
 * Donor locations are never plotted.
 */
const BD_CENTER = [23.685, 90.3563];
const TILE_URL = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
const ATTRIBUTION = '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';

let leafletPromise;
function loadLeaflet() {
    leafletPromise ??= Promise.all([import('leaflet'), import('leaflet/dist/leaflet.css')]).then(([m]) => m.default ?? m);
    return leafletPromise;
}

function pin(L) {
    return L.divIcon({
        className: '',
        html: '<div style="width:28px;height:28px;border-radius:50% 50% 50% 0;background:#a51531;transform:rotate(-45deg);border:3px solid #fff;box-shadow:0 4px 10px rgba(0,0,0,.3)"></div>',
        iconSize: [28, 28],
        iconAnchor: [14, 28],
    });
}

/** Interactive picker: click/drag to set the hospital location; search via Nominatim. */
export function mapPicker(initial = {}) {
    return {
        lat: initial.lat ?? '',
        lng: initial.lng ?? '',
        query: '',
        searching: false,
        results: [],
        message: '',
        map: null,
        marker: null,

        async init() {
            const L = await loadLeaflet();
            const start = this.lat && this.lng ? [Number(this.lat), Number(this.lng)] : BD_CENTER;
            this.map = L.map(this.$refs.map, { scrollWheelZoom: false }).setView(start, this.lat ? 15 : 7);
            L.tileLayer(TILE_URL, { maxZoom: 19, attribution: ATTRIBUTION }).addTo(this.map);
            if (this.lat && this.lng) this.place(L, start);

            this.map.on('click', (e) => this.place(L, [e.latlng.lat, e.latlng.lng]));
            window.addEventListener('location-changed', (e) => {
                if (!this.marker) this.map.setView([e.detail.lat, e.detail.lng], e.detail.zoom);
            });
        },

        place(L, [lat, lng]) {
            this.lat = Number(lat).toFixed(7);
            this.lng = Number(lng).toFixed(7);
            if (!this.marker) {
                this.marker = L.marker([lat, lng], { draggable: true, icon: pin(L) }).addTo(this.map);
                this.marker.on('dragend', () => {
                    const p = this.marker.getLatLng();
                    this.lat = p.lat.toFixed(7);
                    this.lng = p.lng.toFixed(7);
                });
            } else {
                this.marker.setLatLng([lat, lng]);
            }
        },

        async clear() {
            if (this.marker) { this.map.removeLayer(this.marker); this.marker = null; }
            this.lat = ''; this.lng = '';
        },

        async search() {
            if (this.query.trim().length < 3) return;
            this.searching = true; this.message = ''; this.results = [];
            try {
                const url = 'https://nominatim.openstreetmap.org/search?' + new URLSearchParams({
                    q: this.query, format: 'json', countrycodes: 'bd', limit: '5',
                });
                const res = await fetch(url, { headers: { 'Accept-Language': 'bn,en' } });
                this.results = res.ok ? await res.json() : [];
                if (!this.results.length) this.message = 'কোনো জায়গা পাওয়া যায়নি। হাসপাতালের নাম ও শহর লিখে দেখুন, অথবা ম্যাপে ক্লিক করুন।';
            } catch {
                this.message = 'এখন খোঁজা যাচ্ছে না। ম্যাপে ক্লিক করে জায়গা বেছে নিন।';
            } finally {
                this.searching = false;
            }
        },

        async choose(r) {
            const L = await loadLeaflet();
            this.place(L, [Number(r.lat), Number(r.lon)]);
            this.map.setView([Number(r.lat), Number(r.lon)], 16);
            this.results = [];
        },

        locate() {
            if (!navigator.geolocation) { this.message = 'আপনার ব্রাউজারে লোকেশন সুবিধা নেই।'; return; }
            this.message = 'লোকেশন খোঁজা হচ্ছে...';
            navigator.geolocation.getCurrentPosition(async (pos) => {
                const L = await loadLeaflet();
                this.place(L, [pos.coords.latitude, pos.coords.longitude]);
                this.map.setView([pos.coords.latitude, pos.coords.longitude], 16);
                this.message = 'আপনার বর্তমান জায়গায় পিন বসানো হয়েছে। দরকার হলে টেনে হাসপাতালের ওপর রাখুন।';
            }, () => { this.message = 'আপনার লোকেশন পাওয়া যায়নি।'; }, { enableHighAccuracy: true, timeout: 10000 });
        },
    };
}

/** Read-only map with a single marker. */
export function mapView(lat, lng, label = '') {
    return {
        async init() {
            const L = await loadLeaflet();
            const map = L.map(this.$el, { scrollWheelZoom: false }).setView([lat, lng], 15);
            L.tileLayer(TILE_URL, { maxZoom: 19, attribution: ATTRIBUTION }).addTo(map);
            const m = L.marker([lat, lng], { icon: pin(L) }).addTo(map);
            if (label) m.bindPopup(label);
        },
    };
}
