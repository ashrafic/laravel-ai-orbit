# Changelog

All notable changes to Laravel AI Orbit are documented in this file.

## [1.3.2]

### Fixed
- **Compiled CSS** — `dist/css/orbit.css` now includes the approval badge styles introduced in 1.3.1 (the badge previously rendered without its amber styling in the published artifact).

## [1.3.1]

### Added
- **Step Timeline** — Records the SDK's `StartingStep` and `StepCompleted` events (SDK 0.11+): run traces now include each generation step with its provider call wall time (`time_ms`).
- **Approval Visibility** — Records the SDK's `ToolApprovalRequested` and `ToolApprovalResolved` events: runs paused for human-in-the-loop tool approval are marked `pending_approval` (a new Run Explorer status filter), and paused messages show an approval badge in the Message Timeline via the SDK's `approval_state` column.

## [1.3.0]

### Changed
- **Laravel AI SDK Compatibility** — Now requires `laravel/ai` `^0.10|^0.11`. Reads the SDK's polymorphic conversation participants (`participant_type`/`participant_id`) and the `approval_state` message column introduced in SDK 0.10.
- **CI Matrix** — The test suite now runs against both the highest and lowest dependency sets, covering Laravel 12 and 13 as well as SDK 0.10 and 0.11.

### Added
- **Failure Observability** — Records the SDK's `AgentFailed`, `StepFailed`, and `ToolFailed` events (SDK 0.11+): terminal failures are marked as `failed` runs instead of lingering in `running` state, step failures are appended to run traces, and tool invocations record wall time (`time_ms`).
- **Participant Tracking** — `orbit_ai_runs` gains additive `participant_type` and `participant_id` columns (shipped migration with a guarded backfill from `user_id`). The recorder captures the SDK conversation participant's morph class and key, falling back to the authenticated principal. The legacy `user_id` column keeps being written and is deprecated — it will be removed in 2.0 (planned for the SDK's stable 1.0 era).

### Fixed
- **PHPStan** — Simplified failover error extraction in `AiRunRecorder` to match the SDK's widened `FailoverableException` contract.
- **Test Environment** — Tests now define an application encryption key, required by Livewire 4.4 component rendering under Laravel 13.

## [1.2.2]

### Changed
- **Laravel AI SDK Compatibility** — Composer constraint widened to include `laravel/ai` `^0.9`. No code changes or migrations are required.

### Fixed
- **PHPStan** — Simplified failover error extraction in `AiRunRecorder` to match the SDK's widened `FailoverableException` contract.

## [1.0.0]

### Added
- **AI Run Observability** — Orbit-owned run journal for Laravel AI SDK events via official SDK event listeners
- **Run Explorer** — Livewire component with search, sort, operation/status/provider filters, and pagination for one-off SDK calls
- **Run Detail View** — Inspect individual run metadata, tokens, cost, latency, and captured text payloads
- **Latency Percentiles** — P50, P95, P99 latency metrics in Provider Health
- **Dashboard Link Cards** — Quick navigation from dashboard to Usage, Pricing, Alerts, Health, and Prompts
- **Cost Column** — Estimated cost shown in dashboard agent breakdown table
- **Raw Token Display** — Token counts shown as raw values instead of M/K masked formats
- **Test Email Action** — Send test budget alert emails directly from the Alerts UI
- **Per-Alert Recipients** — Configure specific email addresses per budget alert
- **Missing Pricing Tracking** — Explicitly flag provider+model combinations without pricing rules
- **HTML & Plain-Text Budget Emails** — Polished email templates for budget exceeded notifications

### Changed
- **Unified Usage Page** — `/usage` now combines today's stats and full analytics
- **Merged Analytics** — TokenAggregator, CostCalculator, and Provider Health now merge data from both SDK conversations and `orbit_ai_runs`
- **Provider Health Data Source** — Always merges from `orbit_ai_runs` and `agent_conversation_messages` instead of either-or gating
- **Period Selector** — Added visible date range indicator ("Showing data since...") in Provider Health
- **Chart Palette** — 15 distinct non-brand colors for better visual differentiation
- **Navigation** — Renamed Analytics → Usage, restored Prompt Lab in sidebar

### Fixed
- **CostCalculator Property Names** — Corrected `total_input_tokens` → `input_tokens` for accurate cost calculations
- **Provider Health Period Filter** — Period selector now correctly filters both conversation and run data
- **Usage Index Grid** — Enforced 3-column grid layout on usage dashboard

## [0.0.3] - 2026-05-20

### Added
- Initial release of Laravel AI Orbit
- Dashboard with real-time stats and agent breakdowns
- Thread Explorer with advanced filters, sorting, and bookmarks
- Message Timeline with chat-style view and raw JSON inspector
- Execution Traces with per-step latency visualization
- Agent Playground with intelligent dependency resolution
- Live parameter overrides (model, provider, temperature, max tokens)
- Prompt Lab with side-by-side model comparison (up to 3 slots)
- Auto-tagging (Fastest, Cheapest, Most Concise, Best Value)
- Prompt Lab session history
- Cost Analytics with historical breakdowns by agent/model/provider
- Pricing Matrix with editable per-model token pricing
- Budget Alerts with configurable thresholds and queued notifications
- Provider Health monitoring with success rates and latency
- PII Detection scanner for emails, phones, SSNs, credit cards, IPs
- Data Retention management with dry-run previews
- Export to Pest PHP tests
- Export to OpenAI JSONL fine-tuning format
- Prompt Library with tags, search, and metadata
- Agent Health Scoring (0-100) based on error rates
- Full dark/light mode support
- Glassmorphism UI design
- Zero frontend build steps
- Publishable views, assets, and config
- Livewire 4 components throughout
- Comprehensive test suite with Pest PHP
- Larastan (PHPStan level 8) static analysis
- Laravel Pint code style enforcement

### Security
- Default local-only access via Gate
- Configurable authentication guard and middleware
- Access audit logging
- PII detection and scanning
- Non-blocking budget alert notifications via queues
