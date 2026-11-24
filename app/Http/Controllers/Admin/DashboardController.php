<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\License;
use App\Models\Property;
use App\Models\PropertyStatusChange;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Staff see counts for the whole site; architects see their properties by work status.
 */
class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user->isStaff()) {
            return view('admin.dashboardIndex', [
                'stats' => [
                    'published' => Property::where('status', 'published')->count(),
                    'drafts' => Property::where('status', 'draft')->count(),
                    'architects' => User::where('role', User::EDITOR)->count(),
                    'unreadContacts' => Contact::whereNull('read_at')->count(),
                    'licensesAttention' => License::needsAttention()->count(),
                ],
                'licenses' => License::needsAttention()->with('property:id,name')->orderBy('expires_at')->limit(8)->get(),
                'changes' => PropertyStatusChange::with(['property:id,name', 'user:id,name'])->latest('id')->limit(8)->get(),
            ]);
        }

        $properties = Property::visibleTo($user)->orderBy('name')->get(['id', 'name', 'city', 'work_status']);

        return view('admin.dashboardIndex', [
            'byStatus' => collect(Property::WORK_STATUSES)->map(fn ($label, $status) => $properties->where('work_status', $status)->values()),
            'licenses' => License::needsAttention()
                ->whereIn('property_id', $properties->pluck('id'))
                ->with('property:id,name')->orderBy('expires_at')->limit(8)->get(),
        ]);
    }
}
