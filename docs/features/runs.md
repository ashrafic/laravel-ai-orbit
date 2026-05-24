# Run Observability

Orbit's Run Observability captures one-off AI operations — prompts, embeddings, image generations, and any other non-conversation SDK calls — so nothing happens invisibly. While the Conversations section tracks multi-turn chat threads, the Run Explorer is purpose-built for inspecting individual SDK invocations.

## Access

Navigate to `/ai-orbit/runs` for the Run Explorer, or `/ai-orbit/runs/{id}` for a specific run's detail view.

## How It Works

When SDK Observability is enabled, Orbit listens to Laravel AI SDK events and persists run metadata to the `orbit_ai_runs` table. This happens automatically — no code changes required in your application.

### What Gets Captured

Each run record includes:

| Field | Description |
|:---|:---|
| **Operation** | The SDK operation name (e.g., `chat`, `embed`) |
| **Provider** | The AI provider (e.g., `openai`, `anthropic`) |
| **Model** | The model identifier (e.g., `gpt-4`, `claude-3-opus`) |
| **Status** | `success`, `failed`, or `streaming` |
| **Input Tokens** | Prompt tokens consumed |
| **Output Tokens** | Completion tokens generated |
| **Cost** | Calculated cost using the Pricing Matrix |
| **Latency** | Response time in milliseconds |
| **Prompt** | The prompt text (if text capture is enabled) |
| **Response** | The response text (if text capture is enabled) |
| **Conversation Link** | Associated SDK conversation ID, if the run belongs to a thread |
| **Created At** | Timestamp of the SDK event |

### Configuration

Control observability via `config/ai-orbit.php`:

```php
'observability' => [
    'enabled' => env('AI_ORBIT_OBSERVABILITY_ENABLED', true),
    'store_runs' => env('AI_ORBIT_STORE_RUNS', true),
    'capture_text_payloads' => env('AI_ORBIT_CAPTURE_TEXT_PAYLOADS', true),
    'max_payload_length' => (int) env('AI_ORBIT_MAX_PAYLOAD_LENGTH', 10000),
    'excluded_operations' => [],
],
```

| Option | Description |
|:---|:---|
| `enabled` | Master toggle for SDK event listening |
| `store_runs` | Persist runs to `orbit_ai_runs`. Disable to keep event-driven budget checks without storing data |
| `capture_text_payloads` | Store prompt/response text in the database |
| `max_payload_length` | Truncate text payloads to this character limit |
| `excluded_operations` | Array of operation names to ignore (e.g., `['embed']`) |

Environment variables:

```env
AI_ORBIT_OBSERVABILITY_ENABLED=true
AI_ORBIT_STORE_RUNS=true
AI_ORBIT_CAPTURE_TEXT_PAYLOADS=true
AI_ORBIT_MAX_PAYLOAD_LENGTH=10000
```

## Run Explorer

The Run Explorer is a Livewire component that replaces server-side filtering with real-time search, sort, and pagination.

### Search & Filters

| Filter | Description |
|:---|:---|
| **Search** | Full-text search across operation names and text payloads |
| **Operation** | Filter by specific operation type (`chat`, `embed`, etc.) |
| **Status** | `success`, `failed`, or `streaming` |
| **Provider** | Filter by AI provider |
| **Conversation** | Show only runs linked to a specific conversation |
| **Sort** | Sort by date, latency, tokens, or cost |

### Pagination

Results are paginated with Livewire, so filtering is instant without full page reloads.

## Run Detail View

Click any run to open its detail page. The detail view shows:

- Complete run metadata (provider, model, status, latency)
- Token breakdown and calculated cost
- Full prompt and response text (if captured)
- Link to the associated conversation, if any
- Raw JSON inspector for the complete run payload

## Data Sources

Orbit merges analytics from two sources for complete coverage:

1. **`agent_conversation_messages`** — Multi-turn conversation data (primary source)
2. **`orbit_ai_runs`** — One-off SDK calls where `conversation_id` is `NULL`

Usage dashboards, budget monitoring, and provider health all merge data from both tables automatically.

## Programmatic Access

```php
use Ashrafic\AiOrbit\Services\AiRunRepository;
use Ashrafic\AiOrbit\Models\AiRun;

$repository = app(AiRunRepository::class);

// List runs with filters
$runs = $repository->list([
    'search' => 'invoice',
    'operation' => 'chat',
    'status' => 'success',
    'provider' => 'openai',
], perPage: 15);

// Find a specific run
$run = $repository->find('abc-123');

// Direct model access
$recentRuns = AiRun::recent()->limit(10)->get();
$failedRuns = AiRun::failed()->count();
```

### AiRunRecorder

For advanced use cases, you can manually record runs:

```php
use Ashrafic\AiOrbit\Services\AiRunRecorder;

$recorder = app(AiRunRecorder::class);

$recorder->record([
    'operation' => 'custom-analysis',
    'provider' => 'openai',
    'model' => 'gpt-4',
    'status' => 'success',
    'input_tokens' => 156,
    'output_tokens' => 89,
    'latency_ms' => 1240,
]);
```

## Use Cases

### Debugging One-Off Prompts
Search for a specific operation to see exactly what prompt was sent and what response came back — useful when agents make standalone API calls outside of conversations.

### Cost Attribution
Filter by operation type to understand which one-off calls (embeddings, image generation, etc.) are driving costs alongside conversation-based usage.

### Failure Investigation
Filter by `failed` status to find errors in SDK calls that don't belong to any conversation thread.

### Compliance Auditing
With text capture enabled, the Run Explorer provides a complete audit trail of all AI operations, including standalone calls that wouldn't appear in conversation logs.

## Customization

Override the Run Explorer views:

```bash
php artisan vendor:publish --tag=ai-orbit-views
```

Then edit:
- `resources/views/vendor/ai-orbit/runs/index.blade.php` — Run Explorer layout
- `resources/views/vendor/ai-orbit/runs/show.blade.php` — Run detail layout
- `resources/views/vendor/ai-orbit/livewire/run-explorer.blade.php` — Livewire filter component
