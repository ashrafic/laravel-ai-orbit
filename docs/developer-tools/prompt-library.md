# Prompt Library

The Prompt Library is a centralized repository for saving, organizing, and reusing prompts. Tag prompts, search by content, and load them directly into the Prompt Lab or Playground.

## Access

Navigate to `/ai-orbit/prompts`.

## Prompt Cards

Each saved prompt displays:

| Field | Description |
|:---|:---|
| **Name** | Descriptive title |
| **Content** | The system prompt or full prompt text |
| **Instruction** | Optional user instruction template |
| **Tags** | Category labels for organization |
| **Metadata** | Temperature, max tokens, top P, context |
| **Updated** | Last modified timestamp |

## Creating a Prompt

Click **"New Prompt"** and fill in the form:

```
Name:        Customer Support Greeting
Content:     You are a friendly customer support agent for Acme Corp.
             Be concise, professional, and helpful.
Instruction: Greet the customer and ask how you can help.
Tags:        support, greeting, customer-service
Temperature: 0.7
Max Tokens:  200
Top P:       1.0
Context:     {"previous_interaction": "none"}
```

### Tags

Tags help you organize prompts. Add multiple tags separated by commas:

- `support`, `sales`, `onboarding`
- `gpt-4`, `claude`, `comparison`
- `high-temperature`, `low-temperature`

Click a tag to filter the library to prompts with that tag.

## Searching

The Prompt Library supports full-text search across:

- Prompt names
- Prompt content
- Instructions

Type in the search box and results update in real time.

## Loading Prompts

### Into Prompt Lab

1. Open the Prompt Lab
2. Browse recent prompts in the sidebar
3. Click any prompt to populate the form
4. Adjust model slots and run

### Into Playground

1. Open an Agent Sandbox
2. Use the "Load from Library" feature
3. Select a prompt to set the system prompt

## Editing Prompts

Click the edit icon on any prompt card to modify:

- Name and content
- Instruction
- Tags
- Metadata (temperature, max tokens, top P, context)

Changes are saved immediately.

## Deleting Prompts

Click the delete icon to remove a prompt. This action is permanent.

## Programmatic Access

```php
use Ashrafic\AiOrbit\Models\SavedPrompt;

// Create a prompt
$prompt = SavedPrompt::create([
    'name' => 'Support Greeting',
    'content' => 'You are a friendly support agent...',
    'instruction' => 'Greet the customer...',
    'meta' => [
        'temperature' => 0.7,
        'max_tokens' => 200,
        'top_p' => 1.0,
        'context' => ['previous' => 'none'],
    ],
    'tags' => ['support', 'greeting'],
]);

// Search prompts
$results = SavedPrompt::where('name', 'like', '%support%')
    ->orWhere('content', 'like', '%support%')
    ->get();

// Get prompts by tag
$supportPrompts = SavedPrompt::whereJsonContains('tags', 'support')->get();
```

## Use Cases

### Prompt Versioning
Save iterations of prompts as you refine them. Compare versions by loading them into the Prompt Lab.

### Team Sharing
Share prompt templates with your team. Everyone loads the same prompt from the library, ensuring consistency.

### A/B Testing
Save two variants of a prompt with different temperatures or instructions. Load both into the Prompt Lab to compare.

### Documentation
Document your best-performing prompts with tags and metadata. New team members can browse the library to understand your prompt patterns.

### Quick Start
Build a library of starter prompts for common tasks. Load them into the Playground to bootstrap new agent development.

## Customization

Override the Prompt Library view:

```bash
php artisan vendor:publish --tag=ai-orbit-views
```

Then edit `resources/views/vendor/laravel-ai-orbit/livewire/prompt-library.blade.php`.

## Best Practices

1. **Use descriptive names** — "Support Greeting" is better than "Prompt 1"
2. **Tag consistently** — Establish a tagging convention for your team
3. **Include instructions** — Separate system prompt from user instruction
4. **Save metadata** — Record temperature and max tokens for reproducibility
5. **Review regularly** — Delete outdated prompts to keep the library clean
