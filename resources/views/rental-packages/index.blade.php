<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-semibold text-neutral-950">Rental Packages</h1>
            <p class="mt-0.5 text-sm text-neutral-500">Manage standard rental durations and included services.</p>
        </div>
    </x-slot>

    <div class="space-y-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <form method="GET" action="{{ route('rental-packages.index') }}" class="grid gap-3 sm:grid-cols-[minmax(220px,1fr)_150px_auto]">
                <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search package" class="rounded-lg border-neutral-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <select name="status" class="rounded-lg border-neutral-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All status</option>
                    <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                    <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
                </select>
                <button type="submit" class="rounded-lg border border-neutral-200 bg-white px-4 py-2 text-sm font-medium text-neutral-700 transition hover:bg-neutral-50">Filter</button>
            </form>

            @if (auth()->user()->hasRole(\App\Enums\UserRole::Admin))
                <a href="{{ route('rental-packages.create') }}" class="inline-flex items-center justify-center rounded-lg bg-neutral-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-neutral-800">
                    Add package
                </a>
            @endif
        </div>

        <section class="overflow-hidden rounded-lg border border-neutral-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200 text-left text-sm">
                    <thead class="bg-neutral-50 text-xs uppercase tracking-normal text-neutral-500">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Package</th>
                            <th class="px-5 py-3 font-semibold">Duration</th>
                            <th class="px-5 py-3 font-semibold">Includes</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                            @if (auth()->user()->hasRole(\App\Enums\UserRole::Admin))
                                <th class="px-5 py-3 text-right font-semibold">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100">
                        @forelse ($rentalPackages as $rentalPackage)
                            <tr class="hover:bg-neutral-50">
                                <td class="px-5 py-4">
                                    <div class="font-medium text-neutral-950">{{ $rentalPackage->name }}</div>
                                    <div class="mt-0.5 max-w-xl truncate text-neutral-500">{{ $rentalPackage->description ?: 'No description' }}</div>
                                </td>
                                <td class="px-5 py-4 text-neutral-700">{{ $rentalPackage->duration_months }} months</td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ([
                                            'includes_driver' => 'Driver',
                                            'includes_maintenance' => 'Maintenance',
                                            'includes_insurance' => 'Insurance',
                                        ] as $field => $label)
                                            @if ($rentalPackage->{$field})
                                                <span class="rounded-full bg-sky-50 px-2.5 py-1 text-xs font-semibold text-sky-700">{{ $label }}</span>
                                            @endif
                                        @endforeach
                                        @unless ($rentalPackage->includes_driver || $rentalPackage->includes_maintenance || $rentalPackage->includes_insurance)
                                            <span class="text-neutral-500">Rental only</span>
                                        @endunless
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    @if ($rentalPackage->is_active)
                                        <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">Active</span>
                                    @else
                                        <span class="rounded-full bg-neutral-100 px-2.5 py-1 text-xs font-semibold text-neutral-600">Inactive</span>
                                    @endif
                                </td>
                                @if (auth()->user()->hasRole(\App\Enums\UserRole::Admin))
                                    <td class="px-5 py-4">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('rental-packages.edit', $rentalPackage) }}" class="rounded-lg border border-neutral-200 px-3 py-1.5 text-sm font-medium text-neutral-700 transition hover:bg-white">Edit</a>
                                            <form method="POST" action="{{ route('rental-packages.toggle-active', $rentalPackage) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="rounded-lg border border-neutral-200 px-3 py-1.5 text-sm font-medium text-neutral-700 transition hover:bg-white">{{ $rentalPackage->is_active ? 'Archive' : 'Activate' }}</button>
                                            </form>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->user()->hasRole(\App\Enums\UserRole::Admin) ? 5 : 4 }}" class="px-5 py-12 text-center">
                                    <div class="font-medium text-neutral-950">No rental packages found.</div>
                                    <div class="mt-1 text-sm text-neutral-500">Add packages before building quotations.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($rentalPackages->hasPages())
                <div class="border-t border-neutral-200 px-5 py-4">
                    {{ $rentalPackages->links() }}
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
