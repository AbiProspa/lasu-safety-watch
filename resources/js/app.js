import './bootstrap';
import Alpine from 'alpinejs';
import L from 'leaflet';
import Chart from 'chart.js/auto';

window.L = L;
window.Chart = Chart;

// Free OpenStreetMap tiles (no API key needed)
const TILE_URL = 'https://tile.openstreetmap.org/{z}/{x}/{y}.png';
const TILE_ATTR = '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';

const csrf = () => document.querySelector('meta[name="csrf-token"]').content;

function baseMap(el, center) {
    const map = L.map(el, { scrollWheelZoom: false }).setView([center.lat, center.lng], center.zoom || 16);
    L.tileLayer(TILE_URL, { attribution: TILE_ATTR, maxZoom: 19 }).addTo(map);
    return map;
}

function pin(color, size = 18) {
    return L.divIcon({
        className: '',
        html: `<div class="lsw-pin" style="background:${color};width:${size}px;height:${size}px"></div>`,
        iconSize: [size, size],
        iconAnchor: [size / 2, size / 2],
    });
}

/* ---------- Report form: pick a location on the map ---------- */
Alpine.data('locationPicker', (center, locations, initial = {}) => ({
    lat: initial.lat || '',
    lng: initial.lng || '',
    locationName: initial.name || '',
    locating: false,
    message: '',
    map: null,
    marker: null,
    init() {
        this.map = baseMap(this.$refs.map, center);
        this.map.on('click', (e) => this.setPoint(e.latlng.lat, e.latlng.lng, 'Pin placed on the map.'));
        if (this.lat) this.setPoint(this.lat, this.lng);
        this.$watch('locationName', (name) => {
            const p = locations[name];
            if (p) { this.setPoint(p[0], p[1]); this.map.setView(p, 17); }
        });
    },
    setPoint(lat, lng, msg = '') {
        this.lat = (+lat).toFixed(6);
        this.lng = (+lng).toFixed(6);
        this.message = msg;
        if (this.marker) this.marker.setLatLng([lat, lng]);
        else this.marker = L.marker([lat, lng], { icon: pin('#dc2626', 22), draggable: true }).addTo(this.map)
            .on('dragend', (e) => { const p = e.target.getLatLng(); this.setPoint(p.lat, p.lng, 'Pin moved.'); });
    },
    useMyLocation() {
        if (!navigator.geolocation) { this.message = 'Your browser cannot share location.'; return; }
        this.locating = true;
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                this.locating = false;
                this.setPoint(pos.coords.latitude, pos.coords.longitude, 'Using your current location.');
                this.map.setView([pos.coords.latitude, pos.coords.longitude], 17);
            },
            () => { this.locating = false; this.message = 'Location permission denied. Tap the map instead.'; },
            { enableHighAccuracy: true, timeout: 8000 },
        );
    },
}));

/* ---------- Public incident map ---------- */
Alpine.data('incidentMap', (center, dataUrl) => ({
    reports: [],
    hidden: [],
    days: 0,
    loading: true,
    map: null,
    layer: null,
    async init() {
        this.map = baseMap(this.$refs.map, center);
        this.map.scrollWheelZoom.enable();
        this.layer = L.layerGroup().addTo(this.map);
        await this.load();
        this.$watch('days', () => this.load());
    },
    async load() {
        this.loading = true;
        const res = await fetch(`${dataUrl}?days=${this.days}`);
        this.reports = await res.json();
        this.loading = false;
        this.draw();
    },
    toggle(id) {
        this.hidden = this.hidden.includes(id) ? this.hidden.filter((h) => h !== id) : [...this.hidden, id];
        this.draw();
    },
    visible() { return this.reports.filter((r) => !this.hidden.includes(r.category_id)); },
    draw() {
        this.layer.clearLayers();
        this.visible().forEach((r) => {
            L.marker([r.lat, r.lng], { icon: pin(r.color, r.severity === 'critical' ? 24 : 18) })
                .bindPopup(`<div style="min-width:200px">
                    <div style="font-size:11px;font-weight:700;color:${r.color};text-transform:uppercase;letter-spacing:.04em">${r.category}</div>
                    <div style="font-weight:700;font-size:14px;margin:2px 0 6px">${r.title}</div>
                    <div style="font-size:12px;color:#475569">${r.location || ''} · ${r.when}</div>
                    <div style="font-size:12px;margin-top:6px"><b>Status:</b> ${r.status_label}</div>
                    <a href="/track?ref=${r.ref}" style="font-size:12px;font-weight:600">${r.ref} →</a></div>`)
                .addTo(this.layer);
        });
    },
    count(id) { return this.reports.filter((r) => r.category_id === id).length; },
}));

/* ---------- Small read-only map (report details) ---------- */
Alpine.data('miniMap', (lat, lng, color) => ({
    init() {
        const map = baseMap(this.$refs.map, { lat, lng, zoom: 17 });
        L.marker([lat, lng], { icon: pin(color, 22) }).addTo(map);
    },
}));

/* ---------- SOS panic button ---------- */
Alpine.data('sosButton', (url, center) => ({
    stage: 'idle', // idle | countdown | sending | sent | error
    count: 5,
    note: '',
    reference: '',
    exact: false,
    timer: null,
    start() {
        this.stage = 'countdown';
        this.count = 5;
        this.timer = setInterval(() => { if (--this.count <= 0) this.send(); }, 1000);
    },
    cancel() { clearInterval(this.timer); this.stage = 'idle'; },
    position() {
        return new Promise((resolve) => {
            if (!navigator.geolocation) return resolve(null);
            navigator.geolocation.getCurrentPosition(
                (p) => resolve({ latitude: p.coords.latitude, longitude: p.coords.longitude }),
                () => resolve(null), { enableHighAccuracy: true, timeout: 8000 });
        });
    },
    async send() {
        clearInterval(this.timer);
        this.stage = 'sending';
        try {
            const pos = await this.position();
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
                body: JSON.stringify({ ...(pos || {}), note: this.note }),
            });
            if (!res.ok) throw new Error(res.status === 429 ? 'Too many alerts sent. Please call Campus Security.' : 'Server error');
            const data = await res.json();
            this.reference = data.reference;
            this.exact = data.exact;
            this.stage = 'sent';
            this.note = '';
        } catch (e) {
            this.stage = 'error';
            this.reference = e.message;
        }
    },
}));

/* ---------- Live SOS alerts on the staff dashboard ---------- */
Alpine.data('sosAlerts', (url) => ({
    alerts: [],
    seen: new Set(),
    init() { this.poll(); setInterval(() => this.poll(), 15000); },
    async poll() {
        try {
            const res = await fetch(url, { headers: { Accept: 'application/json' } });
            const list = await res.json();
            const fresh = list.some((a) => !this.seen.has(a.id));
            list.forEach((a) => this.seen.add(a.id));
            if (fresh && this.alerts.length < list.length) this.beep();
            this.alerts = list;
        } catch (e) { /* offline */ }
    },
    beep() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            [0, 0.25, 0.5].forEach((t) => {
                const o = ctx.createOscillator(); const g = ctx.createGain();
                o.frequency.value = 880; o.connect(g); g.connect(ctx.destination);
                g.gain.setValueAtTime(0.15, ctx.currentTime + t);
                o.start(ctx.currentTime + t); o.stop(ctx.currentTime + t + 0.15);
            });
        } catch (e) { /* audio blocked */ }
    },
}));

/* ---------- Charts ---------- */
Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
Chart.defaults.color = '#64748b';
window.lswChart = (el, config) => new Chart(el, config);

window.Alpine = Alpine;
Alpine.start();
