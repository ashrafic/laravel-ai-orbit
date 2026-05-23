<?php

namespace Ashrafic\AiOrbit\Http\Controllers;

use Ashrafic\AiOrbit\Services\AiRunRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class RunController extends Controller
{
    public function index(Request $request, AiRunRepository $runs): View
    {
        return view('ai-orbit::runs.index', [
            'runs' => $runs->list($request->only([
                'operation',
                'status',
                'provider',
                'model',
                'agent_class',
                'conversation_state',
                'date_from',
                'date_to',
            ])),
            'filters' => $request->query(),
        ]);
    }

    public function show(string $id, AiRunRepository $runs): View
    {
        $run = $runs->find($id);

        abort_unless($run !== null, 404);

        return view('ai-orbit::runs.show', ['run' => $run]);
    }
}
