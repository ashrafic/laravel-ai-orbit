import DefaultTheme from 'vitepress/theme'
import Layout from './Layout.vue'
import './custom.css'

function injectNavBranding() {
  const titleEl = document.querySelector('.VPNavBarTitle .title')
  if (!titleEl || titleEl.querySelector('.orbit-nav-brand')) return

  const brand = document.createElement('span')
  brand.className = 'orbit-nav-brand'
  brand.innerHTML = '<span class="orbit-nav-ai">AI</span><span class="orbit-nav-orbit">Orbit</span>'
  titleEl.appendChild(brand)
}

function injectHeroBranding() {
  const nameEl = document.querySelector('.VPHero .name')
  if (!nameEl || nameEl.querySelector('.orbit-hero-ai')) return

  nameEl.textContent = ''
  nameEl.style.color = ''
  nameEl.style.background = ''
  nameEl.style.webkitBackgroundClip = ''
  nameEl.style.backgroundClip = ''
  nameEl.style.filter = ''

  const laravel = document.createElement('span')
  laravel.className = 'orbit-hero-laravel'
  laravel.textContent = 'Laravel'
  nameEl.appendChild(laravel)

  const ai = document.createElement('span')
  ai.className = 'orbit-hero-ai'
  ai.textContent = 'AI'
  nameEl.appendChild(ai)

  const orbit = document.createElement('span')
  orbit.className = 'orbit-hero-orbit'
  orbit.textContent = ' Orbit'
  nameEl.appendChild(orbit)
}

export default {
  extends: DefaultTheme,
  Layout,
  enhanceApp({ router }) {
    if (typeof window !== 'undefined') {
      injectNavBranding()
      injectHeroBranding()
      router.onAfterRouteChanged = () => {
        setTimeout(() => {
          injectNavBranding()
          injectHeroBranding()
        }, 50)
      }
    }
  },
}
