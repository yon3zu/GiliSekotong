import './bootstrap';
import Alpine from 'alpinejs';
import bookingForm from './booking-form';
import { heroSlider, lightbox } from './gallery';

Alpine.data('bookingForm', bookingForm);
Alpine.data('heroSlider', heroSlider);
Alpine.data('lightbox', lightbox);

// Visualisasi pohon keputusan: posisikan akar di tengah, atau perkecil agar seluruh pohon muat.
Alpine.data('treeView', () => ({
    fit: false,
    scale: 1,
    init() {
        this.$nextTick(() => this.center());
    },
    center() {
        const box = this.$refs.box;
        box.scrollLeft = (box.scrollWidth - box.clientWidth) / 2;
    },
    toggle() {
        this.fit = !this.fit;
        this.scale = Math.min(1, this.$refs.box.clientWidth / this.$refs.tree.scrollWidth);
        this.$nextTick(() => this.center());
    },
}));

window.Alpine = Alpine;
Alpine.start();
