<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-semibold text-neutral-950">Audit Logs</h1>
            <p class="mt-0.5 text-sm text-neutral-500">Trace sensitive CRM actions and data changes</p>
        </div>
    </x-slot>

    <div class="space-y-6">
        <section class="rounded-lg border border-neutral-200 bg-white p-5 shadow-sm">
            <form method="GET" action="{{ route('audit-logs.index') }}" class="grid gap-4 lg:grid-cols-[1fr_220px_260px_auto] lg:items-end">
                <div>
                    <x-input-label for="search" value="Search" />
                    <x-text-input id="search" name="search" type="search" class="mt-1 block w-full" placeholder="Actor, action, summary..." :value="$filters['search'] ?? ''" />
                </div>
                <div>
                    <x-input-label for="action" value="Action" />
                    <select id="action" name="action" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All actions</option>
                        @foreach ($actions as $action)
                            <option value="{{ $action }}" @selected(($filters['action'] ?? '') === $action)>{{ str(str_replace('.', ' ', $action))->headline() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="auditable_type" value="Record Type" />
                    <select id="auditable_type" name="auditable_type" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All records</option>
                        @foreach ($auditableTypes as $type)
                            <option value="{{ $type }}" @selected(($filters['auditable_type'] ?? '') === $type)>{{ class_basename($type) }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="rounded-lg bg-neutral-950 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-neutral-800">Filter</button>
            </form>
        </section>

        <section class="overflow-hidden rounded-lg border border-neutral-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200 text-left text-sm">
                    <thead class="bg-neutral-50 text-xs uppercase tracking-normal text-neutral-500">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Time</th>
                            <th class="px-5 py-3 font-semibold">Actor</th>
                            <th class="px-5 py-3 font-semibold">Action</th>
                            <th class="px-5 py-3 font-semibold">Summary</th>
                            <th class="px-5 py-3 font-semibold">Record</th>
                            <th class="px-5 py-3 font-semibold">IP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100">
                        @forelse ($logs as $log)
                            <tr>
                                <td class="whitespace-nowrap px-5 py-4 text-neutral-700">{{ $log->created_at->format('d M Y H:i') }}</td>
                                <td class="px-5 py-4">
                                    <div class="font-medium text-neutral-950">{{ $log->user?->name ?? 'System' }}</div>
                                    <div class="mt-0.5 text-xs text-neutral-500">{{ $log->user?->email ?? '-' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full bg-neutral-100 px-2.5 py-1 text-xs font-semibold text-neutral-700">{{ str(str_replace('.', ' ', $log->action))->headline() }}</span>
                                </td>
                                <td class="min-w-72 px-5 py-4 text-neutral-800">
                                    <div>{{ $log->summary }}</div>
                                    @if ($log->new_values)
                                        <details class="mt-2">
                                            <summary class="cursor-pointer text-xs font-semibold text-neutral-500">Details</summary>
                                            <pre class="mt-2 max-w-xl overflow-x-auto rounded-lg bg-neutral-950 p-3 text-xs text-white">{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</pre>
                                        </details>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-neutral-700">
                                    {{ $log->auditable_type ? class_basename($log->auditable_type) : '-' }}
                                    @if ($log->auditable_id)
                                        <span class="text-neutral-400">#{{ $log->auditable_id }}</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-neutral-700">{{ $log->ip_address ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-sm text-neutral-500">No audit logs yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-neutral-200 px-5 py-4">
                {{ $logs->links() }}
            </div>
        </section>
    </div>
</x-app-layout>
