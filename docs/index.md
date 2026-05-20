---
layout: home

hero:
  name: "AI Orbit"
  text: ""
  tagline: "The Intelligent Control Tower for the Laravel AI SDK"
  image:
    src: /orbit-logo.svg
    alt: Laravel AI Orbit
  actions:
    - theme: brand
      text: Get Started
      link: /getting-started/installation
    - theme: alt
      text: View Features
      link: /features/dashboard
    - theme: alt
      text: GitHub
      link: https://github.com/ashrafic/laravel-ai-orbit

features:
  - icon: 🔭
    title: Complete Observability
    details: Real-time dashboard with conversations, message timelines, execution traces, and token analytics. See everything your AI agents are doing at a glance.
  - icon: 🧪
    title: Agent Playground
    details: Interactive sandbox for testing any discovered agent with intelligent dependency resolution, live parameter overrides, and persistent multi-turn chat sessions.
  - icon: ⚖️
    title: Prompt Lab
    details: Compare up to 3 provider+model combinations side-by-side on the same prompt. Auto-tagged results show fastest, cheapest, and best-value responses.
  - icon: 💰
    title: Cost Control
    details: Editable pricing matrix, historical cost breakdowns by agent/model/provider, configurable budget alerts with queued notifications, and provider health monitoring.
  - icon: 🔒
    title: Security & Compliance
    details: Built-in PII detection scanner, configurable data retention policies with dry-run previews, and full access audit logs.
  - icon: 🛠️
    title: Developer Tools
    details: Export conversations to Pest tests, JSONL fine-tuning format, or CSV. Save and reuse prompts in the Prompt Library with full-text search and tags.
---

<style>
@media (max-width: 768px) {
  .orbit-hero-laravel { font-size: 1.3rem !important; }
  .VPHero .name { margin-top: 44px !important; }
}
@media (max-width: 480px) {
  .orbit-hero-laravel { font-size: 1rem !important; }
  .VPHero .name { margin-top: 36px !important; }
}
</style>

<div style="margin-top: 3rem; text-align: center;">

## Why Laravel AI Orbit?

**Laravel AI Orbit** is a standalone observability dashboard and developer playground for the official [Laravel AI SDK](https://github.com/laravel/ai). Think of it as **Telescope for your AI agents** — a polished, real-time window into everything your agents are doing, with powerful tools to test, compare, and optimize them.

Built for **Laravel 11+** and **PHP 8.2+**, Orbit installs in seconds, requires **zero frontend build steps**, and ships with a gorgeous glassmorphism UI in both dark and light modes.

```bash
composer require ashrafic/laravel-ai-orbit
```

</div>

<div style="margin-top: 3rem;">

## What You Get

| Capability | What It Means For You |
|:---|:---|
| **Real-Time Dashboard** | At-a-glance stats for conversations, messages, tokens, and agent breakdowns with configurable time periods |
| **Thread Explorer** | Searchable conversation list with advanced filters, bookmarks, and chat-style message timelines |
| **Agent Sandbox** | Test any agent interactively with auto-detected dependencies, live overrides, and multi-turn sessions |
| **Prompt Lab** | Side-by-side model comparison with auto-tagged winners — fastest, cheapest, most concise, best value |
| **Cost Analytics** | Historical breakdowns by agent, model, and provider with interactive charts |
| **Budget Alerts** | Configurable thresholds with queued email/Slack notifications that never block requests |
| **PII Detection** | Automatic scanning for emails, phones, SSNs, credit cards, and API keys in message payloads |
| **Data Retention** | Configurable cleanup policies with dry-run previews and automatic stale conversation purging |
| **Export Tools** | One-click export to Pest PHP tests, OpenAI JSONL fine-tuning format, or CSV |
| **Prompt Library** | Save, tag, and reuse prompts with full-text search and metadata |

</div>

<div style="margin-top: 3rem; text-align: center;">

### Zero Configuration. Zero Build Steps. Zero Hassle.

Orbit auto-discovers your agents, reads from the SDK's existing tables, and presents everything in a polished standalone dashboard. No webpack, no Vite, no NPM — just Composer and go.

<p style="margin-top: 2rem;">
  <a href="/laravel-ai-orbit/getting-started/installation" class="orbit-cta-btn">Get Started →</a>
</p>

</div>
