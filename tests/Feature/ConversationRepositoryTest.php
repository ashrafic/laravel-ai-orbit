<?php

use Ashraf\Orbit\Services\ConversationRepository;
use Illuminate\Pagination\LengthAwarePaginator;

it('can list conversations with pagination', function () {
    $repository = app(ConversationRepository::class);

    $result = $repository->list();

    expect($result)->toBeInstanceOf(LengthAwarePaginator::class);
});

it('handles missing columns gracefully', function () {
    $repository = app(ConversationRepository::class);

    // Should not throw when columns don't exist
    $result = $repository->list();

    expect($result->total())->toBe(0);
});

it('returns null for non-existent conversation', function () {
    $repository = app(ConversationRepository::class);

    $result = $repository->find('non-existent-id');

    expect($result)->toBeNull();
});
