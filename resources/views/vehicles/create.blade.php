<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-semibold text-neutral-950">Create Vehicle</h1>
            <p class="mt-0.5 text-sm text-neutral-500">Add a rentable vehicle type to the master data.</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('vehicles.store') }}">
        @include('vehicles._form', [
            'method' => 'POST',
            'submitLabel' => 'Create vehicle',
        ])
    </form>
</x-app-layout>
