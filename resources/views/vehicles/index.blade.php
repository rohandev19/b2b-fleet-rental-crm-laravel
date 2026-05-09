<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-semibold text-neutral-950">Vehicles</h1>
            <p class="mt-0.5 text-sm text-neutral-500">Manage vehicle types and base monthly rental pricing.</p>
        </div>
    </x-slot>

    <div class="space-y-5">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <form method="GET" action="{{ route('vehicles.index') }}" class="grid gap-3 sm:grid-cols-[minmax(220px,1fr)_160px_150px_auto]">
                <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search brand or model" class="rounded-lg border-neutral-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">

                <select name="vehicle_type" class="rounded-lg border-neutral-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All types</option>
                    @foreach (\App\Models\Vehicle::TYPES as $type)
                        <option value="{{ $type }}" @selected(($filters['vehicle_type'] ?? '') === $type)>{{ str($type)->headline() }}</option>
                    @endforeach
                </select>

                <select name="status" class="rounded-lg border-neutral-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All status</option>
                    <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                    <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
                </select>

                <button type="submit" class="rounded-lg border border-neutral-200 bg-white px-4 py-2 text-sm font-medium text-neutral-700 transition hover:bg-neutral-50">Filter</button>
            </form>

            @if (auth()->user()->hasRole(\App\Enums\UserRole::Admin))
                <a href="{{ route('vehicles.create') }}" class="inline-flex items-center justify-center rounded-lg bg-neutral-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-neutral-800">
                    Add vehicle
                </a>
            @endif
        </div>

        <section class="overflow-hidden rounded-lg border border-neutral-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200 text-left text-sm">
                    <thead class="bg-neutral-50 text-xs uppercase tracking-normal text-neutral-500">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Vehicle</th>
                            <th class="px-5 py-3 font-semibold">Type</th>
                            <th class="px-5 py-3 font-semibold">Specs</th>
                            <th class="px-5 py-3 font-semibold">Base Price</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                            @if (auth()->user()->hasRole(\App\Enums\UserRole::Admin))
                                <th class="px-5 py-3 text-right font-semibold">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100">
                        @forelse ($vehicles as $vehicle)
                            <tr class="hover:bg-neutral-50">
                                <td class="px-5 py-4">
                                    <div class="font-medium text-neutral-950">{{ $vehicle->brand }} {{ $vehicle->model }}</div>
                                    <div class="mt-0.5 text-neutral-500">{{ $vehicle->seat_capacity }} seats</div>
                                </td>
                                <td class="px-5 py-4 text-neutral-700">{{ str($vehicle->vehicle_type)->headline() }}</td>
                                <td class="px-5 py-4 text-neutral-700">{{ str($vehicle->transmission)->headline() }} · {{ str($vehicle->fuel_type)->headline() }}</td>
                                <td class="px-5 py-4 font-medium text-neutral-950">Rp{{ number_format((float) $vehicle->base_monthly_price, 0, ',', '.') }}</td>
                                <td class="px-5 py-4">
                                    @if ($vehicle->is_active)
                                        <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">Active</span>
                                    @else
                                        <span class="rounded-full bg-neutral-100 px-2.5 py-1 text-xs font-semibold text-neutral-600">Inactive</span>
                                    @endif
                                </td>
                                @if (auth()->user()->hasRole(\App\Enums\UserRole::Admin))
                                    <td class="px-5 py-4">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('vehicles.edit', $vehicle) }}" class="rounded-lg border border-neutral-200 px-3 py-1.5 text-sm font-medium text-neutral-700 transition hover:bg-white">Edit</a>
                                            <form method="POST" action="{{ route('vehicles.toggle-active', $vehicle) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="rounded-lg border border-neutral-200 px-3 py-1.5 text-sm font-medium text-neutral-700 transition hover:bg-white">{{ $vehicle->is_active ? 'Archive' : 'Activate' }}</button>
                                            </form>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->user()->hasRole(\App\Enums\UserRole::Admin) ? 6 : 5 }}" class="px-5 py-12 text-center">
                                    <div class="font-medium text-neutral-950">No vehicles found.</div>
                                    <div class="mt-1 text-sm text-neutral-500">Add vehicle types before building quotations.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($vehicles->hasPages())
                <div class="border-t border-neutral-200 px-5 py-4">
                    {{ $vehicles->links() }}
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
