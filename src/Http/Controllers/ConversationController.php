<?php

namespace Ashraf\LaravelAiOrbit\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

class ConversationController extends Controller
{
    /**
     * Display the thread explorer.
     */
    public function index(): View
    {
        return view('laravel-ai-orbit::conversations.index');
    }

    /**
     * Display the message timeline for a conversation.
     */
    public function show(string $id): View
    {
        return view('laravel-ai-orbit::conversations.show', ['id' => $id]);
    }
}
