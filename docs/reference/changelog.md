# Changelog

All notable changes to Laravel AI Orbit are documented in this file.

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
