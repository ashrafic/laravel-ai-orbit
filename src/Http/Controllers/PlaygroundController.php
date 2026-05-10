<?php

namespace Ashrafic\AiOrbit\Http\Controllers;

use Ashrafic\AiOrbit\Contracts\AgentRegistryContract;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

class PlaygroundController extends Controller
{
    /**
     * Display the agent list.
     */
    public function index(): View
    {
        $registry = app(AgentRegistryContract::class);

        return view('ai-orbit::playground.index', [
            'agents' => $registry->all(),
        ]);
    }

    /**
     * Display the sandbox for a specific agent.
     */
    public function show(string $agent): View
    {
        return view('ai-orbit::playground.show', ['agent' => $agent]);
    }
}
