<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-extrabold text-navy-950 tracking-tight">Logistics Carriers Directory</h1>
                <p class="text-sm text-slate-600 mt-1">Manage maritime, air, and freight carrier partners</p>
            </div>
            @if (Auth::user()->isAdmin())
                <button onclick="document.getElementById('modal-carrier').classList.remove('hidden')" class="px-4 py-2 bg-navy-900 text-beige-100 rounded-xl text-xs font-bold hover:bg-navy-800 shadow-sm transition">
                    + Add Carrier
                </button>
            @endif
        </div>
    </x-slot>

    <div class="bg-white rounded-2xl border border-beige-300/60 shadow-sm overflow-hidden mb-6">
        <table class="w-full text-left text-sm text-slate-700">
            <thead class="bg-beige-100/70 text-navy-950 text-xs uppercase font-bold tracking-wider border-b border-beige-200">
                <tr>
                    <th class="px-6 py-4">Carrier Code</th>
                    <th class="px-6 py-4">Name</th>
                    <th class="px-6 py-4">Contact</th>
                    <th class="px-6 py-4">Active Shipments</th>
                    <th class="px-6 py-4">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-beige-100">
                @foreach ($carriers as $carrier)
                    <tr class="hover:bg-beige-50/50">
                        <td class="px-6 py-4 font-mono font-bold text-navy-900">{{ $carrier->code }}</td>
                        <td class="px-6 py-4 font-semibold text-slate-900">{{ $carrier->name }}</td>
                        <td class="px-6 py-4 text-xs text-slate-500">
                            <div>{{ $carrier->contact_email ?? 'No email' }}</div>
                            <div class="font-mono">{{ $carrier->contact_phone }}</div>
                        </td>
                        <td class="px-6 py-4 font-mono font-semibold text-navy-900">{{ $carrier->shipments_count }}</td>
                        <td class="px-6 py-4">
                            @if ($carrier->is_active)
                                <span class="px-2 py-0.5 rounded text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Active</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 text-slate-500">Inactive</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-4 border-t border-beige-200">
            {{ $carriers->links() }}
        </div>
    </div>

    <!-- Modal Add Carrier -->
    @if (Auth::user()->isAdmin())
        <div id="modal-carrier" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-beige-300">
                <h3 class="text-base font-bold text-navy-950 mb-2">Register New Carrier</h3>
                <form method="POST" action="{{ route('carriers.store') }}">
                    @csrf
                    <div class="space-y-4 text-xs">
                        <div>
                            <label class="block font-semibold uppercase text-slate-600 mb-1">Carrier Code *</label>
                            <input type="text" name="code" required class="w-full text-xs rounded-xl border-beige-300 font-mono" placeholder="e.g. MAEU, MSC, ONE">
                        </div>
                        <div>
                            <label class="block font-semibold uppercase text-slate-600 mb-1">Company Name *</label>
                            <input type="text" name="name" required class="w-full text-xs rounded-xl border-beige-300" placeholder="e.g. Maersk Line">
                        </div>
                        <div>
                            <label class="block font-semibold uppercase text-slate-600 mb-1">Contact Email</label>
                            <input type="email" name="contact_email" class="w-full text-xs rounded-xl border-beige-300">
                        </div>
                        <div>
                            <label class="block font-semibold uppercase text-slate-600 mb-1">Contact Phone</label>
                            <input type="text" name="contact_phone" class="w-full text-xs rounded-xl border-beige-300">
                        </div>
                        <div class="flex items-center space-x-2">
                            <input type="checkbox" name="is_active" value="1" checked class="rounded border-beige-300 text-navy-900 focus:ring-navy-700">
                            <span class="text-xs text-slate-700 font-medium">Active for new bookings</span>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-2">
                        <button type="button" onclick="document.getElementById('modal-carrier').classList.add('hidden')" class="px-4 py-2 bg-beige-200 text-slate-700 rounded-xl text-xs font-semibold">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-navy-900 text-beige-100 rounded-xl text-xs font-semibold">Save Carrier</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</x-app-layout>
