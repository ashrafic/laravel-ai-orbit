# Agent Health Score

The Agent Health Score provides a quantitative assessment of each agent's performance. Score agents by response quality, error rates, and token efficiency to identify agents that need attention.

## How Scoring Works

Each agent receives a score from 0 to 100 based on:

| Factor | Weight | Description |
|:---|:---|:---|
| **Error Rate** | High | Percentage of messages with errors or tool failures |
| **Total Requests** | Context | Volume of activity (more data = more reliable score) |
| **Average Tokens** | Context | Mean tokens per interaction |

### Score Calculation

```
score = max(0, 100 - (error_rate * 2))
```

For example:
- 0% error rate → 100 score
- 5% error rate → 90 score
- 15% error rate → 70 score
- 50% error rate → 0 score

### Status Levels

| Score | Status | Color |
|:---|:---|:---|
| 80–100 | Healthy | Green |
| 50–79 | Warning | Yellow |
| 0–49 | Critical | Red |

## Accessing Health Scores

Agent health scores are displayed in the Agent Inspector panel within the Playground. When you select an agent, its health score appears alongside metadata.

## Programmatic Access

```php
use Ashrafic\AiOrbit\Services\AgentHealthScorer;

$scorer = app(AgentHealthScorer::class);

// Score a single agent
$health = $scorer->score('App\AI\Agents\SupportAgent', period: '7d');
// Returns: score, total_requests, error_rate, avg_tokens, status

// Score all agents
$allHealth = $scorer->scoreAll(period: '7d');
// Returns collection sorted by score descending
```

### Period Selection

Analyze health over different time windows:

- `24h` — Last 24 hours (recent issues)
- `7d` — Last 7 days (short-term trends)
- `30d` — Last 30 days (long-term reliability)

## What Counts as an Error

Errors are detected from the `agent_conversation_messages` table:

1. **Tool failures** — Messages with `role = 'tool'`
2. **API errors** — Messages where `meta.error` is not null

### Error Rate Calculation

```
error_rate = (error_count / total_messages) * 100
```

## Interpreting Scores

### Score = 100
The agent has zero errors in the period. This is ideal but verify that the agent is actually being used (check `total_requests`).

### Score = 90–99
Excellent performance with minimal errors. Monitor for trends.

### Score = 70–89
Good performance but some errors. Investigate the error types:
- Are they tool failures? Fix the tools.
- Are they API errors? Check provider health.

### Score = 50–69
Warning level. The agent has a significant error rate. Review:
- Agent instructions
- Tool implementations
- Input validation
- Provider reliability

### Score = 0–49
Critical. The agent is failing frequently. Immediate action needed:
- Check the Traces for specific errors
- Review the agent's code
- Test in the Playground
- Consider disabling the agent

### Score = 100 with 0 Requests
This means the agent has no activity in the period. The score is technically perfect but meaningless. Check if the agent is actually being used.

## Use Cases

### Daily Monitoring
Check agent health scores each morning. Address any agents in Warning or Critical status.

### Pre-Release Validation
Before deploying a new agent, run it in the Playground and monitor its health score.

### Post-Incident Review
After an outage or error spike, check health scores to see which agents were affected.

### Capacity Planning
Agents with high request counts and good health scores are your workhorses. Ensure they have adequate resources.

### Team Dashboards
Integrate health scores into your team's monitoring dashboard for at-a-glance agent status.

## Customization

The health scoring algorithm is extensible. To customize scoring:

1. Extend the `AgentHealthScorer` class
2. Override the `score` method
3. Bind your custom class in a service provider

```php
use Ashrafic\AiOrbit\Services\AgentHealthScorer;

class CustomHealthScorer extends AgentHealthScorer
{
    public function score(string $agentClass, string $period = '7d'): array
    {
        $base = parent::score($agentClass, $period);
        
        // Add custom scoring logic
        $customScore = $this->calculateCustomMetric($agentClass);
        
        return array_merge($base, [
            'custom_metric' => $customScore,
        ]);
    }
}
```

## Best Practices

1. **Monitor daily** — Check health scores as part of your daily routine
2. **Investigate warnings** — Don't ignore yellow status; it often precedes red
3. **Compare periods** — A 7-day score of 95 dropping to 30-day 70 indicates a recent degradation
4. **Correlate with deployments** — If scores drop after a deployment, investigate the changes
5. **Use with Traces** — Health scores tell you *that* there's a problem; Traces tell you *why*
