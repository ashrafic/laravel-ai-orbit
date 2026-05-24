# Export Formats

Orbit can export any conversation to two formats: Pest PHP tests and OpenAI JSONL fine-tuning format. Export from the Message Timeline or programmatically via the API.

## Export from the UI

Open any conversation in the Message Timeline and click the export buttons:

### Pest Test Export

Generates a ready-to-run Pest PHP test case:

```php
<?php

namespace Tests\Feature\AI;

use Illuminate\Support\Facades\AI;

it('generates a response from SupportAgent', function () {
    AI::fake([
        'App\AI\Agents\SupportAgent' => 'Hello! How can I help you today?',
    ]);

    $response = AI::ask(new \App\AI\Agents\SupportAgent(), 'Test prompt');

    expect($response)->toBeString();
});
```

**Use this for:**
- Regression testing agent behavior
- CI/CD pipeline integration
- Documenting expected responses

### JSONL Export

Exports in OpenAI fine-tuning format:

```jsonl
{"messages":[{"role":"user","content":"Hello"},{"role":"assistant","content":"Hi there!"}]}
{"messages":[{"role":"user","content":"What's the weather?"},{"role":"assistant","content":"I don't have access to weather data."}]}
```

**Use this for:**
- Fine-tuning your own models
- Training data extraction
- Dataset preparation

## Programmatic Export

### Pest Export

```php
use Ashrafic\AiOrbit\Services\ExportService;

$export = app(ExportService::class);
$content = $export->toPest('conversation-id-123');

// Save to file
file_put_contents('tests/Feature/AI/conversation_test.php', $content);
```

### JSONL Export

```php
$content = $export->toJson('conversation-id-123');

// Save to file
file_put_contents('training_data.jsonl', $content);
```

## Configuration

### Pest Namespace

Customize the namespace for generated Pest tests:

```php
// config/ai-orbit.php
'export' => [
    'pest_namespace' => 'Tests\Feature\AI',
],
```

Or via `.env`:

```env
ORBIT_PEST_NAMESPACE="Tests\Feature\AI"
```

### JSON Format

The JSON export format is currently fixed to OpenAI's fine-tuning format:

```php
// config/ai-orbit.php
'export' => [
    'json_format' => 'openai',
],
```

## Bulk Export

Loop through conversation IDs for batch exports:

```php
$ids = ['conversation-1', 'conversation-2'];

foreach ($ids as $id) {
    $pest = $export->toPest($id);
    file_put_contents("tests/Feature/AI/{$id}_test.php", $pest);
}

foreach ($ids as $id) {
    $jsonl = $export->toJson($id);
    file_put_contents("training_data_{$id}.jsonl", $jsonl);
}
```

## Export Endpoints

The export controller provides HTTP endpoints:

| Method | Endpoint | Description |
|:---|:---|:---|
| POST | `/ai-orbit/export/pest/{id}` | Download Pest test file |
| POST | `/ai-orbit/export/json/{id}` | Download JSONL file |

Export endpoints return downloadable files with appropriate `Content-Disposition` headers.

## Best Practices

1. **Export before deleting** — Always export important conversations before purging
2. **Version control Pest tests** — Commit generated tests to track behavior changes
3. **Curate training data** — Not all conversations are suitable for fine-tuning; review before using
4. **Automate exports** — Schedule regular exports for compliance or analytics

## Customization

To customize export formats, extend the `ExportService`:

```php
use Ashrafic\AiOrbit\Services\ExportService;

class CustomExportService extends ExportService
{
    public function toPest(string $conversationId): string
    {
        $content = parent::toPest($conversationId);
        
        // Add custom assertions
        $content .= "\n    expect(\$response)->not->toBeEmpty();\n";
        
        return $content;
    }
}
```

Then bind it in your service provider:

```php
app()->singleton(ExportService::class, CustomExportService::class);
```
