/**
 * Dependent Division > District > Upazila > Area dropdowns.
 * Data comes from the throttled, cached /api/locations endpoints.
 *
 * Emits `location-changed` (window event) with { lat, lng, zoom } so a map
 * on the same page can re-center when the user picks a district/upazila.
 */
const cache = new Map();

async function getJson(url) {
    if (cache.has(url)) return cache.get(url);
    const res = await fetch(url, { headers: { Accept: 'application/json' } });
    if (!res.ok) throw new Error(`Request failed (${res.status})`);
    const data = await res.json();
    cache.set(url, data);
    return data;
}

export default (initial = {}) => ({
    division: initial.division ? String(initial.division) : '',
    district: initial.district ? String(initial.district) : '',
    upazila: initial.upazila ? String(initial.upazila) : '',
    area: initial.area ? String(initial.area) : '',
    areaName: initial.areaName ?? '',
    withArea: initial.withArea ?? false,
    districts: [],
    upazilas: [],
    areas: [],
    loading: { districts: false, upazilas: false, areas: false },
    error: null,

    async init() {
        if (this.division) await this.loadDistricts(true);
        if (this.district) await this.loadUpazilas(true);
        if (this.withArea && this.upazila) await this.loadAreas(true);
    },

    async load(kind, url) {
        this.loading[kind] = true;
        this.error = null;
        try {
            this[kind] = await getJson(url);
        } catch (e) {
            this.error = 'এলাকার তালিকা আনা যায়নি। ইন্টারনেট সংযোগ দেখে আবার চেষ্টা করুন।';
            this[kind] = [];
        } finally {
            this.loading[kind] = false;
        }
    },

    async loadDistricts(keep = false) {
        if (!keep) { this.district = ''; this.upazila = ''; this.area = ''; }
        this.districts = []; this.upazilas = []; this.areas = [];
        if (!this.division) return;
        await this.load('districts', `/api/locations/divisions/${this.division}/districts`);
    },

    async loadUpazilas(keep = false) {
        if (!keep) { this.upazila = ''; this.area = ''; }
        this.upazilas = []; this.areas = [];
        if (!this.district) return;
        await this.load('upazilas', `/api/locations/districts/${this.district}/upazilas`);
        if (!keep) this.emit(this.districts.find((d) => String(d.id) === this.district), 11);
    },

    async loadAreas(keep = false) {
        if (!keep) this.area = '';
        this.areas = [];
        if (!this.upazila) return;
        this.emit(this.upazilas.find((u) => String(u.id) === this.upazila), 13);
        if (this.withArea) await this.load('areas', `/api/locations/upazilas/${this.upazila}/areas`);
    },

    emit(place, zoom) {
        if (place?.latitude && place?.longitude) {
            window.dispatchEvent(new CustomEvent('location-changed', {
                detail: { lat: Number(place.latitude), lng: Number(place.longitude), zoom },
            }));
        }
    },
});
