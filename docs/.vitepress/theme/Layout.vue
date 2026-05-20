<script setup>
import DefaultTheme from 'vitepress/theme'

const { Layout: BaseLayout } = DefaultTheme
</script>

<template>
  <BaseLayout>
    <template #nav-bar-title>
      <span class="orbit-nav-brand">
        <span class="orbit-nav-ai">AI</span>
        <span class="orbit-nav-orbit">Orbit</span>
      </span>
    </template>
  </BaseLayout>
</template>
