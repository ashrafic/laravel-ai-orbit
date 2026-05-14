<?php

namespace Ashrafic\AiOrbit\Http\Controllers;

use Ashrafic\AiOrbit\Models\PromptLabSession;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

class PromptLabController extends Controller
{
    public function index(): View
    {
        $sessions = PromptLabSession::orderBy('created_at', 'desc')->paginate(15);

        return view('ai-orbit::prompt-lab.index', [
            'sessions' => $sessions,
        ]);
    }

    public function show(string $id): View
    {
        $session = PromptLabSession::findOrFail($id);

        return view('ai-orbit::prompt-lab.show', [
            'session' => $session,
        ]);
    }
}
