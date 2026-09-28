@props(['categories' => []])

@php
if (empty($categories) || !($categories instanceof \Illuminate\Support\Collection)) {
$categories = \App\Models\ProductCategory::withCount('products')->orderBy('name')->take(6)->get();
}
@endphp

<!-- Expanded Professional Footer -->
<footer class="bg-slate-900 text-white border-t border-slate-800 text-sm">

    <!-- Top Direct Callout Banner -->
    <div class="bg-gradient-to-r from-emerald-950 via-emerald-900 to-slate-950 py-10 px-4 sm:px-6 lg:px-8 border-b border-emerald-800/60">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="space-y-2 text-center md:text-left">
                <span class="px-3 py-1 rounded-md bg-amber-400 text-slate-950 font-semibold text-xs uppercase tracking-wider">DIRECT WHOLESALE YARD</span>
                <h3 class="text-xl sm:text-3xl font-semibold text-white">Need Large Bamboo Truckloads or Custom Scaffolding?</h3>
                <p class="text-slate-300 text-sm sm:text-base font-medium">Get instant wholesale rates and loading availability directly from our Karnal timber yard.</p>
            </div>

            <div class="flex flex-wrap items-center gap-4 shrink-0">
                <a href="https://wa.me/919254998000?text=Hello%20Ashok%20Baans%20Store%2C%20I%20want%20to%20inquire%20about%20wholesale%20rates."
                    target="_blank"
                    class="px-6 py-3.5 rounded-2xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold text-sm transition shadow-lg flex items-center gap-2">
                    <span>💬 WhatsApp Order</span>
                </a>
                <a href="tel:+919254998000"
                    class="px-6 py-3.5 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-semibold text-sm transition border border-white/20 flex items-center gap-2">
                    <span>📞 Call +91 92549 98000</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Multi-Column Footer Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-10">

            <!-- Column 1: Brand & Contact Info (4 cols) -->
            <div class="lg:col-span-4 space-y-4">
                <div class="flex items-center gap-3.5">
                    <div class="h-14 w-14 rounded-2xl bg-slate-950 border-2 border-emerald-500/80 p-1.5 flex items-center justify-center shadow-lg">
                        <img src="{{ asset('images/ak-emblem.png') }}"
                            alt="Ashok Kumar Baans Store Logo"
                            class="h-full w-full object-contain drop-shadow-md"
                            onerror="this.src='/images/ak-logo.png'" />
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold text-white tracking-tight">ASHOK KUMAR BAANS STORE</h4>
                        <span class="text-xs font-medium text-emerald-400 block">Direct Bamboo & Scaffolding Merchant</span>
                    </div>
                </div>

                <p class="text-slate-400 text-sm leading-relaxed font-medium">
                    Leading wholesale timber yard merchant supplying premium 15ft, 20ft & 25ft raw bamboo poles, scaffolding Ghodi trestles, woven Chaali platforms, and Siddi ladders across Karnal and Haryana.
                </p>

                <div class="space-y-2.5 text-slate-300 font-medium pt-2 text-xs sm:text-sm">
                    <div class="flex items-start gap-2.5">
                        <span class="text-emerald-400 text-base shrink-0">📍</span>
                        <span>House No 2755, Janak Puri, Opp. Gaushala Road, Karnal, Haryana - 132001</span>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-emerald-400 text-base shrink-0">📞</span>
                        <a href="tel:+919254998000" class="hover:text-emerald-400 transition font-semibold">+91 92549 98000 / +91 92555 23276</a>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-emerald-400 text-base shrink-0">💬</span>
                        <a href="https://wa.me/919254998000" target="_blank" class="hover:text-emerald-400 transition font-semibold text-emerald-400">WhatsApp Inquiry Active</a>
                    </div>
                </div>
            </div>

            <!-- Column 2: Quick Links (2 cols) -->
            <div class="lg:col-span-2 space-y-4">
                <h4 class="text-xs font-semibold uppercase tracking-widest text-emerald-400 border-b border-slate-800 pb-3">Quick Links</h4>
                <ul class="space-y-3 font-medium text-sm text-slate-300">
                    <li><a href="{{ route('home') }}" class="hover:text-emerald-400 transition flex items-center gap-2"><span>›</span> Home</a></li>
                    <li><a href="{{ route('catalog.index') }}" class="hover:text-emerald-400 transition flex items-center gap-2"><span>›</span> Products Catalog</a></li>
                    <li><a href="{{ route('about') }}" class="hover:text-emerald-400 transition flex items-center gap-2"><span>›</span> About Us</a></li>
                    <li><a href="{{ route('contact') }}" class="hover:text-emerald-400 transition flex items-center gap-2"><span>›</span> Contact Us</a></li>
                    @if(Route::has('login'))
                    <li><a href="{{ route('login') }}" class="hover:text-emerald-400 transition flex items-center gap-2"><span>›</span> Admin Portal</a></li>
                    @endif
                </ul>
            </div>

            <!-- Column 3: Categories (3 cols) -->
            <div class="lg:col-span-3 space-y-4">
                <h4 class="text-xs font-semibold uppercase tracking-widest text-emerald-400 border-b border-slate-800 pb-3">Top Categories</h4>
                <ul class="space-y-3 font-medium text-sm text-slate-300">
                    @forelse($categories as $cat)
                    <li>
                        <a href="{{ route('catalog.index', ['category_id' => $cat->id]) }}" class="hover:text-emerald-400 transition flex items-center justify-between">
                            <span>› {{ $cat->name }}</span>
                            <span class="text-xs text-slate-400 font-normal">({{ $cat->products_count }})</span>
                        </a>
                    </li>
                    @empty
                    <li><a href="{{ route('catalog.index', ['q' => 'Bamboo']) }}" class="hover:text-emerald-400 transition">› Raw Bamboo Poles</a></li>
                    <li><a href="{{ route('catalog.index', ['q' => 'Ghodi']) }}" class="hover:text-emerald-400 transition">› Scaffolding Ghodi</a></li>
                    <li><a href="{{ route('catalog.index', ['q' => 'Chaali']) }}" class="hover:text-emerald-400 transition">› Woven Bamboo Chaali</a></li>
                    @endforelse
                </ul>
            </div>

            <!-- Column 4: Yard Operating Hours & Trust (3 cols) -->
            <div class="lg:col-span-3 space-y-4">
                <h4 class="text-xs font-semibold uppercase tracking-widest text-emerald-400 border-b border-slate-800 pb-3">Yard Hours & Trust</h4>

                <div class="p-4 rounded-2xl bg-slate-800/80 border border-slate-800 space-y-2">
                    <span class="text-xs font-semibold text-amber-300 block uppercase tracking-wider">⏰ YARD WORKING HOURS</span>
                    <p class="text-slate-300 text-xs sm:text-sm font-medium">Monday – Sunday: <span class="text-white font-semibold">6:00 AM – 10:00 PM</span></p>
                    <p class="text-xs text-slate-400 font-normal">Ready yard loading staff available in Janak Puri, Karnal.</p>
                </div>

                <div class="space-y-2 text-slate-300 text-xs sm:text-sm font-medium pt-1">
                    <div class="flex items-center gap-2">
                        <span class="text-emerald-400">✓</span> Instant GST Invoice & Digital Ledger
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-emerald-400">✓</span> Direct Wholesale Yard Rates
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-emerald-400">✓</span> Construction Grade Timber Stock
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Bottom Copyright Sub-Bar -->
    <div class="bg-slate-950 py-6 px-4 sm:px-6 lg:px-8 border-t border-slate-800/80 text-slate-400">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
            <span class="font-normal text-xs sm:text-sm">© {{ date('Y') }} Ashok Kumar Baans Store, Karnal. All rights reserved.</span>

            <div class="flex items-center gap-6 font-medium text-xs sm:text-sm text-slate-400">
                <a href="{{ route('home') }}" class="hover:text-emerald-400 transition">Home</a>
                <a href="{{ route('catalog.index') }}" class="hover:text-emerald-400 transition">Products Catalog</a>
                <a href="{{ route('about') }}" class="hover:text-emerald-400 transition">About</a>
                <a href="{{ route('contact') }}" class="hover:text-emerald-400 transition">Contact</a>
            </div>

            <span class="text-emerald-400 font-semibold text-xs sm:text-sm">Direct Yard Merchant • Karnal, Haryana</span>
        </div>
    </div>
</footer>