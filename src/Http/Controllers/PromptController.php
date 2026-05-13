<?php

namespace Ashrafic\AiOrbit\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

class PromptController extends Controller
{
    public function index(): View
    {
        return view('ai-orbit::prompts.index');
    }
}
