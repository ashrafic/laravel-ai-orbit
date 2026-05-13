<?php

namespace Ashrafic\AiOrbit\Notifications;

use Ashrafic\AiOrbit\Models\BudgetAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BudgetExceeded extends Notification implements ShouldQueue
{
    use Queueable;

    private BudgetAlert $alert;

    private float $currentSpend;

    public function __construct(BudgetAlert $alert, float $currentSpend)
    {
        $this->alert = $alert;
        $this->currentSpend = $currentSpend;
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
        $symbol = config('ai-orbit.currency_symbol', '$');

        return (new MailMessage)
            ->subject("Budget Alert: {$this->alert->period} threshold exceeded")
            ->line("Your {$this->alert->period} AI spending has reached {$symbol}{$this->currentSpend}.")
            ->line("This exceeds your configured threshold of {$symbol}{$this->alert->threshold_amount}.")
            ->action('View Orbit Dashboard', url(config('ai-orbit.path', 'ai-orbit').'/usage/dashboard'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'alert_id' => $this->alert->id,
            'period' => $this->alert->period,
            'threshold' => $this->alert->threshold_amount,
            'current_spend' => $this->currentSpend,
        ];
    }
}
