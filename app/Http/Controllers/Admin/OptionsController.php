<?php

namespace App\Http\Controllers\Admin;

use App\Models\Options;
use App\Http\Requests\OptionsRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OptionsController extends Controller
{
    /**
     * Edit options from admin.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $options = Options::all()->pluck('value', 'name');

        $site_title = $options->get('site_title', '');
        $tagline = $options->get('tagline', '');
        $email_address = $options->get('email_address', '');
        $membership = $options->get('membership', '');

        return view('admin.optionsEdit', compact('site_title', 'tagline', 'email_address', 'membership'));
    }

    /**
     * Edit options from admin.
     *
     * @return \Illuminate\Http\Response
     */
    public function optionsEdit(OptionsRequest $request)
    {
        // Only the options on this form; any other row in the table is left alone.
        // An unchecked checkbox (membership) isn't sent at all, so it's stored as '0'.
        foreach ( ['site_title', 'tagline', 'email_address', 'membership'] as $name ) {
            $value = $request->get($name);

            Options::updateOrCreate(['name' => $name], ['value' => is_null($value) ? '0' : $value]);
        }

        // get_option() must not keep serving the values loaded before the save.
        app()->forgetInstance('project.options');

        return back()->with('success', 'General options updated successfully.');
    }
}
