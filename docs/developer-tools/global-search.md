# Global Search

Global Search lets you find conversations, messages, and content across the entire Orbit dataset. Search from any page using the search bar or programmatically via the API.

## Using Global Search

### From the UI

The search box is available in the Orbit navigation bar. Press `Cmd+K` (Mac) or `Ctrl+K` (Windows/Linux) to focus the search input.

Type your query and results appear instantly, showing:

- **Matching Messages** — Individual messages containing the search term
- **Conversation Context** — The conversation title and timestamp
- **Message Role** — Whether it's a user or assistant message

### Search Scope

Global Search covers:

- Conversation titles
- Message content (user and assistant)

It does not search:
- Tool call arguments
- Metadata JSON
- Prompt library content (use the Prompt Library search for that)

## Programmatic Access

```php
use Ashrafic\AiOrbit\Services\GlobalSearch;

$search = app(GlobalSearch::class);

// Search across all conversations and messages
$results = $search->search('invoice refund', perPage: 20);

// Iterate results
foreach ($results as $message) {
    $message->conversation_title;  // 'Support Chat #123'
    $message->role;                // 'user' or 'assistant'
    $message->content;             // 'I need an invoice refund...'
    $message->created_at;          // '2025-01-15 10:30:00'
}
```

### Pagination

Results are paginated using Laravel's `LengthAwarePaginator`:

```php
$results->total();       // Total matching records
$results->currentPage(); // Current page number
$results->lastPage();    // Last page number
$results->perPage();     // Records per page
```

## How It Works

### Query

Global Search performs a SQL `LIKE` query across both tables:

```sql
SELECT 
    agent_conversation_messages.id,
    agent_conversation_messages.conversation_id,
    agent_conversation_messages.role,
    agent_conversation_messages.content,
    agent_conversation_messages.created_at,
    agent_conversations.title as conversation_title
FROM agent_conversation_messages
JOIN agent_conversations ON agent_conversations.id = agent_conversation_messages.conversation_id
WHERE agent_conversation_messages.content LIKE '%search_term%'
   OR agent_conversations.title LIKE '%search_term%'
ORDER BY agent_conversation_messages.created_at DESC
```

### Safety

If the required tables don't exist, Global Search returns an empty paginator rather than throwing an error.

## Use Cases

### Finding Specific Conversations
Search for a customer's name or email to find all their interactions with your agents.

### Content Audit
Search for sensitive keywords (e.g., "password", "credit card") to identify potential data leaks.

### Debugging
Search for error messages or specific phrases to find conversations where an agent misbehaved.

### Training Data Collection
Search for high-quality responses to specific topics, then export those conversations for fine-tuning.

### Trend Analysis
Search for emerging topics in user messages to identify new feature requests or support themes.

## Performance

Global Search uses simple `LIKE` queries, which are fast for moderate datasets but may slow down with millions of records. For production deployments with high message volumes, consider:

1. **Database indexing** — Add a full-text index on the `content` column
2. **Search engines** — Integrate with Elasticsearch, Algolia, or Meilisearch
3. **Caching** — Cache frequent search results

### Adding an Index

```php
// In a migration
Schema::table('agent_conversation_messages', function (Blueprint $table) {
    $table->index('content');
});
```

## Customization

To extend Global Search with additional fields or filters, extend the `GlobalSearch` service:

```php
use Ashrafic\AiOrbit\Services\GlobalSearch;

class CustomGlobalSearch extends GlobalSearch
{
    public function search(string $query, int $perPage = 20): LengthAwarePaginator
    {
        // Add custom search logic
        return parent::search($query, $perPage);
    }
}
```

Bind it in your service provider:

```php
app()->singleton(GlobalSearch::class, CustomGlobalSearch::class);
```

## Best Practices

1. **Use specific terms** — "invoice refund" is better than "refund"
2. **Check conversation context** — The search result shows the conversation title; use it to verify relevance
3. **Combine with filters** — Use the Thread Explorer filters after finding a conversation via search
4. **Monitor performance** — If searches become slow, consider adding database indexes
