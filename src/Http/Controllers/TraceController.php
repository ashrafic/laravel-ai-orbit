<?php

namespace Ashraf\Orbit\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

class TraceController extends Controller
{
    /**
     * Display the execution trace for a conversation.
     */
    public function show(string $id): View
    {
        return view('orbit::traces.show', ['id' => $id]);
    }
}
