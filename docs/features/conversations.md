# Conversations

The Conversations section is your window into every interaction between users and your AI agents. It combines a powerful thread explorer with a detailed message timeline.

## Thread Explorer

Access at `/ai-orbit/conversations`. The Thread Explorer lists all conversation threads with advanced filtering and search capabilities.

### Conversation List

Each row shows:

| Field | Description |
|:---|:---|
| **Title** | Conversation title (if set by the agent) |
| **Agent** | The agent class that handled the conversation |
| **Messages** | Total message count |
| **Input Tokens** | Cumulative prompt tokens |
| **Output Tokens** | Cumulative completion tokens |
| **Created** | Timestamp |
| **Actions** | View, bookmark, delete |

### Advanced Filters

Filter conversations using multiple criteria:

- **Search** — Full-text search on conversation titles
- **Date Range** — Today, Last 7 Days, Last 30 Days, or custom
- **Agent** — Filter by specific agent class
- **User ID** — Filter by the user who initiated the conversation
- **Token Range** — Min/max total token count
- **Cost Range** — Min/max estimated cost (requires pricing rules)
- **Latency Range** — Min/max average latency
- **Status** — Filter by conversation state
- **Bookmarked Only** — Show only starred conversations

### Sorting

Click any column header to sort. Click again to reverse the sort direction.

### Bookmarks

Star important conversations for quick access:

- Click the star icon to toggle a bookmark
- Filter to "Bookmarked Only" to see starred conversations
- Bookmarks are stored in the `orbit_bookmarks` table

### Deletion

Delete a conversation and all its messages with a single click. This permanently removes data from the SDK tables.

## Message Timeline

Click any conversation to open the Message Timeline at `/ai-orbit/conversations/{id}`.

### Chat-Style View

Messages are displayed in a chat-style interface with clear role indicators:

- **User** messages — right-aligned, distinct styling
- **Assistant** messages — left-aligned, primary styling
- **System** messages — subdued, informational styling
- **Tool Calls** — highlighted with expandable details
- **Tool Results** — nested under the corresponding tool call

### Raw JSON Inspector

Every message has a **"Raw"** toggle that reveals the complete payload:

```json
{
  "id": 1,
  "conversation_id": "abc-123",
  "role": "assistant",
  "content": "Hello! How can I help?",
  "agent": "App\\AI\\Agents\\SupportAgent",
  "tool_calls": null,
  "tool_results": null,
  "usage": {
    "prompt_tokens": 156,
    "completion_tokens": 89
  },
  "meta": {
    "provider": "openai",
    "model": "gpt-4",
    "latency_ms": 1240
  },
  "created_at": "2025-01-15T10:30:00Z"
}
```

The JSON is syntax-highlighted with color-coded keys, strings, numbers, and booleans for easy reading.

### Export from Timeline

From the message timeline, you can export the conversation:

- **Pest Test** — Generate a PHP Pest test case
- **JSONL** — Export in OpenAI fine-tuning format
- **CSV** — Export conversation metadata for spreadsheet analysis

### Bookmark from Timeline

Toggle the bookmark star directly from the message timeline view. The bookmark state syncs with the Thread Explorer.

## How It Works

### Data Source

The Conversations feature reads from the Laravel AI SDK tables:

- `agent_conversations` — Conversation metadata
- `agent_conversation_messages` — Individual messages

Orbit safely checks for table and column existence before querying, gracefully handling SDK schema variations.

### ConversationRepository

The `ConversationRepository` service powers both the Thread Explorer and Message Timeline:

```php
use Ashrafic\AiOrbit\Services\ConversationRepository;

$repository = app(ConversationRepository::class);

// List with filters
$conversations = $repository->list([
    'search' => 'invoice',
    'date_from' => now()->subDays(7),
    'agent' => 'App\AI\Agents\SupportAgent',
], perPage: 15);

// Find single conversation with messages
$conversation = $repository->find('abc-123');

// Get messages for a conversation
$messages = $repository->messages('abc-123');

// Delete conversation
$repository->delete('abc-123');
```

### Column Safety

Orbit checks for column existence before including them in queries:

- `agent` — Agent class name
- `tool_calls` — Tool call payloads
- `tool_results` — Tool execution results
- `usage` — Token usage statistics
- `attachments` — File attachments
- `meta` — Provider, model, latency, and error metadata

If a column doesn't exist, Orbit simply omits it from the query rather than failing.

## Use Cases

### Debugging Agent Behavior
Open a conversation to see the exact message flow, tool calls, and responses. Use the raw JSON inspector to verify metadata like model and latency.

### Customer Support Review
Search for conversations by user ID to review how your support agent handled specific customer inquiries.

### Cost Analysis
Filter by date range and sort by token count to identify the most expensive conversations and optimize agent prompts.

### Training Data Extraction
Export high-quality conversations to JSONL format for fine-tuning your own models.

## Customization

Override the views:

```bash
php artisan vendor:publish --tag=ai-orbit-views
```

Then edit:
- `resources/views/vendor/laravel-ai-orbit/conversations/index.blade.php` — Thread Explorer layout
- `resources/views/vendor/laravel-ai-orbit/conversations/show.blade.php` — Message Timeline layout
- `resources/views/vendor/laravel-ai-orbit/livewire/thread-explorer.blade.php` — Thread Explorer Livewire component
- `resources/views/vendor/laravel-ai-orbit/livewire/message-timeline.blade.php` — Message Timeline Livewire component
