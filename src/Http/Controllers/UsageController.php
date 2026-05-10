<?php

namespace Ashraf\Orbit\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

class UsageController extends Controller
{
    /**
     * Display the usage statistics page.
     */
    public function index(): View
    {
        return view('orbit::usage.index');
    }
}
