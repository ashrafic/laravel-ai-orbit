<?php

namespace Ashrafic\AiOrbit\Http\Livewire;

use Ashrafic\AiOrbit\Models\Bookmark;
use Ashrafic\AiOrbit\Services\ConversationRepository;
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

    public function toggleBookmark(): void
    {
        $existing = Bookmark::where('conversation_id', $this->conversationId)->first();

        if ($existing) {
            $existing->delete();
        } else {
            Bookmark::create(['conversation_id' => $this->conversationId]);
        }
    }

    public function isBookmarked(): bool
    {
        return Bookmark::where('conversation_id', $this->conversationId)->exists();
    }

    public function render(): View
    {
        if ($this->conversation === null) {
            $repository = app(ConversationRepository::class);
            $this->conversation = $repository->find($this->conversationId);

            if ($this->conversation === null) {
                abort(404);
            }
        }

        return view('ai-orbit::livewire.message-timeline', [
            'conversation' => $this->conversation,
            'messages' => $this->conversation->messages ?? collect(),
        ]);
    }
}
