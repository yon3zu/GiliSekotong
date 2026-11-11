// Kalkulator pemesanan di halaman detail perahu.
export default ({ tripPrice, capacity, meals, rentals, availabilityUrl, initial }) => ({
    tripPrice,
    capacity,
    meals,
    rentals,
    tripType: initial.trip_type ?? 'gili',
    date: initial.trip_date ?? '',
    time: initial.departure_time ?? '08:30',
    passengers: initial.passengers ?? Math.min(4, capacity),
    mealQty: Object.fromEntries(meals.map((m) => [m.id, initial.meals?.[m.id] ?? 0])),
    rentalQty: Object.fromEntries(rentals.map((r) => [r.id, initial.rentals?.[r.id] ?? 0])),
    check: null,
    checking: false,

    sum(items, qty) {
        return items.reduce((total, item) => total + item.price * (qty[item.id] || 0), 0);
    },
    get mealTotal() {
        return this.sum(this.meals, this.mealQty);
    },
    get mealCount() {
        return Object.values(this.mealQty).reduce((a, b) => a + (b || 0), 0);
    },
    get rentalTotal() {
        return this.sum(this.rentals, this.rentalQty);
    },
    get total() {
        return this.tripPrice + this.mealTotal + this.rentalTotal;
    },
    get perPerson() {
        return this.passengers > 0 ? Math.ceil(this.total / this.passengers) : 0;
    },
    get blocked() {
        return this.check && (this.check.booked || this.check.tone === 'tidak');
    },

    rupiah(n) {
        return 'Rp' + Number(n).toLocaleString('id-ID');
    },
    clamp(value, max) {
        return Math.max(0, Math.min(max, parseInt(value) || 0));
    },
    setPassengers(value) {
        this.passengers = Math.max(1, this.clamp(value, this.capacity));
        // Tiap alat sewa paling banyak satu per penumpang.
        for (const id in this.rentalQty) this.rentalQty[id] = this.clamp(this.rentalQty[id], this.passengers);
    },
    stepMeal(id, delta) {
        this.mealQty[id] = this.clamp((this.mealQty[id] || 0) + delta, 20);
    },
    stepRental(id, delta) {
        this.rentalQty[id] = this.clamp((this.rentalQty[id] || 0) + delta, this.passengers);
    },
    async checkDate() {
        if (!this.date) return (this.check = null);
        this.checking = true;
        try {
            const res = await fetch(`${availabilityUrl}?date=${encodeURIComponent(this.date)}`, {
                headers: { Accept: 'application/json' },
            });
            this.check = res.ok ? await res.json() : null;
        } catch {
            this.check = null;
        } finally {
            this.checking = false;
        }
    },
    init() {
        if (this.date) this.checkDate();
    },
});
