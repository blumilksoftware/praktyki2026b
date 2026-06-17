<script setup>
import { HomeIcon, ClipboardDocumentListIcon, Cog6ToothIcon, ArrowRightOnRectangleIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  activePage: {
    type: String,
    default: 'dashboard',
  },
})

const navItems = [
  {
    key: 'dashboard',
    label: 'Dashboard',
    href: '/admin',
    icon: HomeIcon,
  },
  {
    key: 'applications',
    label: 'Zgloszenia',
    href: '/admin/zgloszenia',
    icon: ClipboardDocumentListIcon,
  },
  {
    key: 'settings',
    label: 'Ustawienia',
    href: '#',
    icon: Cog6ToothIcon,
  },
]
</script>

<template>
  <div class="min-h-screen flex bg-secondary text-text">
    <a
      href="#main-content"
      class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:m-3 focus:rounded-md focus:bg-white focus:px-3 focus:py-2 focus:text-sm focus:font-medium focus:text-primary"
    >
      Przejdz do tresci
    </a>

    <aside
      class="hidden md:flex w-72 shrink-0 min-h-screen flex-col bg-white/95 shadow-md ring-1 ring-inset ring-primary/10 p-6"
    >
      <div class="mb-8 flex items-center gap-3">
        <div class="h-10 w-10 bg-primary text-white rounded-lg flex items-center justify-center font-semibold text-lg">
          A
        </div>
        <div>
          <div class="text-lg font-semibold text-text">Admin</div>
          <div class="text-xs text-addicional">Panel zarzadzania</div>
        </div>
      </div>

      <nav class="flex-1" aria-label="Nawigacja administratora">
        <ul class="space-y-1">
          <li v-for="item in navItems" :key="item.key">
            <a
              :href="item.href"
              :aria-current="item.key === props.activePage ? 'page' : undefined"
              class="flex items-center gap-3 rounded-xl px-4 py-2.5 transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/60"
              :class="item.key === props.activePage
                ? 'bg-primary/10 text-primary font-medium hover:bg-primary/15'
                : 'text-slate-600 hover:bg-white/40 hover:text-primary'"
            >
              <component
                :is="item.icon"
                class="h-5 w-5"
                :class="item.key === props.activePage ? 'text-primary' : 'text-slate-400'"
                aria-hidden="true"
              />
              {{ item.label }}
            </a>
          </li>
          <li>
            <a
              class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-red-600 transition hover:bg-red-50/60 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-300"
              href="#"
            >
              <ArrowRightOnRectangleIcon class="h-5 w-5 text-red-600" aria-hidden="true" />
              Wyloguj
            </a>
          </li>
        </ul>
      </nav>

      <div class="mt-6 pt-5 border-t border-primary/10 flex items-center gap-3">
        <img
          class="h-10 w-10 rounded-full ring-2 ring-primary/10"
          src="https://www.gravatar.com/avatar?d=mp&s=48"
          alt="admin"
        >
        <div>
          <p class="text-sm font-medium text-text">Admin Name</p>
          <p class="text-xs text-addicional">Administrator</p>
        </div>
      </div>
    </aside>

    <main id="main-content" class="relative flex-1 flex flex-col items-stretch justify-start overflow-y-auto p-4 md:p-6">
      <nav class="md:hidden relative z-20 mb-4 overflow-x-auto rounded-xl bg-white/70 p-2 ring-1 ring-black/5 backdrop-blur-sm" aria-label="Nawigacja administratora mobilna">
        <ul class="flex min-w-max w-full justify-center gap-2">
          <li v-for="item in navItems" :key="`mobile-${item.key}`">
            <a
              :href="item.href"
              :aria-current="item.key === props.activePage ? 'page' : undefined"
              class="inline-flex items-center rounded-lg px-3 py-2 text-sm font-medium transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/60"
              :class="item.key === props.activePage
                ? 'bg-primary text-white'
                : 'bg-white/70 text-slate-700 hover:bg-white'"
            >
              <component :is="item.icon" class="h-4 w-4 mr-2" aria-hidden="true" />
              {{ item.label }}
            </a>
          </li>
          <li>
            <a
              href="#"
              class="inline-flex items-center rounded-lg px-3 py-2 text-sm font-medium transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-300 bg-white/70 text-red-600 hover:bg-red-50/60"
            >
              <ArrowRightOnRectangleIcon class="h-4 w-4 mr-2 text-red-600" aria-hidden="true" />
              Wyloguj
            </a>
          </li>
        </ul>
      </nav>

      <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
        <div class="absolute inset-0 bg-gradient-to-br from-secondary via-slate-100/90 to-primary/12" />
        <div class="absolute inset-0 admin-panel-dots" />
        <div class="absolute -top-24 -right-16 h-96 w-96 rounded-full bg-primary/12 blur-3xl" />
        <div class="absolute bottom-[-4rem] left-[8%] h-80 w-80 rounded-full bg-accent/45 blur-3xl" />
        <div class="absolute top-[38%] right-[18%] h-56 w-56 rounded-full bg-primary/6 blur-2xl" />
        <div class="absolute inset-0 bg-gradient-to-t from-white/35 via-transparent to-primary/8" />
      </div>

      <div class="relative z-10 w-full max-w-7xl mx-auto flex flex-col gap-8">
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
