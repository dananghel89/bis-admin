<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EmailController extends Controller
{
    /**
     * Show the admin messages.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.messages.messagesInbox');
    }

    /**
     * Show the admin sent messages.
     *
     * @return \Illuminate\Http\Response
     */
    public function messagesSent()
    {
        return view('admin.messages.messagesSent');
    }

    /**
     * Send message from admin.
     *
     * @return \Illuminate\Http\Response
     */
    public function messageSend()
    {
        return view('admin.messages.messageSend');
    }

    /**
     * View message from admin.
     *
     * @return \Illuminate\Http\Response
     */
    public function messageView($id)
    {
        return view('admin.messages.messageView');
    }
}
