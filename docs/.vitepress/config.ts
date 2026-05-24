import { defineConfig } from 'vitepress'

const mainSidebar = [
  {
    text: 'Getting Started',
    items: [
      { text: 'Installation', link: '/getting-started/installation' },
      { text: 'Configuration', link: '/getting-started/configuration' },
      { text: 'Authorization', link: '/getting-started/authorization' },
      { text: 'Upgrading', link: '/getting-started/upgrading' },
    ],
  },
  {
    text: 'Core Features',
    items: [
      { text: 'Dashboard', link: '/features/dashboard' },
      { text: 'Conversations', link: '/features/conversations' },
      { text: 'Runs', link: '/features/runs' },
      { text: 'Playground', link: '/features/playground' },
      { text: 'Traces', link: '/features/traces' },
    ],
  },
  {
    text: 'Usage & Analytics',
    items: [
      { text: 'Usage', link: '/usage/' },
      { text: 'Pricing Matrix', link: '/usage/pricing-matrix' },
      { text: 'Budget Alerts', link: '/usage/budget-alerts' },
      { text: 'Provider Health', link: '/usage/provider-health' },
    ],
  },
  {
    text: 'Advanced',
    items: [
      { text: 'Prompt Lab', link: '/advanced/prompt-lab' },
      { text: 'Audit & Compliance', link: '/advanced/audit' },
    ],
  },
  {
    text: 'Developer Tools',
    items: [
      { text: 'Prompt Library', link: '/developer-tools/prompt-library' },
      { text: 'Agent Health Score', link: '/developer-tools/agent-health' },
    ],
  },
  {
    text: 'Customization',
    items: [
      { text: 'Publishing Views', link: '/customization/publishing-views' },
      { text: 'Publishing Assets', link: '/customization/publishing-assets' },
      { text: 'Extending Orbit', link: '/customization/extending-orbit' },
    ],
  },
  {
    text: 'Reference',
    items: [
      { text: 'Config Options', link: '/reference/config' },
      { text: 'Routes', link: '/reference/routes' },
      { text: 'Export Formats', link: '/developer-tools/exports' },
      { text: 'Changelog', link: '/reference/changelog' },
      { text: 'Roadmap', link: '/reference/roadmap' },
    ],
  },
]

export default defineConfig({
  title: 'Laravel AI Orbit',
  titleTemplate: ':title — AI Orbit',
  description: 'Observability, management, and developer playground for the Laravel AI SDK.',
  lang: 'en-US',
  base: '/laravel-ai-orbit/',
  lastUpdated: true,
  cleanUrls: true,
  srcExclude: ['**/vendor/**', '**/node_modules/**', '**/.superpowers/**', '**/tests/**'],

  head: [
    ['link', { rel: 'icon', type: 'image/svg+xml', href: '/laravel-ai-orbit/favicon.svg' }],
    ['link', { rel: 'preconnect', href: 'https://fonts.googleapis.com' }],
    ['link', { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: '' }],
    ['link', { rel: 'stylesheet', href: 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Orbitron:wght@500;600;700;800;900&display=swap' }],
    ['meta', { property: 'og:type', content: 'website' }],
    ['meta', { property: 'og:title', content: 'Laravel AI Orbit' }],
    ['meta', { property: 'og:description', content: 'Observability, management, and developer playground for the Laravel AI SDK.' }],
    ['meta', { property: 'og:image', content: 'https://ashrafic.github.io/laravel-ai-orbit/og.png' }],
    ['meta', { property: 'twitter:card', content: 'summary_large_image' }],
    ['meta', { property: 'twitter:title', content: 'Laravel AI Orbit' }],
    ['meta', { property: 'twitter:description', content: 'Observability, management, and developer playground for the Laravel AI SDK.' }],
    ['meta', { property: 'twitter:image', content: 'https://ashrafic.github.io/laravel-ai-orbit/og.png' }],
  ],

  themeConfig: {
    logo: { src: '/orbit-logo.svg', width: 36, height: 36 },
    siteTitle: false,
    outline: 'deep',

    nav: [
      { text: 'Docs', link: '/getting-started/installation' },
      { text: 'Features', link: '/features/dashboard' },
      { 
        text: 'Usage', 
        items: [
          { text: 'Analytics', link: '/usage/' },
          { text: 'Pricing Matrix', link: '/usage/pricing-matrix' },
          { text: 'Budget Alerts', link: '/usage/budget-alerts' },
          { text: 'Provider Health', link: '/usage/provider-health' },
        ],
      },
      {
        text: 'Tools',
        items: [
          { text: 'Prompt Lab', link: '/advanced/prompt-lab' },
          { text: 'Prompt Library', link: '/developer-tools/prompt-library' },
          { text: 'Agent Health', link: '/developer-tools/agent-health' },
          { text: 'Audit & Compliance', link: '/advanced/audit' },
        ],
      },
      { text: 'Reference', link: '/reference/config' },
    ],

    sidebar: {
      '/getting-started/': mainSidebar,
      '/features/': mainSidebar,
      '/usage/': mainSidebar,
      '/advanced/': mainSidebar,
      '/developer-tools/': mainSidebar,
      '/customization/': mainSidebar,
      '/reference/': mainSidebar,
    },

    socialLinks: [
      { icon: 'github', link: 'https://github.com/ashrafic/laravel-ai-orbit' },
    ],

    editLink: {
      pattern: 'https://github.com/ashrafic/laravel-ai-orbit/edit/main/docs/:path',
      text: 'Edit this page on GitHub',
    },

    search: {
      provider: 'local',
    },

    footer: {
      message: 'Released under the MIT License.',
      copyright: 'Copyright © 2026 <a href="https://ashraficlabs.com">Ashrafic Labs</a>',
    },
  },
})
