<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-semibold text-neutral-950">Edit User</h1>
            <p class="mt-0.5 text-sm text-neutral-500">{{ $user->email }}</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('users.update', $user) }}">
        @include('users._form', [
            'method' => 'PUT',
            'submitLabel' => 'Save changes',
        ])
    </form>
</x-app-layout>
