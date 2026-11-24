<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-extrabold text-navy-950 tracking-tight">Security & System Audit Trail</h1>
                <p class="text-sm text-slate-600 mt-1">Immutable ledger of state transitions, entity updates, and operator actions</p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-navy-900 text-beige-200">
                Administrator Access Only
            </span>
        </div>
    </x-slot>

    <div class="bg-white rounded-2xl border border-beige-300/60 shadow-sm overflow-hidden mb-6">
        <table class="w-full text-left text-sm text-slate-700">
            <thead class="bg-beige-100/70 text-navy-950 text-xs uppercase font-bold tracking-wider border-b border-beige-200">
                <tr>
                    <th class="px-6 py-4">Timestamp</th>
                    <th class="px-6 py-4">Actor</th>
                    <th class="px-6 py-4">Action</th>
                    <th class="px-6 py-4">Target Entity</th>
                    <th class="px-6 py-4">IP / Client</th>
                    <th class="px-6 py-4">State Diff</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-beige-100">
                @forelse ($logs as $log)
                    <tr class="hover:bg-beige-50/50">
                        <td class="px-6 py-4 font-mono text-xs text-slate-500 whitespace-nowrap">
                            {{ $log->created_at->format('d M Y, H:i:s') }}
                        </td>
                        <td class="px-6 py-4 font-semibold text-slate-800">
                            {{ $log->user->name ?? 'System/API' }}
                            <span class="text-[10px] text-slate-400 block font-mono">({{ $log->user->role ?? 'automation' }})</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-mono font-bold text-xs px-2 py-0.5 rounded bg-navy-50 text-navy-800 border border-navy-200">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-xs font-mono text-slate-600">
                            {{ $log->entity_type }} #{{ $log->entity_id }}
                        </td>
                        <td class="px-6 py-4 text-xs font-mono text-slate-500">
                            {{ $log->ip_address }}
                        </td>
                        <td class="px-6 py-4 text-xs">
                            <details class="cursor-pointer">
                                <summary class="text-navy-700 hover:underline font-mono">inspect changes</summary>
                                <pre class="mt-2 p-2 bg-slate-900 text-slate-200 rounded text-[10px] max-h-36 overflow-auto font-mono">{{ json_encode(['before' => $log->old_values, 'after' => $log->new_values], JSON_PRETTY_PRINT) }}</pre>
                            </details>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-slate-400">
                            No audit log events recorded yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-beige-200">
            {{ $logs->links() }}
        </div>
    </div>
</x-app-layout>
