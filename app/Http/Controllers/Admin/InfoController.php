<?php

namespace App\Http\Controllers\Admin;

use App\User;
use App\Http\Models\Info;
use App\Http\Requests\InfoAddRequest;
use App\Http\Requests\InfoEditRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class InfoController extends Controller
{
    /**
     * Show the admin all infos.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = User::class;
        $infos = Info::all()->toArray();

        return view('admin.infos.infosIndex', compact('user', 'infos'));
    }

    /**
     * Show the admin add info.
     *
     * @return \Illuminate\Http\Response
     */
    public function infoAdd()
    {
        $statuses = get_post_statuses();

        return view('admin.infos.infoAdd', compact('statuses'));
    }

    /**
     * Show the admin single info.
     *
     * @return \Illuminate\Http\Response
     */
    public function infoView($id)
    {
        $info = Info::find($id)->toArray();

        return view('admin.infos.infoView', compact('info'));
    }

    /**
     * Show the admin edit info.
     *
     * @return \Illuminate\Http\Response
     */
    public function infoEdit($id)
    {
        $statuses = get_post_statuses();
        $info = Info::find($id)->toArray();

        return view('admin.infos.infoEdit', compact('statuses', 'info'));
    }

    /**
     * Add info from admin.
     *
     * @return \Illuminate\Http\Response
     */
    public function infoAddSave(InfoAddRequest $request)
    {
        $info = Info::create([
            'title' => $request->get('title'),
            'content' => $request->get('content'),
            'author' => Auth::user()->id,
            'status' => $request->get('status'),
        ]);

        return redirect()->route('infoEdit', $info->id)->with('success', 'Info published successfully.');
    }

    /**
     * Edit info from admin.
     *
     * @return \Illuminate\Http\Response
     */
    public function infoEditSave(InfoEditRequest $request, $id)
    {
        $info = Info::find($id);

        $info->title = $request->get('title');
        $info->content = $request->get('content');
        $info->status = $request->get('status');

        $info->save();

        return back()->with('success', 'Info updated successfully.');
    }

    /**
     * Delete info from admin.
     *
     * @return \Illuminate\Http\Response
     */
    public function infoDelete($id)
    {
        $info = Info::find($id);

        $info->delete();

        return redirect()->route('infos')->with('success', 'Info deleted successfully.');
    }
}
