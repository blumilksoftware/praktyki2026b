<script setup>
import { computed } from 'vue'
import { IconHome, IconClipboard, IconSettings, IconLogout, IconLanguage } from '@tabler/icons-vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  activePage: {
    type: String,
    default: 'dashboard',
  },
})

const { t, locale } = useI18n()

function toggleLanguage() {
  locale.value = locale.value === 'pl' ? 'en' : 'pl'
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
  <div class="flex bg-secondary min-h-screen text-text">
    <a
      href="#main-content"
      class="sr-only focus:not-sr-only focus:z-50 focus:absolute focus:bg-white focus:m-3 focus:px-3 focus:py-2 focus:rounded-md focus:font-medium focus:text-primary focus:text-sm"
    >
      {{ t('admin.layout.skipToContent') }}
    </a>

    <aside
      class="hidden md:flex flex-col bg-white/95 shadow-md p-6 ring-1 ring-primary/10 ring-inset w-72 min-h-screen shrink-0"
    >
      <div class="flex items-center gap-3 mb-8">
        <div class="flex justify-center items-center bg-primary rounded-lg w-10 h-10 font-semibold text-white text-lg">
          A
        </div>
        <div>
          <div class="font-semibold text-text text-lg">{{ t('admin.layout.title') }}</div>
          <div class="text-addicional text-xs">{{ t('admin.layout.subtitle') }}</div>
        </div>
      </div>

      <nav class="flex-1" :aria-label="t('admin.layout.nav.ariaLabel')">
        <ul class="space-y-1">
          <li v-for="item in navItems" :key="item.key">
            <a
              :href="item.href"
              :aria-current="item.key === props.activePage ? 'page' : undefined"
              class="flex items-center gap-3 px-4 py-2.5 rounded-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/60 transition"
              :class="item.key === props.activePage
                ? 'bg-primary/10 text-primary font-medium hover:bg-primary/15'
                : 'text-slate-600 hover:bg-white/40 hover:text-primary'"
            >
              <component
                :is="item.icon"
                class="w-5 h-5"
                :class="item.key === props.activePage ? 'text-primary' : 'text-slate-600'"
                aria-hidden="true"
              />
              {{ item.label }}
            </a>
          </li>
        </ul>
      </nav>

      <div class="mt-6 pt-5 border-primary/10 border-t">
        <div class="flex items-center gap-3">
          <img
            class="rounded-full ring-2 ring-primary/10 w-10 h-10"
            src="https://www.gravatar.com/avatar?d=mp&s=48"
            alt="admin"
          >
          <div class="flex-1">
            <p class="font-medium text-text text-sm">TestAdmin</p>
            <p class="text-addicional text-xs">{{ t('admin.layout.userRole') }}</p>
          </div>
          <button
            class="flex justify-center items-center hover:bg-slate-100 rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-300 w-8 h-8 text-slate-600 hover:text-primary transition"
            :title="locale === 'pl' ? t('admin.layout.languageSwitch.toEnglish') : t('admin.layout.languageSwitch.toPolish')"
            @click="toggleLanguage"
          >
            <IconLanguage class="w-4 h-4" aria-hidden="true" />
          </button>
          <a
            class="flex justify-center items-center hover:bg-red-100 rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-300 w-8 h-8 text-slate-600 hover:text-red-600 transition"
            href="#"
            :title="t('admin.layout.logout')"
          >
            <IconLogout class="w-4 h-4" aria-hidden="true" />
          </a>
        </div>
      </div>
    </aside>

    <main id="main-content" class="relative flex flex-col flex-1 justify-start items-stretch p-4 md:p-6 overflow-y-auto">
      <nav class="md:hidden z-20 relative bg-white/70 backdrop-blur-sm mb-4 p-2 rounded-xl ring-1 ring-black/5 overflow-x-auto" :aria-label="t('admin.layout.nav.mobileAriaLabel')">
        <ul class="flex justify-center gap-2 w-full min-w-max">
          <li v-for="item in navItems" :key="`mobile-${item.key}`">
            <a
              :href="item.href"
              :aria-current="item.key === props.activePage ? 'page' : undefined"
              class="inline-flex items-center px-3 py-2 rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/60 font-medium text-sm transition"
              :class="item.key === props.activePage
                ? 'bg-primary text-white'
                : 'bg-white/70 text-slate-700 hover:bg-white'"
            >
              <component :is="item.icon" class="mr-2 w-4 h-4" aria-hidden="true" />
              {{ item.label }}
            </a>
          </li>
          <li>
            <button
              class="inline-flex items-center bg-white/70 hover:bg-slate-100 px-3 py-2 rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-300 font-medium text-slate-600 text-sm transition"
              :title="locale === 'pl' ? t('admin.layout.languageSwitch.toEnglish') : t('admin.layout.languageSwitch.toPolish')"
              @click="toggleLanguage"
            >
              <IconLanguage class="mr-2 w-4 h-4" aria-hidden="true" />
              {{ locale === 'pl' ? 'EN' : 'PL' }}
            </button>
          </li>
          <li>
            <a
              href="#"
              class="inline-flex items-center bg-white/70 hover:bg-red-50/60 px-3 py-2 rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-300 font-medium text-red-600 text-sm transition"
            >
              <IconLogout class="mr-2 w-4 h-4 text-red-600" aria-hidden="true" />
              {{ t('admin.layout.logout') }}
            </a>
          </li>
        </ul>
      </nav>

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
