<?php

namespace Ashrafic\AiOrbit\Http\Controllers;

use Ashrafic\AiOrbit\Models\ArenaSession;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

class ArenaController extends Controller
{
    public function index(): View
    {
        $sessions = ArenaSession::orderBy('created_at', 'desc')->paginate(15);

        return view('ai-orbit::arena.index', [
            'sessions' => $sessions,
        ]);
    }

    public function show(string $id): View
    {
        $session = ArenaSession::findOrFail($id);

        return view('ai-orbit::arena.show', [
            'session' => $session,
        ]);
    }
}
