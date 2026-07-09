@csrf

@if ($method !== 'POST')
    @method($method)
@endif

<div class="grid gap-6 lg:grid-cols-[1fr_320px]">
    <section class="rounded-lg border border-neutral-200 bg-white shadow-sm">
        <div class="border-b border-neutral-200 px-5 py-4">
            <h2 class="text-base font-semibold text-neutral-950">User Details</h2>
        </div>

        <div class="space-y-5 p-5">
            <div>
                <x-input-label for="name" value="Name" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name ?? '')" required autofocus />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div>
                <x-input-label for="email" value="Email" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email ?? '')" required />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />
            </div>

            <div>
                <x-input-label for="role" value="Role" />
                <select id="role" name="role" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    @foreach ($roles as $role)
                        <option value="{{ $role->value }}" @selected(old('role', isset($user) ? $user->role->value : 'sales') === $role->value)>
                            {{ $role->label() }}
                        </option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('role')" />
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <x-input-label for="password" :value="isset($user) ? 'New Password' : 'Password'" />
                    <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" :required="! isset($user)" autocomplete="new-password" />
                    <x-input-error class="mt-2" :messages="$errors->get('password')" />
                </div>

                <div>
                    <x-input-label for="password_confirmation" value="Confirm Password" />
                    <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" :required="! isset($user)" autocomplete="new-password" />
                </div>
            </div>
        </div>
    </section>

    <aside class="space-y-4">
        <section class="rounded-lg border border-neutral-200 bg-white p-5 shadow-sm">
            <h2 class="text-base font-semibold text-neutral-950">Access Status</h2>
            <label class="mt-4 flex items-start gap-3">
                <input type="hidden" name="is_active" value="0">
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    class="mt-1 rounded border-neutral-300 text-neutral-950 shadow-sm focus:ring-neutral-800"
                    @checked(old('is_active', isset($user) ? $user->is_active : true))
                >
                <span>
                    <span class="block text-sm font-medium text-neutral-900">Active user</span>
                    <span class="mt-1 block text-sm text-neutral-500">Inactive users cannot sign in.</span>
                </span>
            </label>
            <x-input-error class="mt-2" :messages="$errors->get('is_active')" />
        </section>

        <div class="flex justify-end gap-3">
            <a href="{{ route('users.index') }}" class="rounded-lg border border-neutral-200 px-4 py-2 text-sm font-medium text-neutral-700 transition hover:bg-neutral-50">
                Cancel
            </a>
            <button type="submit" class="rounded-lg bg-neutral-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-neutral-800">
                {{ $submitLabel }}
            </button>
        </div>
    </aside>
</div>
