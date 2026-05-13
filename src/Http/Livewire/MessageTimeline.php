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

    public function highlightJson(mixed $data): string
    {
        if (is_string($data)) {
            $data = json_decode($data, true) ?? $data;
        }

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return preg_replace_callback(
            '/("(\\\\u[a-zA-Z0-9]{4}|\\\\[^u]|[^\\\\"])*")(\s*:)?|\b(true|false|null)\b|-?\b\d+\.?\d*\b/',
            function ($matches) {
                if (isset($matches[1]) && $matches[1] !== '') {
                    if (isset($matches[3]) && $matches[3] !== '') {
                        return '<span class="text-purple-400 dark:text-purple-300">'.$matches[1].$matches[3].'</span>';
                    }

                    return '<span class="text-green-400 dark:text-green-300">'.$matches[1].'</span>';
                }

                if (isset($matches[4]) && $matches[4] !== '') {
                    return '<span class="text-blue-400 dark:text-blue-300">'.$matches[4].'</span>';
                }

                if (isset($matches[0]) && is_numeric($matches[0])) {
                    return '<span class="text-amber-400 dark:text-amber-300">'.$matches[0].'</span>';
                }

                return $matches[0];
            },
            $json
        );
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
