# Changelog

All notable changes to Laravel AI Orbit are documented in this file.

## [1.0.0] - 2025-01-15

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
- Export to CSV
- Prompt Library with tags, search, and metadata
- Global Search across conversations and messages
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
