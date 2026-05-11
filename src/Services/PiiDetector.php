<?php

namespace Ashrafic\AiOrbit\Services;

use Illuminate\Support\Collection;

class PiiDetector
{
    private const PII_PATTERNS = [
        'email' => '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/',
        'phone' => '/(?:\+?1[-.\s]?)?\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}/',
        'ssn' => '/\b\d{3}-\d{2}-\d{4}\b/',
        'credit_card' => '/\b(?:\d{4}[- ]?){3}\d{4}\b/',
        'ip_address' => '/\b(?:\d{1,3}\.){3}\d{1,3}\b/',
    ];

    /**
     * Scan message content for PII patterns.
     *
     * @return array{has_pii: bool, detections: array<string, array<int, string>>}
     */
    public function scan(string $content): array
    {
        $detections = [];

        foreach (self::PII_PATTERNS as $type => $pattern) {
            $matches = [];
            preg_match_all($pattern, $content, $matches);

            if (! empty($matches[0])) {
                $detections[$type] = array_unique($matches[0]);
            }
        }

        return [
            'has_pii' => ! empty($detections),
            'detections' => $detections,
        ];
    }

    /**
     * Scan all messages in a conversation for PII.
     *
     * @param  Collection<int, object>  $messages
     * @return array{has_pii: bool, count: int, detections: array}
     */
    public function scanConversation($messages): array
    {
        $totalDetections = [];

        foreach ($messages as $msg) {
            if (! isset($msg->content)) {
                continue;
            }

            $result = $this->scan($msg->content);

            if ($result['has_pii']) {
                foreach ($result['detections'] as $type => $matches) {
                    if (! isset($totalDetections[$type])) {
                        $totalDetections[$type] = [];
                    }
                    $totalDetections[$type] = array_unique(array_merge(
                        $totalDetections[$type],
                        $matches
                    ));
                }
            }
        }

        $totalCount = collect($totalDetections)->flatten()->count();

        return [
            'has_pii' => $totalCount > 0,
            'count' => $totalCount,
            'detections' => $totalDetections,
        ];
    }
}
