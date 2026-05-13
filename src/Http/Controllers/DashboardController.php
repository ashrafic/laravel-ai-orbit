<?php

namespace Ashrafic\AiOrbit\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

class DashboardController extends Controller
{
    /**
     * Display the Orbit dashboard.
     */
    public function index(): View
    {
        return view('ai-orbit::dashboard');
    }
}
