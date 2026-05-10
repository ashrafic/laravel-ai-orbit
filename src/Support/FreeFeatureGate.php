<?php

namespace Ashraf\Orbit\Support;

use Ashraf\Orbit\Contracts\FeatureGate;

class FreeFeatureGate implements FeatureGate
{
    public function hasArena(): bool
    {
        return false;
    }

    public function hasStepDebugger(): bool
    {
        return false;
    }

    public function hasAdvancedAnalytics(): bool
    {
        return false;
    }

    public function hasExportTools(): bool
    {
        return false;
    }

    public function hasAdvancedFilters(): bool
    {
        return false;
    }

    public function hasAudit(): bool
    {
        return false;
    }
}
