<?php

namespace Ashrafic\AiOrbit\Http\Livewire;

use Ashrafic\AiOrbit\Services\Concerns\UsesAiConnection;
use Ashrafic\AiOrbit\Services\DataRetention;
use Ashrafic\AiOrbit\Services\PiiDetector;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class AuditDashboard extends Component
{
    use UsesAiConnection;

    public string $scanContent = '';

    public ?array $piiResults = null;

    public ?array $dryRunResults = null;

    public ?int $purgedCount = null;

    public string $retentionDays;

    public function mount(): void
    {
        $this->retentionDays = (string) config('ai-orbit.audit.retention_days', 90);
    }

    public function scanPii(): void
    {
        if (trim($this->scanContent) === '') {
            return;
        }

        $detector = app(PiiDetector::class);
        $this->piiResults = $detector->scan($this->scanContent);
    }

    public function dryRun(): void
    {
        $retention = app(DataRetention::class);
        $this->dryRunResults = $retention->dryRun((int) $this->retentionDays);
        $this->purgedCount = null;
    }

    public function purge(): void
    {
        $retention = app(DataRetention::class);
        $this->purgedCount = $retention->purge((int) $this->retentionDays);
        $this->dryRunResults = null;
    }

    public function render(): View
    {
        if (! $this->hasTable('agent_conversations')) {
            return view('ai-orbit::livewire.audit-dashboard', [
                'recentConversations' => collect(),
            ]);
        }

        $query = $this->connection()->table('agent_conversations')
            ->select([
                'agent_conversations.id',
                'agent_conversations.created_at',
            ]);

        if ($this->hasTable('agent_conversation_messages')) {
            $query->selectRaw('COUNT(agent_conversation_messages.id) as messages_count');

            if ($this->hasColumn('agent_conversation_messages', 'agent')) {
                $query->addSelect(
                    $this->connection()->raw('MAX(agent_conversation_messages.agent) as agent_class')
                );
            }

            $query->leftJoin('agent_conversation_messages', 'agent_conversations.id', '=', 'agent_conversation_messages.conversation_id');
            $query->groupBy('agent_conversations.id');
        }

        $recentConversations = $query->orderByDesc('agent_conversations.created_at')
            ->limit(10)
            ->get();

        return view('ai-orbit::livewire.audit-dashboard', [
            'recentConversations' => $recentConversations,
        ]);
    }
}
