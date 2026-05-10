<?php

namespace Ashraf\Orbit\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

class ConversationController extends Controller
{
    /**
     * Display the thread explorer.
     */
    public function index(): View
    {
        return view('orbit::conversations.index');
    }

    /**
     * Display the message timeline for a conversation.
     */
    public function show(string $id): View
    {
        return view('orbit::conversations.show', ['id' => $id]);
    }
}
