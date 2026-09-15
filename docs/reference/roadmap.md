# Roadmap

Planned features and improvements for Laravel AI Orbit.

## Versioning Plan

The Laravel AI SDK is pre-1.0 and may continue to change its schema before reaching a stable version. Orbit's versioning plan reflects that:

- **Orbit 1.x (current)** — While the SDK is pre-1.0, Orbit ships additive changes only. SDK schema and API changes are absorbed internally in minor releases; nothing in Orbit 1.x will break.
- **Orbit 2.0** — Planned for the SDK's stable 1.0 era. It will remove the deprecated `user_id` column from `orbit_ai_runs` (deprecated in 1.3, still written for backward compatibility) and align Orbit's own schema with the SDK's stable participant model. The breaking change will be documented in the [upgrade guide](/getting-started/upgrading).

## Short Term

### Enhanced Analytics
- **Token usage forecasting** — Predict future costs based on historical trends
- **Cost anomaly detection** — Automatically flag unusual spending patterns
- **Custom date ranges** — Pick any date range for analytics

### Improved Playground
- **Conversation branching** — Fork a conversation to test variations
- **Prompt versioning** — Save and compare prompt iterations
- **Batch testing** — Run the same prompt against multiple inputs

### Expanded Exports
- **Markdown export** — Export conversations as readable markdown
- **PDF export** — Generate PDF reports for stakeholders
- **Webhook export** — Send conversation data to external systems

## Medium Term

### Multi-User Support
- **Role-based access control** — Define roles (viewer, editor, admin)
- **Team workspaces** — Separate data and configs per team
- **Shared bookmarks** — Share starred conversations with teammates

### Advanced Monitoring
- **Real-time alerts** — WebSocket-based live notifications
- **Custom dashboards** — Build your own dashboard layouts
- **Scheduled reports** — Email daily/weekly summaries

### Integration Ecosystem
- **Slack notifications** — Native Slack integration for alerts
- **Discord notifications** — Discord webhook support
- **PagerDuty integration** — Escalate critical issues
- **Filament panel plugin** — Alternative dashboard via Filament

## Long Term

### Enterprise Features
- **SSO/SAML support** — Enterprise authentication
- **Audit trails** — Comprehensive change logging
- **Data residency** — Control where data is stored
- **Compliance reporting** — GDPR, SOC2, HIPAA helpers

### AI-Powered Insights
- **Automatic conversation summarization** — AI-generated summaries
- **Sentiment analysis** — Track user sentiment over time
- **Topic clustering** — Group conversations by topic automatically
- **Anomaly detection** — AI-powered detection of unusual patterns

### Community
- **Public prompt marketplace** — Share and discover prompts
- **Agent templates** — Pre-built agent configurations
- **Plugin system** — Third-party extensions

## Contributing

Have an idea for the roadmap? Open an issue or pull request on [GitHub](https://github.com/ashrafic/laravel-ai-orbit).

## Version Support

| Version | Status | PHP | Laravel |
|:---|:---|:---|:---|
| 1.x | Active development | 8.2+ | 12+, 13+ |

## Release Schedule

- **Patch releases** — As needed for bug fixes
- **Minor releases** — Monthly feature releases
- **Major releases** — Annual, with breaking changes documented
