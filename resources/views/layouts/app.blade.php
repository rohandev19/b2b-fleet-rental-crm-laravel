<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'B2B Fleet Rental CRM') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-neutral-100 font-sans text-neutral-950 antialiased">
        @php
            $user = auth()->user();
            $navigation = [
                ['label' => 'Dashboard', 'href' => route('dashboard'), 'active' => request()->routeIs('dashboard'), 'enabled' => true, 'roles' => ['admin', 'sales', 'manager', 'finance']],
                ['label' => 'Prospects', 'href' => route('prospects.index'), 'active' => request()->routeIs('prospects.*'), 'enabled' => true, 'roles' => ['admin', 'sales', 'manager', 'finance']],
                ['label' => 'Pipeline', 'href' => '#', 'active' => false, 'enabled' => false, 'roles' => ['admin', 'sales', 'manager']],
                ['label' => 'Follow-ups', 'href' => route('follow-ups.today'), 'active' => request()->routeIs('follow-ups.*'), 'enabled' => true, 'roles' => ['admin', 'sales', 'manager']],
                ['label' => 'Quotations', 'href' => '#', 'active' => false, 'enabled' => false, 'roles' => ['admin', 'sales', 'manager', 'finance']],
                ['label' => 'Vehicles', 'href' => '#', 'active' => false, 'enabled' => false, 'roles' => ['admin', 'manager', 'finance']],
                ['label' => 'Rental Packages', 'href' => '#', 'active' => false, 'enabled' => false, 'roles' => ['admin', 'manager', 'finance']],
                ['label' => 'Reports', 'href' => '#', 'active' => false, 'enabled' => false, 'roles' => ['admin', 'manager', 'finance']],
                ['label' => 'Users', 'href' => route('users.index'), 'active' => request()->routeIs('users.*'), 'enabled' => true, 'roles' => ['admin']],
                ['label' => 'Audit Logs', 'href' => '#', 'active' => false, 'enabled' => false, 'roles' => ['admin', 'manager']],
            ];
        @endphp

        <div x-data="{ sidebarOpen: false }" class="min-h-screen lg:flex">
            <div x-cloak x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-30 bg-neutral-950/40 lg:hidden" @click="sidebarOpen = false"></div>

            <aside
                class="fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full flex-col border-r border-neutral-200 bg-white transition lg:static lg:translate-x-0"
                :class="{ 'translate-x-0': sidebarOpen }"
            >
                <div class="flex h-16 items-center gap-3 border-b border-neutral-200 px-5">
                    <div class="flex size-10 items-center justify-center rounded-lg bg-neutral-950 text-sm font-bold text-white">HN</div>
                    <div>
                        <div class="text-sm font-semibold leading-5">HAN Fleet CRM</div>
                        <div class="text-xs text-neutral-500">Rental Operations</div>
                    </div>
                </div>

                <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
                    @foreach ($navigation as $item)
                        @if (in_array($user->role->value, $item['roles'], true))
                            <a
                                href="{{ $item['href'] }}"
                                @class([
                                    'group flex items-center justify-between rounded-lg px-3 py-2.5 text-sm font-medium transition',
                                    'bg-neutral-950 text-white' => $item['active'],
                                    'text-neutral-700 hover:bg-neutral-100 hover:text-neutral-950' => ! $item['active'] && $item['enabled'],
                                    'cursor-not-allowed text-neutral-400' => ! $item['enabled'],
                                ])
                                @if (! $item['enabled']) aria-disabled="true" onclick="return false;" @endif
                            >
                                <span>{{ $item['label'] }}</span>
                                @if (! $item['enabled'])
                                    <span class="rounded-full bg-neutral-100 px-2 py-0.5 text-[11px] font-medium text-neutral-500">Soon</span>
                                @endif
                            </a>
                        @endif
                    @endforeach
                </nav>

                <div class="border-t border-neutral-200 p-4">
                    <div class="rounded-lg bg-neutral-50 p-3">
                        <div class="text-sm font-semibold text-neutral-900">{{ $user->name }}</div>
                        <div class="mt-0.5 truncate text-xs text-neutral-500">{{ $user->email }}</div>
                        <div class="mt-3 inline-flex rounded-full bg-sky-50 px-2.5 py-1 text-xs font-semibold text-sky-700">
                            {{ $user->role->label() }}
                        </div>
                    </div>
                </div>
            </aside>

            <div class="min-w-0 flex-1">
                <header class="sticky top-0 z-20 border-b border-neutral-200 bg-white/95 backdrop-blur">
                    <div class="flex h-16 items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                        <div class="flex min-w-0 items-center gap-3">
                            <button
                                type="button"
                                class="inline-flex size-10 items-center justify-center rounded-lg border border-neutral-200 text-neutral-700 lg:hidden"
                                @click="sidebarOpen = true"
                                aria-label="Open navigation"
                            >
                                <span class="block h-0.5 w-5 bg-current before:block before:h-0.5 before:w-5 before:-translate-y-1.5 before:bg-current before:content-[''] after:block after:h-0.5 after:w-5 after:translate-y-1 after:bg-current after:content-['']"></span>
                            </button>

                            <div class="min-w-0">
                                @isset($header)
                                    {{ $header }}
                                @else
                                    <h1 class="truncate text-lg font-semibold text-neutral-950">Dashboard</h1>
                                @endisset
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <a href="{{ route('profile.edit') }}" class="hidden rounded-lg border border-neutral-200 px-3 py-2 text-sm font-medium text-neutral-700 transition hover:bg-neutral-50 sm:inline-flex">
                                Profile
                            </a>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="rounded-lg bg-neutral-950 px-3 py-2 text-sm font-semibold text-white transition hover:bg-neutral-800">
                                    Log out
                                </button>
                            </form>
                        </div>
                    </div>
                </header>

                <main class="px-4 py-6 sm:px-6 lg:px-8">
                    @if (session('status'))
                        <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">
                            {{ session('error') }}
                        </div>
                    @endif

                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
