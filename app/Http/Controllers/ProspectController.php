<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\Prospect\StoreProspectRequest;
use App\Http\Requests\Prospect\UpdateProspectRequest;
use App\Models\Prospect;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProspectController extends Controller
{
    public function index(Request $request): View
    {
        $prospects = Prospect::query()
            ->with('assignedSales')
            ->withCount('contacts')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function ($query) use ($search) {
                    $query
                        ->where('company_name', 'like', "%{$search}%")
                        ->orWhere('industry', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('priority'), fn ($query) => $query->where('priority', $request->string('priority')))
            ->when($request->filled('assigned_sales_id'), fn ($query) => $query->where('assigned_sales_id', $request->integer('assigned_sales_id')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('prospects.index', [
            'prospects' => $prospects,
            'salesUsers' => $this->salesUsers(),
            'filters' => $request->only(['search', 'status', 'priority', 'assigned_sales_id']),
        ]);
    }

    public function create(): View
    {
        return view('prospects.create', [
            'salesUsers' => $this->salesUsers(),
        ]);
    }

    public function pipeline(Request $request): View
    {
        $prospects = Prospect::query()
            ->with(['assignedSales:id,name'])
            ->withCount(['contacts', 'quotations'])
            ->when($request->user()->hasRole(UserRole::Sales), fn ($query) => $query->where('assigned_sales_id', $request->user()->id))
            ->orderByRaw("case priority when 'high' then 1 when 'medium' then 2 else 3 end")
            ->latest('updated_at')
            ->get()
            ->groupBy('status');

        $stages = collect(Prospect::STATUSES)
            ->mapWithKeys(fn (string $status) => [$status => $prospects->get($status, collect())]);

        return view('prospects.pipeline', [
            'stages' => $stages,
        ]);
    }

    public function store(StoreProspectRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $request->validated();

        if ($request->user()->hasRole(UserRole::Sales) && empty($data['assigned_sales_id'])) {
            $data['assigned_sales_id'] = $request->user()->id;
        }

        $prospect = Prospect::query()->create($data);

        $auditLogger->log(
            'prospect.created',
            "Created prospect {$prospect->company_name}.",
            $prospect,
            null,
            $prospect->only(['company_name', 'status', 'priority', 'assigned_sales_id']),
            $request,
        );

        return redirect()
            ->route('prospects.show', $prospect)
            ->with('status', 'Prospect created successfully.');
    }

    public function show(Prospect $prospect): View
    {
        $prospect->load([
            'assignedSales',
            'contacts' => fn ($query) => $query->latest(),
            'followUpActivities' => fn ($query) => $query->with(['contact', 'user'])->latest('activity_date'),
        ]);

        return view('prospects.show', [
            'prospect' => $prospect,
        ]);
    }

    public function edit(Prospect $prospect): View
    {
        return view('prospects.edit', [
            'prospect' => $prospect,
            'salesUsers' => $this->salesUsers(),
        ]);
    }

    public function update(UpdateProspectRequest $request, Prospect $prospect, AuditLogger $auditLogger): RedirectResponse
    {
        $before = $prospect->only(['company_name', 'status', 'priority', 'assigned_sales_id', 'next_follow_up_at']);

        $prospect->update($request->validated());

        $auditLogger->log(
            'prospect.updated',
            "Updated prospect {$prospect->company_name}.",
            $prospect,
            $before,
            $prospect->fresh()->only(['company_name', 'status', 'priority', 'assigned_sales_id', 'next_follow_up_at']),
            $request,
        );

        return redirect()
            ->route('prospects.show', $prospect)
            ->with('status', 'Prospect updated successfully.');
    }

    public function destroy(Prospect $prospect, AuditLogger $auditLogger): RedirectResponse
    {
        $before = $prospect->only(['company_name', 'status', 'priority', 'assigned_sales_id']);

        $prospect->delete();

        $auditLogger->log(
            'prospect.deleted',
            "Deleted prospect {$prospect->company_name}.",
            $prospect,
            $before,
            null,
        );

        return redirect()
            ->route('prospects.index')
            ->with('status', 'Prospect deleted successfully.');
    }

    private function salesUsers()
    {
        return User::query()
            ->where('role', UserRole::Sales)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }
}
