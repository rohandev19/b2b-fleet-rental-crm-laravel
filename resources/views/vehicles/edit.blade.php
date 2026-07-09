<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-semibold text-neutral-950">Edit Vehicle</h1>
            <p class="mt-0.5 text-sm text-neutral-500">{{ $vehicle->brand }} {{ $vehicle->model }}</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('vehicles.update', $vehicle) }}">
        @include('vehicles._form', [
            'method' => 'PUT',
            'submitLabel' => 'Save vehicle',
        ])
    </form>
</x-app-layout>
