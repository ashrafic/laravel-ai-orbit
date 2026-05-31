<?php

namespace Ashrafic\AiOrbit\Agent;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;
use Stringable;

class OrbitPromptLabAgent implements Agent
{
    use Promptable;

    public function __construct(
        protected string $systemPrompt,
        protected string $model,
        protected string $provider,
        protected float $labTemperature = 1.0,
        protected ?int $labMaxTokens = null,
        protected ?array $labContext = null,
        protected float $labTopP = 1.0,
    ) {}

    public function instructions(): Stringable|string
    {
        $instructions = $this->systemPrompt;

        if ($this->labContext) {
            $contextText = (count($this->labContext) === 1 && array_key_exists('context', $this->labContext))
                ? $this->labContext['context']
                : json_encode($this->labContext);

            $instructions .= "\n\nPrevious conversation:\n".$contextText;
        }

        return $instructions;
    }

    public function model(): string
    {
        return $this->model;
    }

    public function provider(): string
    {
        return $this->provider;
    }

    public function temperature(): float
    {
        return $this->labTemperature;
    }

    public function maxTokens(): ?int
    {
        return $this->labMaxTokens;
    }

    public function topP(): float
    {
        return $this->labTopP;
    }
}
