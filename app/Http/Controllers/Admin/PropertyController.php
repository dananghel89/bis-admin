<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PropertyRequest;
use App\Http\Requests\WorkStatusRequest;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Properties ("Infos" in the menu). Everyone with `viewProperties` sees the list
 * and pages of the properties visible to them (Property::visibleTo); staff
 * (`manageProperties`) add, edit and delete them.
 */
class PropertyController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $search = trim((string) $request->query('search'));
        $workStatus = $request->query('work_status');
        $status = $user->isStaff() ? $request->query('status') : null;

        $properties = Property::visibleTo($user)
            ->with('architects:id,name')
            ->withCount(['plans', 'licenses'])
            ->when($search !== '', fn ($query) => $query->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('city', 'like', "%{$search}%")
                ->orWhere('address', 'like', "%{$search}%")))
            ->when(array_key_exists((string) $workStatus, Property::WORK_STATUSES), fn ($query) => $query->where('work_status', $workStatus))
            ->when(array_key_exists((string) $status, Property::STATUSES), fn ($query) => $query->where('status', $status))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.properties.propertiesIndex', compact('properties', 'search', 'workStatus', 'status'));
    }

    public function propertyAdd(): View
    {
        return view('admin.properties.propertyForm', [
            'property' => new Property(['city' => 'Barcelona']),
            'architects' => $this->architectOptions(),
        ]);
    }

    public function propertyAddSave(PropertyRequest $request): RedirectResponse|JsonResponse
    {
        $property = DB::transaction(function () use ($request) {
            $property = Property::create($request->safe()->except('architects') + ['created_by' => $request->user()->id]);
            $property->architects()->sync($request->validated('architects'));

            return $property;
        });

        return $this->done($request, route('infoView', $property), "Property {$property->name} added.");
    }

    public function propertyView(Request $request, int $property): View
    {
        $property = Property::visibleTo($request->user())
            ->with(['architects' => fn ($query) => $query->orderBy('name'), 'statusChanges.user', 'creator'])
            ->findOrFail($property);

        return view('admin.properties.propertyView', compact('property'));
    }

    public function propertyEdit(Property $property): View
    {
        $property->load('architects:id');

        return view('admin.properties.propertyForm', [
            'property' => $property,
            'architects' => $this->architectOptions(),
        ]);
    }

    public function propertyEditSave(PropertyRequest $request, Property $property): RedirectResponse|JsonResponse
    {
        DB::transaction(function () use ($request, $property) {
            $property->update($request->safe()->except('architects'));
            $property->architects()->sync($request->validated('architects'));
        });

        return $this->done($request, route('infoView', $property), 'Property updated.');
    }

    /**
     * Also deletes its plans, licenses, their files, assignments and history.
     */
    public function propertyDelete(Request $request, Property $property): RedirectResponse|JsonResponse
    {
        $property->delete();

        return $this->done($request, route('infos'), "Property {$property->name} deleted.");
    }

    /**
     * Staff, or an architect who sees the property, change its work status; every
     * change is kept in its history.
     */
    public function workStatus(WorkStatusRequest $request, Property $property): RedirectResponse|JsonResponse
    {
        DB::transaction(function () use ($request, $property) {
            $property->statusChanges()->create([
                'user_id' => $request->user()->id,
                'from_status' => $property->work_status,
                'to_status' => $request->validated('work_status'),
                'note' => $request->validated('note'),
            ]);

            $property->update(['work_status' => $request->validated('work_status')]);
        });

        return $this->done($request, route('infoView', $property), "Work status changed to {$property->workStatusLabel()}.");
    }

    /**
     * @return array<int, string>
     */
    protected function architectOptions(): array
    {
        return User::where('role', User::EDITOR)->orderBy('name')->pluck('name', 'id')->all();
    }
}
