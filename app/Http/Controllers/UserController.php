<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $users = User::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function ($query) use ($search) {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('role'), fn ($query) => $query->where('role', $request->string('role')))
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->string('status')->toString() === 'active'))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('users.index', [
            'users' => $users,
            'roles' => UserRole::cases(),
            'filters' => $request->only(['search', 'role', 'status']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('users.create', [
            'roles' => UserRole::cases(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $user = User::query()->create($request->safe()->merge([
            'is_active' => $request->boolean('is_active', true),
        ])->all());

        $auditLogger->log(
            'user.created',
            "Created user {$user->email}.",
            $user,
            null,
            $user->only(['name', 'email', 'role', 'is_active']),
            $request,
        );

        return redirect()
            ->route('users.index')
            ->with('status', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): RedirectResponse
    {
        return redirect()->route('users.edit', $user);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): View
    {
        return view('users.edit', [
            'user' => $user,
            'roles' => UserRole::cases(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user, AuditLogger $auditLogger): RedirectResponse
    {
        $before = $user->only(['name', 'email', 'role', 'is_active']);
        $validated = $request->safe()->except(['password']);

        if ($request->filled('password')) {
            $validated['password'] = $request->string('password')->toString();
        }

        $validated['is_active'] = $request->boolean('is_active');

        if ($user->is(auth()->user()) && ! $validated['is_active']) {
            return back()
                ->withInput()
                ->with('error', 'You cannot deactivate your own account.');
        }

        $user->update($validated);

        $auditLogger->log(
            'user.updated',
            "Updated user {$user->email}.",
            $user,
            $before,
            $user->fresh()->only(['name', 'email', 'role', 'is_active']),
            $request,
        );

        return redirect()
            ->route('users.index')
            ->with('status', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        return $this->toggleActive($user);
    }

    public function toggleActive(User $user): RedirectResponse
    {
        if ($user->is(auth()->user())) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $before = $user->only(['is_active']);

        $user->update([
            'is_active' => ! $user->is_active,
        ]);

        app(AuditLogger::class)->log(
            $user->is_active ? 'user.activated' : 'user.deactivated',
            ($user->is_active ? 'Activated' : 'Deactivated')." user {$user->email}.",
            $user,
            $before,
            $user->fresh()->only(['is_active']),
        );

        return back()->with('status', $user->is_active ? 'User activated successfully.' : 'User deactivated successfully.');
    }
}
