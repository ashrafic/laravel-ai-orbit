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
        $recentConversations = $this->hasTable('agent_conversations')
            ? $this->connection()->table('agent_conversations')
                ->orderByDesc('created_at')
                ->limit(10)
                ->get()
            : collect();

        return view('ai-orbit::livewire.audit-dashboard', [
            'recentConversations' => $recentConversations,
        ]);
    }
}
