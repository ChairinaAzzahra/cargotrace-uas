<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-extrabold text-navy-950 tracking-tight">Create New Shipment Order</h1>
                <p class="text-sm text-slate-600 mt-1">Register container manifest, carrier allocation, and routing schedule</p>
            </div>
            <a href="{{ route('shipments.index') }}" class="text-sm font-semibold text-slate-600 hover:text-navy-950">
                &larr; Back to Shipments
            </a>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto bg-white rounded-2xl p-8 border border-beige-300/60 shadow-sm">
        <form method="POST" action="{{ route('shipments.store') }}">
            @csrf

            <!-- Section 1: Customer & Carrier -->
            <div class="border-b border-beige-200 pb-6 mb-6">
                <h3 class="text-sm font-bold text-navy-950 uppercase tracking-wider mb-4">1. Parties & Logistics Carrier</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block font-semibold uppercase text-slate-600 mb-1">Customer Client *</label>
                        <select name="customer_id" required class="w-full text-xs rounded-xl border-beige-300 focus:ring-navy-700">
                            <option value="">Select Customer</option>
                            @foreach ($customers as $c)
                                <option value="{{ $c->id }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }} ({{ $c->company_name ?? $c->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold uppercase text-slate-600 mb-1">Shipping Carrier *</label>
                        <select name="carrier_id" required class="w-full text-xs rounded-xl border-beige-300 focus:ring-navy-700">
                            <option value="">Select Carrier</option>
                            @foreach ($carriers as $car)
                                <option value="{{ $car->id }}" {{ old('carrier_id') == $car->id ? 'selected' : '' }}>
                                    {{ $car->name }} ({{ $car->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Section 2: Origin, Destination & Cargo Type -->
            <div class="border-b border-beige-200 pb-6 mb-6">
                <h3 class="text-sm font-bold text-navy-950 uppercase tracking-wider mb-4">2. Route & Cargo Specifications</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                    <div>
                        <label class="block font-semibold uppercase text-slate-600 mb-1">Origin Port/Hub *</label>
                        <select name="origin_id" required class="w-full text-xs rounded-xl border-beige-300 focus:ring-navy-700">
                            <option value="">Select Origin</option>
                            @foreach ($locations as $loc)
                                <option value="{{ $loc->id }}" {{ old('origin_id') == $loc->id ? 'selected' : '' }}>
                                    {{ $loc->city }} - {{ $loc->name }} ({{ $loc->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold uppercase text-slate-600 mb-1">Destination Port/Hub *</label>
                        <select name="destination_id" required class="w-full text-xs rounded-xl border-beige-300 focus:ring-navy-700">
                            <option value="">Select Destination</option>
                            @foreach ($locations as $loc)
                                <option value="{{ $loc->id }}" {{ old('destination_id') == $loc->id ? 'selected' : '' }}>
                                    {{ $loc->city }} - {{ $loc->name }} ({{ $loc->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold uppercase text-slate-600 mb-1">Cargo Type *</label>
                        <input type="text" name="cargo_type" value="{{ old('cargo_type', 'Consumer Electronics') }}" required class="w-full text-xs rounded-xl border-beige-300 focus:ring-navy-700" placeholder="e.g. Industrial Machinery, Perishables">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs mt-4">
                    <div>
                        <label class="block font-semibold uppercase text-slate-600 mb-1">Total Weight (kg) *</label>
                        <input type="number" step="0.01" name="total_weight_kg" value="{{ old('total_weight_kg', 14200) }}" required class="w-full text-xs rounded-xl border-beige-300 focus:ring-navy-700">
                    </div>
                    <div>
                        <label class="block font-semibold uppercase text-slate-600 mb-1">Total Volume (CBM) *</label>
                        <input type="number" step="0.01" name="total_volume_cbm" value="{{ old('total_volume_cbm', 38.5) }}" required class="w-full text-xs rounded-xl border-beige-300 focus:ring-navy-700">
                    </div>
                </div>
            </div>

            <!-- Section 3: Expected Schedule & ETA -->
            <div class="border-b border-beige-200 pb-6 mb-6">
                <h3 class="text-sm font-bold text-navy-950 uppercase tracking-wider mb-4">3. Flight/Vessel Schedule & ETA</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                    <div>
                        <label class="block font-semibold uppercase text-slate-600 mb-1">Expected Departure *</label>
                        <input type="datetime-local" name="expected_departure_at" value="{{ old('expected_departure_at', now()->format('Y-m-d\TH:i')) }}" required class="w-full text-xs rounded-xl border-beige-300 focus:ring-navy-700">
                    </div>

                    <div>
                        <label class="block font-semibold uppercase text-slate-600 mb-1">Expected Arrival *</label>
                        <input type="datetime-local" name="expected_arrival_at" value="{{ old('expected_arrival_at', now()->addDays(14)->format('Y-m-d\TH:i')) }}" required class="w-full text-xs rounded-xl border-beige-300 focus:ring-navy-700">
                    </div>

                    <div>
                        <label class="block font-semibold uppercase text-slate-600 mb-1">Estimated Arrival (ETA)</label>
                        <input type="datetime-local" name="estimated_arrival_at" value="{{ old('estimated_arrival_at') }}" class="w-full text-xs rounded-xl border-beige-300 focus:ring-navy-700" placeholder="Optional delay evaluation">
                        <span class="text-[10px] text-slate-400">If later than expected arrival, delay is flagged automatically.</span>
                    </div>
                </div>

                <div class="mt-4 text-xs">
                    <label class="block font-semibold uppercase text-slate-600 mb-1">Operational Notes</label>
                    <textarea name="notes" rows="2" class="w-full text-xs rounded-xl border-beige-300 focus:ring-navy-700" placeholder="Temperature control instructions, handling warnings...">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3">
                <a href="{{ route('shipments.index') }}" class="px-5 py-2.5 bg-beige-200 text-slate-700 rounded-xl text-xs font-semibold hover:bg-beige-300 transition">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-navy-900 text-beige-100 rounded-xl text-xs font-bold hover:bg-navy-800 shadow-md transition">Create Shipment Order</button>
            </div>
        </form>
    </div>
</x-app-layout>
