<?php

namespace App\Http\Controllers\Admin;

use App\Http\Models\Options;
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
        $options = Options::all()->pluck('value', 'id');

        $site_title = !$options->isEmpty() ? $options[1] : '';
        $tagline = !$options->isEmpty() ? $options[2] : '';
        $email_address = !$options->isEmpty() ? $options[3] : '';
        $membership = !$options->isEmpty() ? $options[4] : '';

        return view('admin.optionsEdit', compact('site_title', 'tagline', 'email_address', 'membership'));
    }

    /**
     * Edit options from admin.
     *
     * @return \Illuminate\Http\Response
     */
    public function optionsEdit(OptionsRequest $request)
    {
        $options = Options::all()->pluck('name', 'id');

        foreach ( $options as $optionId => $optionName ) {
            $option = Options::find($optionId);

            if ( is_null( $request->get($optionName) ) ) {
                $option->value = '0';
            } else {
                $option->value = $request->get($optionName);
            }

            $option->save();
        }

        return back()->with('success', 'General options updated successfully.');
    }
}
