document.addEventListener('alpine:init', () => {
    Alpine.data('dashboard', () => ({
        ranges: [
            { value: 7, label: '7 Hari' },
            { value: 30, label: '30 Hari' },
            { value: 90, label: '90 Hari' },
            { value: 0, label: 'Semua' },
        ],
        range: 30,
        chartType: 'bar',
        search: '',
        statusFilter: '',
        loading: false,
        data: null,
        charts: {},
        statusMap: {
            pending: 'Menunggu',
            paid: 'Dibayar',
            shipped: 'Dikirim',
            delivered: 'Selesai',
            cancelled: 'Dibatalkan',
        },
        methodNames: {
            transfer: 'Transfer Bank',
            qris: 'QRIS',
            ewallet: 'E-Wallet',
            cod: 'COD',
        },
        kpis: [],

        get rangeLabel() {
            const r = this.ranges.find((r) => r.value === this.range);
            return r ? r.label : '';
        },

        get filteredOrders() {
            if (!this.data) return [];
            const q = this.search.toLowerCase();
            return this.data.recent_orders.filter((o) => {
                const matchSearch =
                    !q ||
                    o.order_number.toLowerCase().includes(q) ||
                    o.customer.toLowerCase().includes(q) ||
                    o.city.toLowerCase().includes(q);
                const matchStatus = !this.statusFilter || o.status === this.statusFilter;
                return matchSearch && matchStatus;
            });
        },

        get lowStock() {
            return this.data ? this.data.low_stock : [];
        },

        init() {
            this.load();
        },

        setRange(v) {
            if (this.range === v) return;
            this.range = v;
            this.load();
        },

        async load() {
            this.loading = true;
            try {
                const res = await fetch(`/api/dashboard?range=${this.range}`);
                this.data = await res.json();
                this.buildKpis();
                this.$nextTick(() => this.renderCharts());
            } finally {
                this.loading = false;
            }
        },

        buildKpis() {
            const k = this.data.kpi;
            this.kpis = [
                {
                    key: 'revenue',
                    label: 'Pendapatan',
                    icon: '💰',
                    bg: 'bg-emerald-50',
                    value: this.fmt(k.revenue),
                    sub: `Rata-rata pesanan ${this.fmt(k.avg_order)}`,
                    subClass: 'text-emerald-600',
                },
                {
                    key: 'orders',
                    label: 'Pesanan',
                    icon: '🧾',
                    bg: 'bg-indigo-50',
                    value: this.fmtInt(k.orders),
                    sub: 'Pesanan valid (non-batal)',
                    subClass: 'text-indigo-600',
                },
                {
                    key: 'customers',
                    label: 'Pelanggan Baru',
                    icon: '👥',
                    bg: 'bg-violet-50',
                    value: this.fmtInt(k.new_customers),
                    sub: 'Terdaftar pada periode',
                    subClass: 'text-violet-600',
                },
                {
                    key: 'lowstock',
                    label: 'Stok Menipis',
                    icon: '⚠️',
                    bg: 'bg-red-50',
                    value: this.fmtInt(k.low_stock_count),
                    sub: 'Produk perlu restock (stok ≤ 5)',
                    subClass: 'text-red-600',
                },
            ];
        },

        renderCharts() {
            this.monthly();
            this.categories();
            this.methods();
            this.topProducts();
        },

        monthly() {
            const el = document.getElementById('monthlyChart');
            if (!el || !this.data) return;
            this.destroy('monthly');
            const d = this.data.monthly;
            const isBar = this.chartType === 'bar';
            this.charts.monthly = new Chart(el, {
                type: isBar ? 'bar' : 'line',
                data: {
                    labels: d.labels,
                    datasets: [{
                        label: 'Pendapatan',
                        data: d.totals,
                        backgroundColor: 'rgba(99, 102, 241, 0.75)',
                        borderColor: '#6366f1',
                        fill: !isBar,
                        tension: 0.35,
                        borderRadius: 6,
                        borderWidth: 2,
                    }],
                },
                options: this.baseOptions({
                    interaction: { intersect: false, mode: 'index' },
                    scales: {
                        y: { beginAtZero: true, ticks: { callback: (v) => this.compact(v) } },
                    },
                }),
            });
        },

        categories() {
            const el = document.getElementById('categoryChart');
            if (!el || !this.data) return;
            this.destroy('category');
            const d = this.data.categories;
            this.charts.category = new Chart(el, {
                type: 'doughnut',
                data: {
                    labels: d.map((c) => c.name),
                    datasets: [{
                        data: d.map((c) => c.total),
                        backgroundColor: d.map((c) => c.color),
                        borderWidth: 2,
                        borderColor: '#fff',
                    }],
                },
                options: this.baseOptions({
                    cutout: '62%',
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
                        tooltip: { callbacks: { label: (ctx) => ` ${ctx.label}: ${this.fmt(ctx.parsed)}` } },
                    },
                }),
            });
        },

        methods() {
            const el = document.getElementById('methodChart');
            if (!el || !this.data) return;
            this.destroy('method');
            const d = this.data.methods;
            const palette = ['#10b981', '#6366f1', '#f59e0b', '#ec4899'];
            this.charts.method = new Chart(el, {
                type: 'pie',
                data: {
                    labels: d.map((m) => m.label),
                    datasets: [{
                        data: d.map((m) => m.total),
                        backgroundColor: palette.slice(0, d.length),
                        borderWidth: 2,
                        borderColor: '#fff',
                    }],
                },
                options: this.baseOptions({
                    plugins: {
                        legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11 } } },
                        tooltip: { callbacks: { label: (ctx) => ` ${ctx.label}: ${this.fmt(ctx.parsed)}` } },
                    },
                }),
            });
        },

        topProducts() {
            const el = document.getElementById('topChart');
            if (!el || !this.data) return;
            this.destroy('top');
            const d = this.data.top_products;
            this.charts.top = new Chart(el, {
                type: 'bar',
                data: {
                    labels: d.map((p) => p.name),
                    datasets: [{
                        label: 'Unit terjual',
                        data: d.map((p) => p.qty),
                        backgroundColor: 'rgba(245, 158, 11, 0.8)',
                        borderColor: '#f59e0b',
                        borderWidth: 1,
                        borderRadius: 6,
                    }],
                },
                options: this.baseOptions({
                    indexAxis: 'y',
                    scales: {
                        x: { beginAtZero: true, ticks: { precision: 0 } },
                        y: { ticks: { font: { size: 10 } } },
                    },
                }),
            });
        },

        baseOptions(extra = {}) {
            return {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ` ${ctx.dataset.label ?? ''}: ${this.fmt(ctx.parsed.y ?? ctx.parsed)}`,
                        },
                    },
                },
                ...extra,
            };
        },

        destroy(key) {
            if (this.charts[key]) {
                this.charts[key].destroy();
                delete this.charts[key];
            }
        },

        fmt(n) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(n ?? 0);
        },

        fmtInt(n) {
            return new Intl.NumberFormat('id-ID').format(n ?? 0);
        },

        compact(n) {
            if (Math.abs(n) >= 1e9) return 'Rp' + (n / 1e9).toFixed(1) + ' M';
            if (Math.abs(n) >= 1e6) return 'Rp' + (n / 1e6).toFixed(1) + ' jt';
            if (Math.abs(n) >= 1e3) return 'Rp' + (n / 1e3).toFixed(0) + ' rb';
            return 'Rp' + n;
        },

        statusClass(s) {
            return {
                pending: 'bg-amber-100 text-amber-700',
                paid: 'bg-sky-100 text-sky-700',
                shipped: 'bg-indigo-100 text-indigo-700',
                delivered: 'bg-emerald-100 text-emerald-700',
                cancelled: 'bg-red-100 text-red-600',
            }[s] || 'bg-slate-100 text-slate-600';
        },

        methodLabel(m) {
            return this.methodNames[m] || m;
        },
    }));
});
