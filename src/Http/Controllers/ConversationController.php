<?php

namespace Ashrafic\AiOrbit\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

class ConversationController extends Controller
{
    /**
     * Display the thread explorer.
     */
    public function index(): View
    {
        return view('ai-orbit::conversations.index', [
            'useProExplorer' => true,
        ]);
    }

    /**
     * Display the message timeline for a conversation.
     */
    public function show(string $id): View
    {
        return view('ai-orbit::conversations.show', ['id' => $id]);
    }
}
