# Traces

Execution Traces provide a visual timeline of everything that happened during a conversation — every message, every tool call, every latency spike. It's the ultimate debugging tool for understanding agent behavior.

## Accessing Traces

Navigate to `/ai-orbit/traces/{conversation_id}` or click **"View Trace"** from any conversation in the Thread Explorer or Message Timeline.

## Trace View

The trace presents a chronological execution timeline:

### Timeline Components

| Component | Description |
|:---|:---|
| **Message Nodes** | Each message displayed as a node with role, timestamp, and token count |
| **Tool Call Nodes** | Expandable nodes showing tool name, arguments, and execution time |
| **Tool Result Nodes** | Nested under tool calls, showing the returned data |
| **Latency Bars** | Visual bars showing time spent on each step |
| **Error Indicators** | Red highlighting for messages with errors in metadata |

### Per-Step Details

Click any node to expand detailed information:

```
┌─ Message #3 ─────────────────────────────┐
│ Role: assistant                           │
│ Time: 2025-01-15 10:30:45                 │
│ Latency: 1,240ms                          │
│ Tokens: 156 in / 89 out                   │
│ Provider: openai                          │
│ Model: gpt-4                              │
│                                           │
│ Content:                                  │
│ "Based on the invoice data..."            │
│                                           │
│ Tool Calls:                               │
│ ├─ get_invoice_total(args: {...})         │
│ └─ Result: {"total": 149.99}              │
└───────────────────────────────────────────┘
```

### Expandable Tool Call Details

Tool calls are displayed with:

- **Tool Name** — The resolved tool name
- **Arguments** — Pretty-printed JSON of the arguments passed
- **Result** — The tool execution result
- **Execution Time** — How long the tool took to execute

## How It Works

### Data Source

Traces are built from the `agent_conversation_messages` table, reading:

- `role` — user, assistant, system, tool
- `content` — Message content
- `tool_calls` — Tool call payloads
- `tool_results` — Tool execution results
- `usage` — Token statistics
- `meta` — Provider, model, latency, errors
- `created_at` — Timestamps for sequencing

### ConversationRepository

The trace controller uses the `ConversationRepository` to fetch the conversation with all messages:

```php
$conversation = $repository->find($id);
$messages = $conversation->messages;
```

Messages are ordered chronologically by `created_at` ascending.

### Latency Calculation

If the `meta` column contains `latency_ms`, it's displayed directly. Otherwise, latency is inferred from the time difference between consecutive messages.

### Error Detection

Messages are flagged as errors if:
- The `meta` column contains an `error` key
- The role is `tool` (indicating a tool execution)

## Use Cases

### Debugging Slow Responses
Look for latency spikes in the timeline. If a specific tool call is slow, optimize the tool implementation or add caching.

### Understanding Tool Usage
See exactly which tools an agent calls, in what order, and with what arguments. Verify that tools are being used correctly.

### Reproduction
Use the trace to reproduce a conversation in the Playground. Copy the user messages and tool results to recreate the exact scenario.

### Performance Optimization
Identify redundant tool calls or excessive token usage. Adjust the agent's instructions to reduce unnecessary tool usage.

### Error Investigation
When an agent produces unexpected output, check the trace for error indicators and tool call failures.

## Customization

Override the trace view:

```bash
php artisan vendor:publish --tag=ai-orbit-views
```

Then edit `resources/views/vendor/laravel-ai-orbit/traces/show.blade.php`.

## Comparison with Conversations

| Feature | Conversations | Traces |
|:---|:---|:---|
| **Primary Use** | Browse and search | Debug and analyze |
| **View Style** | Chat-style messages | Execution timeline |
| **Tool Details** | Inline expandable | Detailed nodes with latency |
| **Latency** | Not shown | Visual bars per step |
| **Error Focus** | Subtle indicators | Prominent red highlighting |
| **Best For** | Finding conversations | Understanding behavior |

Use Conversations for finding and reviewing chats. Use Traces for deep debugging and performance analysis.
