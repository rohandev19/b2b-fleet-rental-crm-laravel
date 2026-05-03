<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-semibold text-neutral-950">Create User</h1>
            <p class="mt-0.5 text-sm text-neutral-500">Add an internal CRM user account.</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('users.store') }}">
        @include('users._form', [
            'method' => 'POST',
            'submitLabel' => 'Create user',
        ])
    </form>
</x-app-layout>
