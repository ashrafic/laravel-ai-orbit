<?php

use Ashrafic\AiOrbit\Services\TokenAggregator;
use Illuminate\Support\Collection;

it('returns zero stats when no data exists', function () {
    $aggregator = app(TokenAggregator::class);

    $stats = $aggregator->periodStats();

    expect($stats)->toBeArray();
    expect($stats['total_conversations'])->toBe(0);
    expect($stats['total_messages'])->toBe(0);
    expect($stats['input_tokens'])->toBe(0);
    expect($stats['output_tokens'])->toBe(0);
});

it('returns empty agent breakdown when no data', function () {
    $aggregator = app(TokenAggregator::class);

    $breakdown = $aggregator->agentBreakdown();

    expect($breakdown)->toBeInstanceOf(Collection::class);
    expect($breakdown)->toHaveCount(0);
});
