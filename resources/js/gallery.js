const reducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

// Video hero hanya diputar otomatis di layar lebar dan bila pengunjung tidak mengaktifkan mode hemat data.
const canAutoplayVideo = () => !reducedMotion() && !navigator.connection?.saveData && window.matchMedia('(min-width: 768px)').matches;

// Hero beranda: foto/video bergantian setiap beberapa detik.
export const heroSlider = ({ count, interval = 6000 }) => ({
    active: 0,
    count,
    timer: null,
    init() {
        if (this.count > 1 && !reducedMotion()) this.start();
    },
    start() {
        this.timer = setInterval(() => this.go((this.active + 1) % this.count, false), interval);
    },
    go(i, manual = true) {
        this.active = i;
        if (manual && this.timer) {
            clearInterval(this.timer);
            this.start();
        }
    },
    loadVideo(el) {
        if (!canAutoplayVideo()) return; // cukup tampilkan poster
        el.src = el.dataset.src;
        el.play().catch(() => {});
    },
});

// Galeri: klik untuk memperbesar foto atau memutar video.
export const lightbox = (items) => ({
    items,
    index: null,
    get current() {
        return this.index === null ? null : this.items[this.index];
    },
    open(i) {
        this.index = i;
        document.body.style.overflow = 'hidden';
    },
    close() {
        this.index = null;
        document.body.style.overflow = '';
    },
    next() {
        if (this.index !== null) this.index = (this.index + 1) % this.items.length;
    },
    prev() {
        if (this.index !== null) this.index = (this.index - 1 + this.items.length) % this.items.length;
    },
});
