<?php

namespace Ashrafic\AiOrbit\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProviderDown extends Notification implements ShouldQueue
{
    use Queueable;

    private string $provider;

    private float $errorRate;

    public function __construct(string $provider, float $errorRate)
    {
        $this->provider = $provider;
        $this->errorRate = $errorRate;
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Provider Alert: {$this->provider} is experiencing issues")
            ->line("The {$this->provider} AI provider has an error rate of {$this->errorRate}%.")
            ->line('You may want to switch to an alternative provider.')
            ->action('View Provider Health', url(config('ai-orbit.path', 'ai-orbit').'/usage/health'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'provider' => $this->provider,
            'error_rate' => $this->errorRate,
        ];
    }
}
