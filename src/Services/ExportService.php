<?php

namespace Ashrafic\AiOrbit\Services;

class ExportService
{
    private ConversationRepository $repository;

    public function __construct(ConversationRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Generate a Pest test file from a conversation.
     */
    public function toPest(string $conversationId): string
    {
        $conversation = $this->repository->find($conversationId);

        if (! $conversation) {
            throw new \InvalidArgumentException("Conversation [{$conversationId}] not found.");
        }

        $messages = $conversation->messages ?? collect();
        $namespace = config('ai-orbit.export.pest_namespace', 'Tests\\Feature\\AI');
        $agentClass = $messages->first()?->agent ?? 'UnknownAgent';
        $shortName = class_basename($agentClass);

        $pest = "<?php\n\n";
        $pest .= "namespace {$namespace};\n\n";
        $pest .= "use Illuminate\\Support\\Facades\\AI;\n\n";
        $pest .= "it('generates a response from {$shortName}', function () {\n";
        $pest .= "    AI::fake([\n";

        foreach ($messages as $msg) {
            if ($msg->role === 'assistant' && isset($msg->content)) {
                $escaped = addcslashes($msg->content, "'");
                $pest .= "        '{$agentClass}' => '{$escaped}',\n";
            }
        }

        $pest .= "    ]);\n\n";
        $pest .= "    \$response = AI::ask(new \\{$agentClass}(), 'Test prompt');\n\n";
        $pest .= "    expect(\$response)->toBeString();\n";
        $pest .= "});\n";

        return $pest;
    }

    /**
     * Generate an OpenAI-compatible JSONL file from a conversation.
     */
    public function toJson(string $conversationId): string
    {
        $conversation = $this->repository->find($conversationId);

        if (! $conversation) {
            throw new \InvalidArgumentException("Conversation [{$conversationId}] not found.");
        }

        $messages = $conversation->messages ?? collect();
        $lines = [];

        foreach ($messages as $msg) {
            if (! isset($msg->role, $msg->content)) {
                continue;
            }

            $entry = [
                'messages' => [
                    ['role' => $msg->role, 'content' => $msg->content],
                ],
            ];

            $lines[] = json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return implode("\n", $lines);
    }

    /**
     * Generate a CSV export from conversations.
     *
     * @param  array<int>  $conversationIds
     */
    public function toCsv(array $conversationIds): string
    {
        $rows = [];
        $rows[] = implode(',', ['ID', 'Title', 'Agent', 'User', 'Created At', 'Tokens Input', 'Tokens Output']);

        foreach ($conversationIds as $id) {
            $conv = $this->repository->find((string) $id);

            if (! $conv) {
                continue;
            }

            $rows[] = implode(',', [
                $conv->id ?? '',
                '"'.str_replace('"', '""', $conv->title ?? '').'"',
                $conv->agent_class ?? '',
                $conv->user_id ?? '',
                $conv->created_at ?? '',
                $conv->total_input_tokens ?? 0,
                $conv->total_output_tokens ?? 0,
            ]);
        }

        return implode("\n", $rows);
    }
}
