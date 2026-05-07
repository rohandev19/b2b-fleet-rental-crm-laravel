<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-semibold text-neutral-950">{{ $title }}</h1>
            <p class="mt-0.5 text-sm text-neutral-500">{{ $description }}</p>
        </div>
    </x-slot>

    <div class="space-y-5">
        <div class="flex gap-2">
            <a href="{{ route('follow-ups.today') }}" @class([
                'rounded-lg px-4 py-2 text-sm font-medium transition',
                'bg-neutral-950 text-white' => request()->routeIs('follow-ups.today'),
                'border border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-50' => ! request()->routeIs('follow-ups.today'),
            ])>Today</a>
            <a href="{{ route('follow-ups.overdue') }}" @class([
                'rounded-lg px-4 py-2 text-sm font-medium transition',
                'bg-neutral-950 text-white' => request()->routeIs('follow-ups.overdue'),
                'border border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-50' => ! request()->routeIs('follow-ups.overdue'),
            ])>Overdue</a>
        </div>

        <section class="overflow-hidden rounded-lg border border-neutral-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200 text-left text-sm">
                    <thead class="bg-neutral-50 text-xs uppercase tracking-normal text-neutral-500">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Reminder</th>
                            <th class="px-5 py-3 font-semibold">Prospect</th>
                            <th class="px-5 py-3 font-semibold">PIC</th>
                            <th class="px-5 py-3 font-semibold">Owner</th>
                            <th class="px-5 py-3 text-right font-semibold">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100">
                        @forelse ($activities as $activity)
                            <tr class="hover:bg-neutral-50">
                                <td class="px-5 py-4">
                                    <div class="font-medium text-neutral-950">{{ $activity->summary }}</div>
                                    <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-neutral-500">
                                        <span>{{ str($activity->activity_type)->replace('_', ' ')->headline() }}</span>
                                        <span>·</span>
                                        <span>{{ $activity->next_follow_up_at->format('d M Y H:i') }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-medium text-neutral-950">{{ $activity->prospect->company_name }}</div>
                                    <div class="mt-0.5 text-neutral-500">{{ $activity->prospect->assignedSales?->name ?? 'Unassigned' }}</div>
                                </td>
                                <td class="px-5 py-4 text-neutral-700">{{ $activity->contact?->name ?? '-' }}</td>
                                <td class="px-5 py-4 text-neutral-700">{{ $activity->user?->name ?? 'Unknown' }}</td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('prospects.show', $activity->prospect) }}" class="rounded-lg border border-neutral-200 px-3 py-1.5 text-sm font-medium text-neutral-700 transition hover:bg-white">Open</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-12 text-center">
                                    <div class="font-medium text-neutral-950">{{ $emptyMessage }}</div>
                                    <div class="mt-1 text-sm text-neutral-500">Reminder items will appear here when follow-up dates are set.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($activities->hasPages())
                <div class="border-t border-neutral-200 px-5 py-4">
                    {{ $activities->links() }}
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
