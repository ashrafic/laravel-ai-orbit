# Playground

The Agent Playground is an interactive sandbox where you can chat with any discovered agent in real time. Test prompts, debug behavior, and experiment with parameters — all without writing a single line of code.

![Agent Playground](/screenshots/playground.png)

## Agent List

Access at `/ai-orbit/playground`. The playground lists all agents discovered by scanning your configured directories.

### Discovery

Orbit scans the directories configured in `config/ai-orbit.php`:

```php
'agent_directories' => [
    'app/AI/Agents',
    'app/Ai/Agents',
],
```

Agents are discovered using PHP's tokenizer — no file loading or class instantiation happens during scanning, making discovery fast and safe.

### Agent Cards

Each discovered agent is displayed as a card showing:

- **Class Name** — Fully qualified class name
- **Tools** — Count of registered tools (if implements `HasTools`)
- **Structured Output** — Badge indicating schema support
- **Temperature** — Default temperature (from attribute or method)

Click any agent card to enter the sandbox.

## Agent Sandbox

Access at `/ai-orbit/playground/{agent}`. The sandbox provides a fully functional chat interface for the selected agent.

![Agent Sandbox](/screenshots/sandbox.png)

### Intelligent Dependency Resolution

One of Orbit's most powerful features is automatic constructor dependency resolution. When you open a sandbox, Orbit analyzes the agent's constructor and classifies each parameter:

| Strategy | What Happens | Example |
|:---|:---|:---|
| **Eloquent Picker** | Dropdown populated with recent records | `User $user`, `Order $order` |
| **Container** | Auto-resolved from Laravel's service container | `LoggerInterface $logger` |
| **Default** | Uses the parameter's default value | `int $limit = 10` |
| **Input** | Text field for manual entry | `string $apiKey` |
| **Unresolvable** | Flagged as unavailable for sandbox | Complex custom types |

### Eloquent Model Picker

When an agent requires an Eloquent model, Orbit:

1. Detects the model class
2. Identifies display columns (name, title, subject, email, label, slug)
3. Fetches recent records (configurable, default 20)
4. Presents a searchable dropdown

```php
class InvoiceAgent implements Agent
{
    public function __construct(
        public Invoice $invoice,  // Orbit shows a picker with recent invoices
        public User $user,         // Orbit shows a picker with recent users
    ) {}
}
```

### Live Parameter Overrides

Override agent behavior on the fly without touching code:

| Override | Description |
|:---|:---|
| **System Prompt** | Replace the agent's instructions entirely |
| **Model** | Switch to a different AI model |
| **Provider** | Switch to a different AI provider |
| **Temperature** | Adjust creativity/randomness (0–2) |
| **Max Tokens** | Limit response length |

Changes take effect immediately on the next message.

### Multi-Turn Chat

The sandbox supports persistent conversation sessions:

- Send multiple messages in a single session
- Conversation context is maintained automatically
- Tool calls and results are displayed inline
- Clear the session to start fresh

### Tool Call Visualization

When an agent uses tools, the sandbox displays:

1. **Tool Call** — The tool name and arguments (expandable JSON)
2. **Tool Result** — The execution result
3. **Final Response** — The agent's response after tool execution

### Session Persistence

Sandbox sessions are persisted in the session. If you refresh the page, your conversation and parameter inputs are restored.

Clear the session at any time with the **Clear** button.

## How It Works

### AgentIntrospector

The `AgentIntrospector` service analyzes agent constructors:

```php
use Ashrafic\AiOrbit\Services\AgentIntrospector;

$introspector = app(AgentIntrospector::class);

// Analyze constructor
$analysis = $introspector->analyzeConstructor(InvoiceAgent::class);
// Returns: resolvable, needs_input, params[]

// Resolve parameters from user inputs
$resolved = $introspector->resolveParams(InvoiceAgent::class, [
    'invoice' => 42,
    'user' => 7,
]);

// Get recent records for a model
$records = $introspector->getModelRecords(Invoice::class, limit: 20);
```

### SandboxRunner

The `SandboxRunner` executes the agent:

```php
use Ashrafic\AiOrbit\Services\SandboxRunner;

$runner = app(SandboxRunner::class);

$result = $runner->execute(
    agentClass: InvoiceAgent::class,
    prompt: 'Summarize this invoice',
    paramInputs: ['invoice' => 42, 'user' => 7],
    overrides: ['model' => 'gpt-4-turbo', 'provider' => 'openai'],
);

$result->content;          // The agent's response
$result->mode;             // 'full' (real execution)
$result->sdkConversationId; // The conversation ID
$result->toolCalls;        // Array of tool calls
$result->toolResults;      // Array of tool results
```

## Use Cases

### Rapid Prototyping
Test new prompts and parameters against your agents without deploying code changes.

### Debugging Production Issues
Reproduce a production conversation by selecting the same Eloquent model and sending similar prompts.

### Parameter Tuning
Experiment with temperature and max tokens to find the optimal settings for your use case.

### Model Comparison
Test the same prompt across different models using the live override feature.

### Training Demo
Show stakeholders how your agents work by interacting with them in a clean, isolated sandbox.

## Limitations

- Agents with unresolvable constructor dependencies cannot be simulated
- Complex nested dependencies may not be fully resolved
- The sandbox uses the real AI provider, so it consumes real API credits

## Customization

Override the sandbox views:

```bash
php artisan vendor:publish --tag=ai-orbit-views
```

Then edit:
- `resources/views/vendor/ai-orbit/playground/index.blade.php` — Agent list
- `resources/views/vendor/ai-orbit/playground/show.blade.php` — Sandbox layout
- `resources/views/vendor/ai-orbit/livewire/agent-sandbox.blade.php` — Sandbox component
- `resources/views/vendor/ai-orbit/livewire/agent-inspector.blade.php` — Inspector panel
