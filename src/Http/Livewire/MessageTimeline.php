<?php

namespace Ashraf\Orbit\Http\Livewire;

use Ashraf\Orbit\Services\ConversationRepository;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class MessageTimeline extends Component
{
    public string $conversationId;

    public ?object $conversation = null;

    public bool $showRawPayload = false;

    public function mount(string $conversationId): void
    {
        $this->conversationId = $conversationId;
        $this->conversation = app(ConversationRepository::class)->find($conversationId);
    }

    public function toggleRawPayload(): void
    {
        $this->showRawPayload = ! $this->showRawPayload;
    }

    public function render(): View
    {
        if ($this->conversation === null) {
            abort(404);
        }

        return view('orbit::livewire.message-timeline', [
            'conversation' => $this->conversation,
            'messages' => $this->conversation->messages ?? collect(),
        ]);
    }
}
