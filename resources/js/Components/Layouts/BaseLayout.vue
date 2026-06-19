<script setup>
import { computed, ref } from 'vue'
import { IconHome, IconClipboard, IconSettings, IconLogout, IconMenu, IconX } from '@tabler/icons-vue'
import { useI18n } from 'vue-i18n'
import LanguageDropdown from '../Common/LanguageDropdown.vue'

const props = defineProps({
  activePage: {
    type: String,
    default: 'dashboard',
  },
})

const { t, locale } = useI18n()

const isLanguageDropdownOpen = ref(false)
const isMobileMenuOpen = ref(false)

const currentLanguage = computed(() => (locale.value || 'pl').toUpperCase())

/** @param {string} lang */
function setLanguage(lang) {
  locale.value = lang
  localStorage.setItem('locale', lang)
  isLanguageDropdownOpen.value = false
}

const navItems = computed(() => [
  {
    key: 'dashboard',
    label: t('admin.layout.nav.dashboard'),
    href: '/admin',
    icon: IconHome,
  },
  {
    key: 'applications',
    label: t('admin.layout.nav.applications'),
    href: '/admin/applications',
    icon: IconClipboard,
  },
  {
    key: 'settings',
    label: t('admin.layout.nav.settings'),
    href: '#',
    icon: IconSettings,
  },
])
</script>

<template>
  <div class="flex flex-col bg-secondary min-h-screen text-text">
    <a
      href="#main-content"
      class="sr-only focus:not-sr-only focus:z-50 focus:absolute focus:bg-white focus:m-3 focus:px-3 focus:py-2 focus:rounded-md focus:font-medium focus:text-primary focus:text-sm"
    >
      {{ t('admin.layout.skipToContent') }}
    </a>

    <header class="bg-text shadow-md ring-1 ring-primary/10 ring-inset">
      <div class="flex justify-between items-center px-4 min-[900px]:px-6 py-4">
        <div class="flex items-center gap-3">
          <div class="shrink-0">
            <img
              src="/logo.svg"
              alt="Applikuj logo"
              class="brightness-0 invert rounded-lg w-auto h-10"
            >
          </div>
          <div>
            <div class="font-semibold text-white text-lg">{{ t('admin.layout.title') }}</div>
            <div class="text-white/70 text-xs">{{ t('admin.layout.subtitle') }}</div>
          </div>
        </div>

        <nav :aria-label="t('admin.layout.nav.ariaLabel')" class="hidden min-[900px]:flex items-center gap-1">
          <a
            v-for="item in navItems"
            :key="item.key"
            :href="item.href"
            :aria-current="item.key === props.activePage ? 'page' : undefined"
            class="flex items-center gap-2 px-4 py-2 rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/60 transition"
            :class="item.key === props.activePage
              ? 'bg-primary/80 text-white font-medium hover:bg-primary/30'
              : 'text-white/70 hover:bg-white/10 hover:text-white'"
          >
            <component
              :is="item.icon"
              class="w-5 h-5"
              :class="item.key === props.activePage ? 'text-secondary' : 'text-white/70'"
              aria-hidden="true"
            />
            {{ item.label }}
          </a>
        </nav>

        <div class="flex max-[899px]:justify-end items-center gap-3 max-[899px]:w-full">
          <button
            class="min-[900px]:hidden flex justify-center items-center hover:bg-white/10 rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/60 w-8 h-8 text-white/90 hover:text-white transition"
            :aria-label="isMobileMenuOpen ? 'Close menu' : 'Open menu'"
            :aria-expanded="isMobileMenuOpen"
            @click="isMobileMenuOpen = !isMobileMenuOpen"
          >
            <IconMenu v-if="!isMobileMenuOpen" class="w-5 h-5" aria-hidden="true" />
            <IconX v-else class="w-5 h-5" aria-hidden="true" />
          </button>
          <img
            class="rounded-full ring-2 ring-primary/10 w-10 h-10"
            src="https://www.gravatar.com/avatar?d=mp&s=48"
            alt="admin"
          >
          <div class="hidden min-[900px]:block">
            <p class="font-medium text-white text-sm">TestAdmin</p>
            <p class="text-white/70 text-xs">{{ t('admin.layout.userRole') }}</p>
          </div>
          <LanguageDropdown />
          <a
            class="flex justify-center items-center hover:bg-red-500/50 rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500 w-8 h-8 text-white/90 hover:text-secondary transition"
            href="#"
            :title="t('admin.layout.logout')"
          >
            <IconLogout class="w-4 h-4" aria-hidden="true" />
          </a>
        </div>
      </div>

      <nav
        v-if="isMobileMenuOpen"
        class="min-[900px]:hidden max-[899px]:block px-4 pb-4"
        :aria-label="t('admin.layout.nav.mobileAriaLabel')"
      >
        <ul class="flex flex-col gap-2">
          <li v-for="item in navItems" :key="`mobile-${item.key}`">
            <a
              :href="item.href"
              :aria-current="item.key === props.activePage ? 'page' : undefined"
              class="inline-flex items-center px-3 py-2 rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/60 w-full font-medium text-sm whitespace-nowrap transition"
              :class="item.key === props.activePage
                ? 'bg-primary/40 text-white'
                : 'bg-white/20 text-white hover:bg-white/30'"
              @click="isMobileMenuOpen = false"
            >
              <component :is="item.icon" class="mr-2 w-4 h-4" aria-hidden="true" />
              {{ item.label }}
            </a>
          </li>
        </ul>
      </nav>
    </header>

    <main id="main-content" class="relative flex flex-col flex-1 justify-start items-stretch p-4 min-[900px]:p-6 overflow-y-auto">
      <div class="absolute inset-0 overflow-hidden pointer-events-none" aria-hidden="true">
        <div class="absolute inset-0 bg-gradient-to-br from-secondary via-slate-100/90 to-primary/12" />
        <div class="absolute inset-0 admin-panel-dots" />
        <div class="-top-24 -right-16 absolute bg-primary/12 blur-3xl rounded-full w-96 h-96" />
        <div class="bottom-[-4rem] left-[8%] absolute bg-accent/45 blur-3xl rounded-full w-80 h-80" />
        <div class="top-[38%] right-[18%] absolute bg-primary/6 blur-2xl rounded-full w-56 h-56" />
        <div class="absolute inset-0 bg-gradient-to-t from-white/35 via-transparent to-primary/8" />
      </div>

      <div class="z-10 relative flex flex-col gap-8 mx-auto w-full max-w-7xl">
        <slot />
      </div>
    </main>
  </div>
</template>

<style scoped>
.admin-panel-dots {
  background-image: radial-gradient(rgba(4, 67, 137, 0.11) 1px, transparent 1px);
  background-size: 22px 22px;
  mask-image: radial-gradient(ellipse 85% 75% at 50% 45%, black 20%, transparent 100%);
}
</style>
