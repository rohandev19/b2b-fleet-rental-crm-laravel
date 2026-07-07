<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-semibold text-neutral-950">Pipeline Board</h1>
            <p class="mt-0.5 text-sm text-neutral-500">Track prospects across the B2B fleet rental sales stages</p>
        </div>
    </x-slot>

    <div class="space-y-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <a href="{{ route('prospects.index') }}" class="text-sm font-medium text-neutral-600 hover:text-neutral-950">Back to prospects</a>

            @if (auth()->user()->hasRole(\App\Enums\UserRole::Admin, \App\Enums\UserRole::Sales))
                <a href="{{ route('prospects.create') }}" class="rounded-lg bg-neutral-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-neutral-800">New prospect</a>
            @endif
        </div>

        <div class="overflow-x-auto pb-3">
            <div class="grid min-w-[1120px] grid-cols-7 gap-4">
                @foreach ($stages as $status => $prospects)
                    <section class="min-h-[560px] rounded-lg border border-neutral-200 bg-neutral-50">
                        <div class="sticky top-0 z-10 border-b border-neutral-200 bg-neutral-50 px-4 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <h2 class="text-sm font-semibold text-neutral-950">{{ str($status)->headline() }}</h2>
                                <span class="rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-neutral-600 ring-1 ring-neutral-200">{{ $prospects->count() }}</span>
                            </div>
                        </div>

                        <div class="space-y-3 p-3">
                            @forelse ($prospects as $prospect)
                                <article class="rounded-lg border border-neutral-200 bg-white p-4 shadow-sm">
                                    <div class="flex items-start justify-between gap-3">
                                        <a href="{{ route('prospects.show', $prospect) }}" class="text-sm font-semibold text-neutral-950 hover:text-neutral-700">
                                            {{ $prospect->company_name }}
                                        </a>
                                        <span @class([
                                            'rounded-full px-2 py-0.5 text-[11px] font-semibold',
                                            'bg-red-50 text-red-700' => $prospect->priority === 'high',
                                            'bg-amber-50 text-amber-700' => $prospect->priority === 'medium',
                                            'bg-neutral-100 text-neutral-600' => $prospect->priority === 'low',
                                        ])>
                                            {{ str($prospect->priority)->headline() }}
                                        </span>
                                    </div>

                                    <dl class="mt-3 space-y-2 text-xs text-neutral-600">
                                        <div class="flex justify-between gap-3">
                                            <dt>Sales</dt>
                                            <dd class="text-right font-medium text-neutral-800">{{ $prospect->assignedSales?->name ?? '-' }}</dd>
                                        </div>
                                        <div class="flex justify-between gap-3">
                                            <dt>Need</dt>
                                            <dd class="text-right font-medium text-neutral-800">{{ $prospect->estimated_vehicle_need ?? 0 }} vehicles</dd>
                                        </div>
                                        <div class="flex justify-between gap-3">
                                            <dt>Contacts</dt>
                                            <dd class="text-right font-medium text-neutral-800">{{ $prospect->contacts_count }}</dd>
                                        </div>
                                        <div class="flex justify-between gap-3">
                                            <dt>Quotations</dt>
                                            <dd class="text-right font-medium text-neutral-800">{{ $prospect->quotations_count }}</dd>
                                        </div>
                                    </dl>

                                    @if ($prospect->next_follow_up_at)
                                        <div class="mt-3 rounded-md bg-amber-50 px-3 py-2 text-xs font-medium text-amber-800">
                                            Next: {{ $prospect->next_follow_up_at->format('d M Y H:i') }}
                                        </div>
                                    @endif
                                </article>
                            @empty
                                <div class="rounded-lg border border-dashed border-neutral-300 bg-white px-4 py-8 text-center text-sm text-neutral-500">
                                    No prospects in this stage.
                                </div>
                            @endforelse
                        </div>
                    </section>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
