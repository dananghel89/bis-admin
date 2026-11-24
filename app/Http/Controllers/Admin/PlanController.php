<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\StoresPdfFiles;
use App\Http\Controllers\Controller;
use App\Http\Requests\PlanRequest;
use App\Models\Plan;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Plans: a map of the visible properties and their plan PDFs. Everyone with
 * `viewProperties` sees and downloads the visible ones; staff upload, edit and
 * delete them (`manageProperties`).
 */
class PlanController extends Controller
{
    use StoresPdfFiles;

    public function index(Request $request): View
    {
        $user = $request->user();
        $visible = Property::visibleTo($user);
        $propertyId = (int) $request->query('property') ?: null;

        $markers = (clone $visible)->whereNotNull('latitude')->whereNotNull('longitude')
            ->withCount('plans')->orderBy('name')->get()
            ->map(fn (Property $property) => [
                'lat' => $property->latitude,
                'lng' => $property->longitude,
                'title' => $property->name,
                'subtitle' => $property->fullAddress().' · '.trans_choice(':count plan|:count plans', $property->plans_count),
                'url' => route('infoView', $property),
            ])->values();

        $plans = Plan::with('property:id,name,city')
            ->whereIn('property_id', (clone $visible)->select('id'))
            ->when($propertyId, fn ($query) => $query->where('property_id', $propertyId))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.plans.plansIndex', [
            'plans' => $plans,
            'markers' => $markers,
            'properties' => $this->propertyOptions($user),
            'propertyId' => $propertyId,
        ]);
    }

    public function planDownload(Request $request, Plan $plan): StreamedResponse
    {
        return $this->downloadPdf($plan, $request->user());
    }

    public function planAdd(Request $request): View
    {
        return view('admin.plans.planForm', [
            'plan' => new Plan(['property_id' => $request->integer('property') ?: null]),
            'properties' => $this->propertyOptions($request->user()),
        ]);
    }

    public function planAddSave(PlanRequest $request): RedirectResponse|JsonResponse
    {
        $plan = Plan::create($request->safe()->only(['property_id', 'title', 'description'])
            + $this->storePdf($request->file('file'), 'plans')
            + ['uploaded_by' => $request->user()->id]);

        return $this->done($request, route('infoView', $plan->property_id), "Plan {$plan->title} uploaded.");
    }

    public function planEdit(Request $request, Plan $plan): View
    {
        return view('admin.plans.planForm', [
            'plan' => $plan,
            'properties' => $this->propertyOptions($request->user()),
        ]);
    }

    public function planEditSave(PlanRequest $request, Plan $plan): RedirectResponse|JsonResponse
    {
        $plan->fill($request->safe()->only(['property_id', 'title', 'description']));

        if ($request->hasFile('file')) {
            $old = clone $plan;
            $plan->fill($this->storePdf($request->file('file'), 'plans') + ['uploaded_by' => $request->user()->id]);
            $plan->save();
            $old->deleteFile();
        } else {
            $plan->save();
        }

        return $this->done($request, route('infoView', $plan->property_id), 'Plan updated.');
    }

    public function planDelete(Request $request, Plan $plan): RedirectResponse|JsonResponse
    {
        $plan->delete();

        return $this->done($request, route('infoView', $plan->property_id), "Plan {$plan->title} deleted.");
    }
}
