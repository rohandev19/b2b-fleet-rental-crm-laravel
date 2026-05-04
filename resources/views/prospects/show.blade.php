<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-semibold text-neutral-950">{{ $prospect->company_name }}</h1>
            <p class="mt-0.5 text-sm text-neutral-500">{{ $prospect->industry ?: 'No industry' }}{{ $prospect->city ? ' · '.$prospect->city : '' }}</p>
        </div>
    </x-slot>

    @php
        $canManage = auth()->user()->hasRole(\App\Enums\UserRole::Admin, \App\Enums\UserRole::Sales);
        $statusClasses = [
            'new' => 'bg-sky-50 text-sky-700',
            'contacted' => 'bg-indigo-50 text-indigo-700',
            'meeting' => 'bg-violet-50 text-violet-700',
            'quotation' => 'bg-amber-50 text-amber-700',
            'negotiation' => 'bg-orange-50 text-orange-700',
            'won' => 'bg-emerald-50 text-emerald-700',
            'lost' => 'bg-red-50 text-red-700',
        ];
    @endphp

    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <a href="{{ route('prospects.index') }}" class="text-sm font-medium text-neutral-600 hover:text-neutral-950">Back to prospects</a>

            @if ($canManage)
                <div class="flex gap-2">
                    <a href="{{ route('prospects.edit', $prospect) }}" class="rounded-lg border border-neutral-200 bg-white px-4 py-2 text-sm font-medium text-neutral-700 transition hover:bg-neutral-50">Edit</a>
                    <form method="POST" action="{{ route('prospects.destroy', $prospect) }}" onsubmit="return confirm('Delete this prospect?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-700 transition hover:bg-red-50">Delete</button>
                    </form>
                </div>
            @endif
        </div>

        <section class="grid gap-4 md:grid-cols-4">
            <div class="rounded-lg border border-neutral-200 bg-white p-5 shadow-sm">
                <div class="text-sm font-medium text-neutral-500">Status</div>
                <div class="mt-3">
                    <span @class(['rounded-full px-2.5 py-1 text-xs font-semibold', $statusClasses[$prospect->status] ?? 'bg-neutral-100 text-neutral-700'])>
                        {{ str($prospect->status)->headline() }}
                    </span>
                </div>
            </div>
            <div class="rounded-lg border border-neutral-200 bg-white p-5 shadow-sm">
                <div class="text-sm font-medium text-neutral-500">Priority</div>
                <div class="mt-3 text-xl font-semibold text-neutral-950">{{ str($prospect->priority)->headline() }}</div>
            </div>
            <div class="rounded-lg border border-neutral-200 bg-white p-5 shadow-sm">
                <div class="text-sm font-medium text-neutral-500">Vehicle Need</div>
                <div class="mt-3 text-xl font-semibold text-neutral-950">{{ $prospect->estimated_vehicle_need ?? 0 }}</div>
            </div>
            <div class="rounded-lg border border-neutral-200 bg-white p-5 shadow-sm">
                <div class="text-sm font-medium text-neutral-500">Sales Owner</div>
                <div class="mt-3 text-base font-semibold text-neutral-950">{{ $prospect->assignedSales?->name ?? 'Unassigned' }}</div>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-[1fr_420px]">
            <section class="rounded-lg border border-neutral-200 bg-white shadow-sm">
                <div class="border-b border-neutral-200 px-5 py-4">
                    <h2 class="text-base font-semibold text-neutral-950">Company Information</h2>
                </div>
                <dl class="grid gap-5 p-5 md:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-neutral-500">Company Size</dt>
                        <dd class="mt-1 text-sm text-neutral-950">{{ str($prospect->company_size)->headline() }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-neutral-500">Source</dt>
                        <dd class="mt-1 text-sm text-neutral-950">{{ str($prospect->source)->replace('_', ' ')->headline() }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-neutral-500">Location</dt>
                        <dd class="mt-1 text-sm text-neutral-950">{{ collect([$prospect->city, $prospect->province])->filter()->join(', ') ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-neutral-500">Website</dt>
                        <dd class="mt-1 text-sm text-neutral-950">
                            @if ($prospect->website)
                                <a href="{{ $prospect->website }}" target="_blank" class="text-sky-700 hover:underline">{{ $prospect->website }}</a>
                            @else
                                -
                            @endif
                        </dd>
                    </div>
                    <div class="md:col-span-2">
                        <dt class="text-sm font-medium text-neutral-500">Address</dt>
                        <dd class="mt-1 whitespace-pre-line text-sm text-neutral-950">{{ $prospect->address ?: '-' }}</dd>
                    </div>
                    <div class="md:col-span-2">
                        <dt class="text-sm font-medium text-neutral-500">Notes</dt>
                        <dd class="mt-1 whitespace-pre-line text-sm text-neutral-950">{{ $prospect->notes ?: '-' }}</dd>
                    </div>
                    @if ($prospect->next_follow_up_at)
                        <div>
                            <dt class="text-sm font-medium text-neutral-500">Next Follow-up</dt>
                            <dd class="mt-1 text-sm text-neutral-950">{{ $prospect->next_follow_up_at->format('d M Y H:i') }}</dd>
                        </div>
                    @endif
                    @if ($prospect->lost_reason)
                        <div class="md:col-span-2">
                            <dt class="text-sm font-medium text-neutral-500">Lost Reason</dt>
                            <dd class="mt-1 whitespace-pre-line text-sm text-neutral-950">{{ $prospect->lost_reason }}</dd>
                        </div>
                    @endif
                </dl>
            </section>

            <section class="rounded-lg border border-neutral-200 bg-white shadow-sm">
                <div class="border-b border-neutral-200 px-5 py-4">
                    <h2 class="text-base font-semibold text-neutral-950">Add PIC</h2>
                </div>

                @if ($canManage)
                    <form method="POST" action="{{ route('prospects.contacts.store', $prospect) }}" class="space-y-4 p-5">
                        @csrf
                        <div>
                            <x-input-label for="name" value="Name" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>
                        <div>
                            <x-input-label for="position" value="Position" />
                            <x-text-input id="position" name="position" type="text" class="mt-1 block w-full" :value="old('position')" placeholder="Procurement, GA, Director" />
                            <x-input-error class="mt-2" :messages="$errors->get('position')" />
                        </div>
                        <div>
                            <x-input-label for="email" value="Email" />
                            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" />
                            <x-input-error class="mt-2" :messages="$errors->get('email')" />
                        </div>
                        <div>
                            <x-input-label for="phone" value="Phone" />
                            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone')" />
                            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                        </div>
                        <label class="flex items-start gap-3">
                            <input type="hidden" name="is_primary" value="0">
                            <input type="checkbox" name="is_primary" value="1" class="mt-1 rounded border-neutral-300 text-neutral-950 shadow-sm focus:ring-neutral-800" @checked(old('is_primary'))>
                            <span class="text-sm font-medium text-neutral-800">Set as primary PIC</span>
                        </label>
                        <button type="submit" class="w-full rounded-lg bg-neutral-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-neutral-800">Add contact</button>
                    </form>
                @else
                    <div class="p-5 text-sm text-neutral-500">Contact management is available for Admin and Sales.</div>
                @endif
            </section>
        </div>

        <section class="overflow-hidden rounded-lg border border-neutral-200 bg-white shadow-sm">
            <div class="border-b border-neutral-200 px-5 py-4">
                <h2 class="text-base font-semibold text-neutral-950">Contact Persons</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200 text-left text-sm">
                    <thead class="bg-neutral-50 text-xs uppercase tracking-normal text-neutral-500">
                        <tr>
                            <th class="px-5 py-3 font-semibold">PIC</th>
                            <th class="px-5 py-3 font-semibold">Email</th>
                            <th class="px-5 py-3 font-semibold">Phone</th>
                            <th class="px-5 py-3 font-semibold">Primary</th>
                            @if ($canManage)
                                <th class="px-5 py-3 text-right font-semibold">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100">
                        @forelse ($prospect->contacts as $contact)
                            <tr>
                                <td class="px-5 py-4">
                                    <div class="font-medium text-neutral-950">{{ $contact->name }}</div>
                                    <div class="mt-0.5 text-neutral-500">{{ $contact->position ?: '-' }}</div>
                                </td>
                                <td class="px-5 py-4 text-neutral-700">{{ $contact->email ?: '-' }}</td>
                                <td class="px-5 py-4 text-neutral-700">{{ $contact->phone ?: '-' }}</td>
                                <td class="px-5 py-4">
                                    @if ($contact->is_primary)
                                        <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">Primary</span>
                                    @else
                                        <span class="rounded-full bg-neutral-100 px-2.5 py-1 text-xs font-semibold text-neutral-600">Secondary</span>
                                    @endif
                                </td>
                                @if ($canManage)
                                    <td class="px-5 py-4">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('prospects.contacts.edit', [$prospect, $contact]) }}" class="rounded-lg border border-neutral-200 px-3 py-1.5 text-sm font-medium text-neutral-700 transition hover:bg-white">Edit</a>
                                            <form method="POST" action="{{ route('prospects.contacts.destroy', [$prospect, $contact]) }}" onsubmit="return confirm('Delete this contact?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-lg border border-red-200 px-3 py-1.5 text-sm font-medium text-red-700 transition hover:bg-red-50">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $canManage ? 5 : 4 }}" class="px-5 py-10 text-center text-sm text-neutral-500">No PIC contacts recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
