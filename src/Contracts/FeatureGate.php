<?php

namespace Ashrafic\AiOrbit\Contracts;

interface FeatureGate
{
    /**
     * Check if the Arena (model comparison) feature is available.
     */
    public function hasArena(): bool;

    /**
     * Check if the step-through debugger feature is available.
     */
    public function hasStepDebugger(): bool;

    /**
     * Check if advanced analytics features are available.
     */
    public function hasAdvancedAnalytics(): bool;

    /**
     * Check if export tools are available.
     */
    public function hasExportTools(): bool;

    /**
     * Check if advanced conversation filters are available.
     */
    public function hasAdvancedFilters(): bool;

    /**
     * Check if the security audit feature is available.
     */
    public function hasAudit(): bool;
}
