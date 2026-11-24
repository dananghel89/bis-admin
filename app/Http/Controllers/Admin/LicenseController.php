<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\StoresPdfFiles;
use App\Http\Controllers\Controller;
use App\Http\Requests\LicenseRequest;
use App\Models\License;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Licenses: PDFs with a number and dates. Everyone with `viewProperties` sees and
 * downloads the visible ones; staff upload, edit and delete them (`manageProperties`).
 */
class LicenseController extends Controller
{
    use StoresPdfFiles;

    /** The expiry filters on the list. */
    public const STATES = ['attention' => 'Expired or expiring soon', 'expired' => 'Expired', 'valid' => 'Valid'];

    public function index(Request $request): View
    {
        $user = $request->user();
        $propertyId = (int) $request->query('property') ?: null;
        $state = array_key_exists((string) $request->query('state'), self::STATES) ? $request->query('state') : null;

        $licenses = License::with('property:id,name,city')
            ->whereIn('property_id', Property::visibleTo($user)->select('id'))
            ->when($propertyId, fn ($query) => $query->where('property_id', $propertyId))
            ->when($state === 'attention', fn ($query) => $query->needsAttention())
            ->when($state === 'expired', fn ($query) => $query->whereDate('expires_at', '<', today()))
            ->when($state === 'valid', fn ($query) => $query->where(fn ($q) => $q->whereNull('expires_at')->orWhereDate('expires_at', '>=', today())))
            // Soonest expiry first; licenses without an expiry date last.
            ->orderByRaw('expires_at is null')
            ->orderBy('expires_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.licenses.licensesIndex', [
            'licenses' => $licenses,
            'properties' => $this->propertyOptions($user),
            'propertyId' => $propertyId,
            'state' => $state,
        ]);
    }

    public function licenseDownload(Request $request, License $license): StreamedResponse
    {
        return $this->downloadPdf($license, $request->user());
    }

    public function licenseAdd(Request $request): View
    {
        return view('admin.licenses.licenseForm', [
            'license' => new License(['property_id' => $request->integer('property') ?: null]),
            'properties' => $this->propertyOptions($request->user()),
        ]);
    }

    public function licenseAddSave(LicenseRequest $request): RedirectResponse|JsonResponse
    {
        $license = License::create($request->safe()->only(['property_id', 'title', 'number', 'issued_at', 'expires_at'])
            + $this->storePdf($request->file('file'), 'licenses')
            + ['uploaded_by' => $request->user()->id]);

        return $this->done($request, route('infoView', $license->property_id), "License {$license->title} uploaded.");
    }

    public function licenseEdit(Request $request, License $license): View
    {
        return view('admin.licenses.licenseForm', [
            'license' => $license,
            'properties' => $this->propertyOptions($request->user()),
        ]);
    }

    public function licenseEditSave(LicenseRequest $request, License $license): RedirectResponse|JsonResponse
    {
        $license->fill($request->safe()->only(['property_id', 'title', 'number', 'issued_at', 'expires_at']));

        if ($request->hasFile('file')) {
            $old = clone $license;
            $license->fill($this->storePdf($request->file('file'), 'licenses') + ['uploaded_by' => $request->user()->id]);
            $license->save();
            $old->deleteFile();
        } else {
            $license->save();
        }

        return $this->done($request, route('infoView', $license->property_id), 'License updated.');
    }

    public function licenseDelete(Request $request, License $license): RedirectResponse|JsonResponse
    {
        $license->delete();

        return $this->done($request, route('infoView', $license->property_id), "License {$license->title} deleted.");
    }
}
