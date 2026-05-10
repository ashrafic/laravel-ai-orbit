<?php

namespace Ashrafic\AiOrbit\Http\Controllers;

use Ashrafic\AiOrbit\Services\ConversationRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

class TraceController extends Controller
{
    /**
     * Display the execution trace for a conversation.
     */
    public function show(string $id, ConversationRepository $repository): View
    {
        $conversation = $repository->find($id);

        if ($conversation === null) {
            abort(404);
        }

        return view('ai-orbit::traces.show', [
            'conversation' => $conversation,
            'messages' => $conversation->messages ?? collect(),
        ]);
    }
}
