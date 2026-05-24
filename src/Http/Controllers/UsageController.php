<?php

namespace Ashrafic\AiOrbit\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

class UsageController extends Controller
{
    /**
     * Display the usage page.
     */
    public function index(): View
    {
        return view('ai-orbit::usage.index');
    }

    /**
     * Display the pricing matrix.
     */
    public function pricing(): View
    {
        return view('ai-orbit::usage.pricing');
    }

    /**
     * Display the budget alerts.
     */
    public function alerts(): View
    {
        return view('ai-orbit::usage.alerts');
    }

    /**
     * Display the provider health panel.
     */
    public function health(): View
    {
        return view('ai-orbit::usage.health');
    }
}
