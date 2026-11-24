<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-extrabold text-navy-950 tracking-tight">Edit Shipment: {{ $shipment->shipment_number }}</h1>
                <p class="text-sm text-slate-600 mt-1">Update schedule, carrier allocations, or manifest specs</p>
            </div>
            <a href="{{ route('shipments.show', $shipment) }}" class="text-sm font-semibold text-slate-600 hover:text-navy-950">
                &larr; Back to Visibility Portal
            </a>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto bg-white rounded-2xl p-8 border border-beige-300/60 shadow-sm">
        <form method="POST" action="{{ route('shipments.update', $shipment) }}">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs mb-6">
                <div>
                    <label class="block font-semibold uppercase text-slate-600 mb-1">Carrier</label>
                    <select name="carrier_id" required class="w-full text-xs rounded-xl border-beige-300">
                        @foreach ($carriers as $c)
                            <option value="{{ $c->id }}" {{ $shipment->carrier_id == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold uppercase text-slate-600 mb-1">Cargo Type</label>
                    <input type="text" name="cargo_type" value="{{ old('cargo_type', $shipment->cargo_type) }}" required class="w-full text-xs rounded-xl border-beige-300">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs mb-6">
                <div>
                    <label class="block font-semibold uppercase text-slate-600 mb-1">Total Weight (kg)</label>
                    <input type="number" step="0.01" name="total_weight_kg" value="{{ old('total_weight_kg', $shipment->total_weight_kg) }}" required class="w-full text-xs rounded-xl border-beige-300">
                </div>
                <div>
                    <label class="block font-semibold uppercase text-slate-600 mb-1">Total Volume (CBM)</label>
                    <input type="number" step="0.01" name="total_volume_cbm" value="{{ old('total_volume_cbm', $shipment->total_volume_cbm) }}" required class="w-full text-xs rounded-xl border-beige-300">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs mb-6">
                <div>
                    <label class="block font-semibold uppercase text-slate-600 mb-1">Expected Departure</label>
                    <input type="datetime-local" name="expected_departure_at" value="{{ old('expected_departure_at', $shipment->expected_departure_at->format('Y-m-d\TH:i')) }}" required class="w-full text-xs rounded-xl border-beige-300">
                </div>
                <div>
                    <label class="block font-semibold uppercase text-slate-600 mb-1">Expected Arrival</label>
                    <input type="datetime-local" name="expected_arrival_at" value="{{ old('expected_arrival_at', $shipment->expected_arrival_at->format('Y-m-d\TH:i')) }}" required class="w-full text-xs rounded-xl border-beige-300">
                </div>
                <div>
                    <label class="block font-semibold uppercase text-slate-600 mb-1">Estimated Arrival (ETA)</label>
                    <input type="datetime-local" name="estimated_arrival_at" value="{{ old('estimated_arrival_at', $shipment->estimated_arrival_at?->format('Y-m-d\TH:i')) }}" class="w-full text-xs rounded-xl border-beige-300">
                </div>
            </div>

            <div class="text-xs mb-6">
                <label class="block font-semibold uppercase text-slate-600 mb-1">Notes</label>
                <textarea name="notes" rows="2" class="w-full text-xs rounded-xl border-beige-300">{{ old('notes', $shipment->notes) }}</textarea>
            </div>

            <div class="flex items-center justify-end space-x-3">
                <a href="{{ route('shipments.show', $shipment) }}" class="px-5 py-2.5 bg-beige-200 text-slate-700 rounded-xl text-xs font-semibold">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-navy-900 text-beige-100 rounded-xl text-xs font-bold hover:bg-navy-800">Save Changes</button>
            </div>
        </form>
    </div>
</x-app-layout>
