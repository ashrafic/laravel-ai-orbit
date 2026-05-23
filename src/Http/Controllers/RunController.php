<?php

namespace Ashrafic\AiOrbit\Http\Controllers;

use Ashrafic\AiOrbit\Services\AiRunRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

class RunController extends Controller
{
    public function index(): View
    {
        return view('ai-orbit::runs.index');
    }

    public function show(string $id, AiRunRepository $runs): View
    {
        $run = $runs->find($id);

        abort_unless($run !== null, 404);

        return view('ai-orbit::runs.show', ['run' => $run]);
    }
}
