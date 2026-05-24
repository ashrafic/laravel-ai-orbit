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
    details: Export conversations to Pest tests and JSONL fine-tuning format. Save and reuse prompts in the Prompt Library with full-text search and tags.
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

<div style="margin-top: 3rem;">

## See It In Action

<p align="center">
  <img src="/screenshots/dashboard.png" alt="Dashboard Overview" style="border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,0.15); max-width: 100%;">
</p>
<p align="center"><strong>Dashboard</strong> — Real-time stats, token analytics, and agent breakdowns</p>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 2rem;">
  <div>
    <img src="/screenshots/playground.png" alt="Agent Playground" style="border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,0.15); width: 100%;">
    <p align="center"><strong>Playground</strong> — Discover and test agents</p>
  </div>
  <div>
    <img src="/screenshots/sandbox.png" alt="Agent Sandbox" style="border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,0.15); width: 100%;">
    <p align="center"><strong>Sandbox</strong> — Interactive chat with overrides</p>
  </div>
</div>

<p align="center" style="margin-top: 2rem;">
  <img src="/screenshots/prompt-lab.png" alt="Prompt Lab" style="border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,0.15); max-width: 100%;">
</p>
<p align="center"><strong>Prompt Lab</strong> — Side-by-side model comparison</p>

</div>

<div style="margin-top: 3rem; text-align: center;">

## Why Laravel AI Orbit?

**Laravel AI Orbit** is a standalone observability dashboard and developer playground for the official [Laravel AI SDK](https://github.com/laravel/ai). Think of it as **Telescope for your AI agents** — a polished, real-time window into everything your agents are doing, with powerful tools to test, compare, and optimize them.

Built for **Laravel 12+** and **PHP 8.3+**, Orbit installs in seconds, requires **zero frontend build steps**, and ships with a gorgeous glassmorphism UI in both dark and light modes.

```bash
composer require ashrafic/laravel-ai-orbit
php artisan ai-orbit:install
php artisan migrate
```

</div>

<div style="margin-top: 3rem;">

## What You Get

| Capability | What It Means For You |
|:---|:---|
| **Real-Time Dashboard** | At-a-glance stats for conversations, runs, messages, tokens, and agent breakdowns with configurable time periods |
| **Thread Explorer** | Searchable conversation list with advanced filters, bookmarks, and chat-style message timelines |
| **Run Observability** | Capture and inspect one-off SDK calls with search, filters, and full payload detail views |
| **Agent Sandbox** | Test any agent interactively with auto-detected dependencies, live overrides, and multi-turn sessions |
| **Prompt Lab** | Side-by-side model comparison with auto-tagged winners — fastest, cheapest, most concise, best value |
| **Cost Analytics** | Historical breakdowns by agent, model, provider, and operation with interactive charts |
| **Budget Alerts** | Configurable thresholds with per-alert recipients, provider-specific pricing, and queued notifications |
| **Provider Health** | Monitor success rates, latency percentiles, and errors from merged conversation + run data |
| **PII Detection** | Automatic scanning for emails, phones, SSNs, credit cards, and API keys in message payloads |
| **Data Retention** | Configurable cleanup policies with dry-run previews and automatic stale conversation purging |
| **Export Tools** | Export conversations to Pest PHP tests or OpenAI JSONL fine-tuning format |
| **Prompt Library** | Save, tag, and reuse prompts with full-text search and metadata |

</div>

<div style="margin-top: 3rem; text-align: center;">

### Zero Configuration. Zero Build Steps. Zero Hassle.

Orbit auto-discovers your agents, reads from the SDK's existing tables, and presents everything in a polished standalone dashboard. No webpack, no Vite, no NPM — just Composer and go.

<p style="margin-top: 2rem;">
  <a href="/laravel-ai-orbit/getting-started/installation" class="orbit-cta-btn">Get Started →</a>
</p>

</div>
