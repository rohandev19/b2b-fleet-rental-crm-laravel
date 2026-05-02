<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-semibold text-neutral-950">Users</h1>
            <p class="mt-0.5 text-sm text-neutral-500">Manage CRM access, roles, and account status.</p>
        </div>
    </x-slot>

    <div class="space-y-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <form method="GET" action="{{ route('users.index') }}" class="grid gap-3 sm:grid-cols-[minmax(220px,1fr)_180px_160px_auto]">
                <input
                    type="search"
                    name="search"
                    value="{{ $filters['search'] ?? '' }}"
                    placeholder="Search name or email"
                    class="rounded-lg border-neutral-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >

                <select name="role" class="rounded-lg border-neutral-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All roles</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->value }}" @selected(($filters['role'] ?? '') === $role->value)>{{ $role->label() }}</option>
                    @endforeach
                </select>

                <select name="status" class="rounded-lg border-neutral-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All status</option>
                    <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                    <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
                </select>

                <button type="submit" class="rounded-lg border border-neutral-200 bg-white px-4 py-2 text-sm font-medium text-neutral-700 transition hover:bg-neutral-50">
                    Filter
                </button>
            </form>

            <a href="{{ route('users.create') }}" class="inline-flex items-center justify-center rounded-lg bg-neutral-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-neutral-800">
                Add user
            </a>
        </div>

        <section class="overflow-hidden rounded-lg border border-neutral-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200 text-left text-sm">
                    <thead class="bg-neutral-50 text-xs uppercase tracking-normal text-neutral-500">
                        <tr>
                            <th scope="col" class="px-5 py-3 font-semibold">User</th>
                            <th scope="col" class="px-5 py-3 font-semibold">Role</th>
                            <th scope="col" class="px-5 py-3 font-semibold">Status</th>
                            <th scope="col" class="px-5 py-3 font-semibold">Created</th>
                            <th scope="col" class="px-5 py-3 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100">
                        @forelse ($users as $user)
                            <tr class="hover:bg-neutral-50">
                                <td class="px-5 py-4">
                                    <div class="font-medium text-neutral-950">{{ $user->name }}</div>
                                    <div class="mt-0.5 text-neutral-500">{{ $user->email }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full bg-sky-50 px-2.5 py-1 text-xs font-semibold text-sky-700">
                                        {{ $user->role->label() }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    @if ($user->is_active)
                                        <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">Active</span>
                                    @else
                                        <span class="rounded-full bg-neutral-100 px-2.5 py-1 text-xs font-semibold text-neutral-600">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-neutral-500">{{ $user->created_at->format('d M Y') }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('users.edit', $user) }}" class="rounded-lg border border-neutral-200 px-3 py-1.5 text-sm font-medium text-neutral-700 transition hover:bg-white">
                                            Edit
                                        </a>

                                        <form method="POST" action="{{ route('users.toggle-active', $user) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button
                                                type="submit"
                                                class="rounded-lg border border-neutral-200 px-3 py-1.5 text-sm font-medium text-neutral-700 transition hover:bg-white disabled:cursor-not-allowed disabled:opacity-50"
                                                @disabled($user->is(auth()->user()))
                                            >
                                                {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-12 text-center">
                                    <div class="font-medium text-neutral-950">No users found.</div>
                                    <div class="mt-1 text-sm text-neutral-500">Create an internal account to give the team CRM access.</div>
                                    <a href="{{ route('users.create') }}" class="mt-4 inline-flex rounded-lg bg-neutral-950 px-4 py-2 text-sm font-semibold text-white">
                                        Add user
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="border-t border-neutral-200 px-5 py-4">
                    {{ $users->links() }}
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
