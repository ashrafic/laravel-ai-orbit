<?php

use Ashrafic\AiOrbit\Support\FreeFeatureGate;

it('returns false for all pro features', function () {
    $gate = new FreeFeatureGate;

    expect($gate->hasArena())->toBeFalse();
    expect($gate->hasStepDebugger())->toBeFalse();
    expect($gate->hasAdvancedAnalytics())->toBeFalse();
    expect($gate->hasExportTools())->toBeFalse();
    expect($gate->hasAdvancedFilters())->toBeFalse();
    expect($gate->hasAudit())->toBeFalse();
});
