# Export Formats

Orbit can export any conversation to three formats: Pest PHP tests, OpenAI JSONL fine-tuning format, and CSV. Export directly from the Message Timeline or programmatically via the API.

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

### CSV Export

Exports conversation metadata:

```csv
ID,Title,Agent,User,Created At,Tokens Input,Tokens Output
1,"Support Chat","App\AI\Agents\SupportAgent",7,"2025-01-15",156,89
```

**Use this for:**
- Spreadsheet analysis
- Cost reporting
- Data science workflows

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

### CSV Export

```php
$content = $export->toCsv(['conversation-id-123', 'conversation-id-456']);

// Save to file
file_put_contents('conversations.csv', $content);
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

Export multiple conversations at once:

```php
// Export multiple conversations to CSV
$content = $export->toCsv([
    'conversation-1',
    'conversation-2',
    'conversation-3',
]);
```

For Pest and JSONL, loop through conversations:

```php
$ids = ['conversation-1', 'conversation-2'];

foreach ($ids as $id) {
    $pest = $export->toPest($id);
    file_put_contents("tests/Feature/AI/{$id}_test.php", $pest);
}
```

## Export Endpoints

The export controller provides HTTP endpoints:

| Method | Endpoint | Description |
|:---|:---|:---|
| POST | `/ai-orbit/export/pest/{id}` | Download Pest test file |
| POST | `/ai-orbit/export/json/{id}` | Download JSONL file |

Note: CSV export is available programmatically but not exposed as a standalone endpoint (use the bulk action in the Thread Explorer).

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
