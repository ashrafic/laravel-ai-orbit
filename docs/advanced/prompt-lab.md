# Prompt Lab

The Prompt Lab is a side-by-side model comparison tool. Send the same prompt to up to 3 different provider+model combinations and compare responses, latency, tokens, and cost — all in one view.

![Prompt Lab](/screenshots/prompt-lab.png)

## Access

Navigate to `/ai-orbit/prompt-lab`.

## Running a Comparison

### 1. Configure the Prompt

Enter your prompt details:

| Field | Description |
|:---|:---|
| **System Prompt** | Instructions for the AI (required) |
| **Instruction** | The actual prompt to send (required) |
| **Temperature** | Creativity control (0–2, default 1.0) |
| **Max Tokens** | Maximum response length |
| **Top P** | Nucleus sampling parameter (0–1) |
| **Context** | Optional JSON context for multi-turn simulation |

### 2. Select Model Slots

Choose up to 3 provider+model combinations:

```
Slot 1: openai → gpt-4
Slot 2: anthropic → claude-3-opus
Slot 3: openai → gpt-3.5-turbo
```

Each slot is configured independently. The number of slots is configurable (default: 3, max: 3).

### 3. Run Comparison

Click **"Run Comparison"**. Orbit sends the prompt to all configured slots simultaneously and displays results as they arrive.

## Results View

Each slot shows:

| Metric | Description |
|:---|:---|
| **Response** | The full AI-generated response |
| **Latency** | Time to complete in milliseconds |
| **Tokens** | Total tokens consumed (prompt + completion) |
| **Cost** | Estimated cost (requires pricing rules) |
| **Success** | Whether the request succeeded |
| **Error** | Error message if the request failed |

## Auto-Tagging

Orbit automatically tags the best performers:

| Tag | Criteria |
|:---|:---|
| **Fastest** | Lowest latency among successful responses |
| **Cheapest** | Lowest cost among successful responses |
| **Most Concise** | Fewest tokens among successful responses |
| **Best Value** | Both Fastest and Cheapest |

Tags appear as badges on each result card, making it easy to identify the winning model at a glance.

## Session History

Every comparison is saved as a session. Browse past sessions at `/ai-orbit/prompt-lab`.

Each session stores:
- Prompt and system prompt
- Slot configurations
- Results with latency, tokens, and cost
- Auto-generated tags
- Total cost and latency across all slots
- Status: `completed` or `partial` (if some slots failed)

### Revisiting Sessions

Click any past session to view the full results. Use this to:
- Compare results over time
- Share configurations with your team
- Document model performance benchmarks

## Save to Prompt Library

After running a comparison, save the prompt to the [Prompt Library](/developer-tools/prompt-library) for future reuse:

1. Click **"Save to Library"**
2. Enter a name
3. The prompt, system prompt, temperature, max tokens, top P, and context are all saved

## Load from Prompt Library

Load existing prompts into the Prompt Lab:

1. Browse recent prompts in the sidebar
2. Click any prompt to populate the form
3. Adjust model slots and run

## Graceful Failure Handling

If one model fails, the others continue. You'll see:
- Successful responses with full metrics
- Failed slots with error messages
- The session status shows `partial` instead of `completed`

This is especially useful when testing new or unstable providers.

## How It Works

### PromptLabService

The `PromptLabService` orchestrates comparisons:

```php
use Ashrafic\AiOrbit\Services\PromptLabService;

$service = app(PromptLabService::class);

// Run a comparison
$results = $service->runComparison(
    prompt: 'Explain quantum computing',
    slots: [
        ['provider' => 'openai', 'model' => 'gpt-4'],
        ['provider' => 'anthropic', 'model' => 'claude-3-opus'],
    ],
    systemPrompt: 'You are a helpful assistant.',
    temperature: 0.7,
);

// Auto-tag results
$tags = $service->autoTagResults($results);

// Save session
$session = $service->saveSession(
    prompt: 'Explain quantum computing',
    slots: [...],
    results: $results,
);
```

### OrbitPromptLabAgent

The `OrbitPromptLabAgent` is a lightweight agent implementation used specifically for the Prompt Lab:

```php
use Ashrafic\AiOrbit\Agent\OrbitPromptLabAgent;

$agent = new OrbitPromptLabAgent(
    systemPrompt: 'You are a helpful assistant.',
    model: 'gpt-4',
    provider: 'openai',
    labTemperature: 0.7,
    labMaxTokens: 500,
);

$response = $agent->prompt('Explain quantum computing');
```

### Timeout

Each slot has a configurable timeout (default: 120 seconds). If a slot exceeds the timeout, it's marked as failed with a timeout error.

## Configuration

Adjust Prompt Lab settings in `config/ai-orbit.php`:

```php
'prompt-lab' => [
    'max_slots' => 3,           // Maximum slots per comparison
    'timeout_seconds' => 120,   // Timeout per slot
],
```

Or via environment variables:

```env
ORBIT_PROMPT_LAB_MAX_SLOTS=3
ORBIT_PROMPT_LAB_TIMEOUT=120
```

## Use Cases

### Model Selection
Compare candidate models for a new feature before committing to one. Evaluate quality, speed, and cost.

### Prompt Engineering
Test prompt variations across multiple models to ensure consistent behavior.

### Provider Benchmarking
Measure latency and reliability of different providers for your specific use case.

### Cost Optimization
Find the cheapest model that meets your quality requirements. Use the "Best Value" tag to identify optimal choices.

### Regression Testing
After updating a prompt, run the same comparison to verify responses haven't degraded.

## Customization

Override the Prompt Lab views:

```bash
php artisan vendor:publish --tag=ai-orbit-views
```

Then edit:
- `resources/views/vendor/ai-orbit/prompt-lab/index.blade.php` — Session list
- `resources/views/vendor/ai-orbit/prompt-lab/compare.blade.php` — Comparison interface
- `resources/views/vendor/ai-orbit/prompt-lab/show.blade.php` — Session detail
