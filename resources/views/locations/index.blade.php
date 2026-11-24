<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-extrabold text-navy-950 tracking-tight">Ports & Strategic Hubs</h1>
                <p class="text-sm text-slate-600 mt-1">Geocoded origin, transit, and destination terminals</p>
            </div>
            @if (Auth::user()->isAdmin())
                <button onclick="document.getElementById('modal-location').classList.remove('hidden')" class="px-4 py-2 bg-navy-900 text-beige-100 rounded-xl text-xs font-bold hover:bg-navy-800 shadow-sm transition">
                    + Add Port/Hub
                </button>
            @endif
        </div>
    </x-slot>

    <div class="bg-white rounded-2xl border border-beige-300/60 shadow-sm overflow-hidden mb-6">
        <table class="w-full text-left text-sm text-slate-700">
            <thead class="bg-beige-100/70 text-navy-950 text-xs uppercase font-bold tracking-wider border-b border-beige-200">
                <tr>
                    <th class="px-6 py-4">UN/LOCODE</th>
                    <th class="px-6 py-4">Hub Name</th>
                    <th class="px-6 py-4">City & Country</th>
                    <th class="px-6 py-4">Hub Type</th>
                    <th class="px-6 py-4">Coordinates</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-beige-100">
                @foreach ($locations as $loc)
                    <tr class="hover:bg-beige-50/50">
                        <td class="px-6 py-4 font-mono font-bold text-navy-900">{{ $loc->code }}</td>
                        <td class="px-6 py-4 font-semibold text-slate-900">{{ $loc->name }}</td>
                        <td class="px-6 py-4 text-xs text-slate-600">{{ $loc->city }}, {{ $loc->country }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-navy-50 text-navy-800 border border-navy-200">
                                {{ $loc->type }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-xs font-mono text-slate-500">
                            {{ number_format($loc->latitude, 4) }}, {{ number_format($loc->longitude, 4) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-4 border-t border-beige-200">
            {{ $locations->links() }}
        </div>
    </div>

    <!-- Modal Add Location -->
    @if (Auth::user()->isAdmin())
        <div id="modal-location" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-beige-300">
                <h3 class="text-base font-bold text-navy-950 mb-2">Register Location Hub</h3>
                <form method="POST" action="{{ route('locations.store') }}">
                    @csrf
                    <div class="space-y-4 text-xs">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold uppercase text-slate-600 mb-1">Code *</label>
                                <input type="text" name="code" required class="w-full text-xs rounded-xl border-beige-300 font-mono" placeholder="IDJKT">
                            </div>
                            <div>
                                <label class="block font-semibold uppercase text-slate-600 mb-1">Type *</label>
                                <select name="type" required class="w-full text-xs rounded-xl border-beige-300">
                                    <option value="PORT">PORT</option>
                                    <option value="AIRPORT">AIRPORT</option>
                                    <option value="WAREHOUSE">WAREHOUSE</option>
                                    <option value="CUSTOMS_CHECKPOINT">CUSTOMS_CHECKPOINT</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block font-semibold uppercase text-slate-600 mb-1">Facility Name *</label>
                            <input type="text" name="name" required class="w-full text-xs rounded-xl border-beige-300" placeholder="e.g. Port of Tanjung Priok">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold uppercase text-slate-600 mb-1">City *</label>
                                <input type="text" name="city" required class="w-full text-xs rounded-xl border-beige-300">
                            </div>
                            <div>
                                <label class="block font-semibold uppercase text-slate-600 mb-1">Country *</label>
                                <input type="text" name="country" required class="w-full text-xs rounded-xl border-beige-300">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold uppercase text-slate-600 mb-1">Latitude *</label>
                                <input type="number" step="0.000001" name="latitude" required class="w-full text-xs rounded-xl border-beige-300 font-mono" placeholder="-6.1012">
                            </div>
                            <div>
                                <label class="block font-semibold uppercase text-slate-600 mb-1">Longitude *</label>
                                <input type="number" step="0.000001" name="longitude" required class="w-full text-xs rounded-xl border-beige-300 font-mono" placeholder="106.8841">
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-2">
                        <button type="button" onclick="document.getElementById('modal-location').classList.add('hidden')" class="px-4 py-2 bg-beige-200 text-slate-700 rounded-xl text-xs font-semibold">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-navy-900 text-beige-100 rounded-xl text-xs font-semibold">Save Hub</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</x-app-layout>
