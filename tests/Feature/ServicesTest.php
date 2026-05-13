<?php

use Ashrafic\AiOrbit\Services\DataRetention;
use Ashrafic\AiOrbit\Services\ExportService;
use Ashrafic\AiOrbit\Services\PiiDetector;

test('PiiDetector detects email patterns', function () {
    $detector = new PiiDetector;
    $result = $detector->scan('Contact us at test@example.com for help.');

    expect($result['has_pii'])->toBeTrue();
    expect($result['detections'])->toHaveKey('email');
});

test('PiiDetector detects phone patterns', function () {
    $detector = new PiiDetector;
    $result = $detector->scan('Call 555-123-4567 for support.');

    expect($result['has_pii'])->toBeTrue();
    expect($result['detections'])->toHaveKey('phone');
});

test('PiiDetector detects SSN patterns', function () {
    $detector = new PiiDetector;
    $result = $detector->scan('SSN: 123-45-6789');

    expect($result['has_pii'])->toBeTrue();
    expect($result['detections'])->toHaveKey('ssn');
});

test('PiiDetector detects credit card patterns', function () {
    $detector = new PiiDetector;
    $result = $detector->scan('Card: 4111-1111-1111-1111');

    expect($result['has_pii'])->toBeTrue();
    expect($result['detections'])->toHaveKey('credit_card');
});

test('PiiDetector reports no PII on clean content', function () {
    $detector = new PiiDetector;
    $result = $detector->scan('This is a normal conversation about AI.');

    expect($result['has_pii'])->toBeFalse();
    expect($result['detections'])->toBeEmpty();
});

test('DataRetention dryRun returns deletable count', function () {
    $retention = new DataRetention;
    $result = $retention->dryRun(0); // 0 days = all conversations eligible

    expect($result)->toHaveKey('count');
    expect($result)->toHaveKey('conversations');
});

test('ExportService throws for non-existent conversation', function () {
    $export = app(ExportService::class);

    expect(fn () => $export->toPest('999999'))
        ->toThrow(InvalidArgumentException::class);
});
