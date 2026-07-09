<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-semibold text-neutral-950">Create Rental Package</h1>
            <p class="mt-0.5 text-sm text-neutral-500">Add a package option for future quotations.</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('rental-packages.store') }}">
        @include('rental-packages._form', [
            'method' => 'POST',
            'submitLabel' => 'Create package',
        ])
    </form>
</x-app-layout>
