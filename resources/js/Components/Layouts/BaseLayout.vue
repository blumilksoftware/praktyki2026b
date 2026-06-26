<script setup>
import { computed, ref } from 'vue'
import { IconSettings, IconLogout, IconMenu, IconX, IconUser, IconLanguage, IconChevronDown } from '@tabler/icons-vue'
import { useI18n } from 'vue-i18n'
import LanguageDropdown from '../Common/LanguageDropdown.vue'

const props = defineProps({
  activePage: { type: String, default: '' },
  navItems: { type: Array, default: () => [] },
})

const { t, locale } = useI18n()

const isMobileMenuOpen = ref(false)
const isDesktopMenuOpen = ref(false)
const isDesktopLanguageOpen = ref(false)

const currentLanguage = computed(() => (locale.value || 'pl').toUpperCase())

/** @param {string} lang */
function setLanguage(lang) {
  locale.value = lang
  localStorage.setItem('locale', lang)
}

const navItems = computed(() => props.navItems.length > 0 
  ? props.navItems 
  : [])
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
      <div class="flex justify-between items-center px-4 md:px-6 py-4">
        <a
          href="/admin/dashboard"
          class="flex items-center gap-3 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/60 rounded-lg transition"
        >
          <div class="flex-1">
            <img
              src="/logo.svg"
              alt="Applikuj logo"
              class="brightness-0 invert rounded-lg w-auto h-10"
            >
          </div>
        </a>

        <nav
          :aria-label="t('admin.layout.nav.ariaLabel')"
          class="hidden md:flex absolute left-1/2 -translate-x-1/2 items-center gap-1"
        >
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

        <div class="flex items-center gap-3">
          <button
            class="md:hidden flex justify-center items-center hover:bg-white/10 rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/60 w-9 h-9 text-white/90 hover:text-white transition"
            :aria-label="isMobileMenuOpen ? t('admin.layout.nav.closeMenu') : t('admin.layout.nav.openMenu')"
            :aria-expanded="isMobileMenuOpen"
            @click="isMobileMenuOpen = true"
          >
            <IconMenu class="w-5 h-5" aria-hidden="true" />
          </button>

          <img
            class="hidden md:block rounded-full ring-2 ring-primary/10 w-10 h-10"
            src="https://www.gravatar.com/avatar?d=mp&s=48"
            alt=""
          >
          <div class="hidden md:block">
            <p class="font-medium text-white text-sm">TestAdmin</p>
            <p class="text-white/70 text-xs">{{ t('admin.layout.userRole') }}</p>
          </div>

          <div class="relative hidden md:block">
            <button
              class="flex justify-center items-center hover:bg-white/10 rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/60 w-9 h-9 text-white/90 hover:text-white transition"
              :aria-label="isDesktopMenuOpen ? t('admin.layout.nav.closeMenu') : t('admin.layout.nav.openMenu')"
              :aria-expanded="isDesktopMenuOpen"
              @click="isDesktopMenuOpen = !isDesktopMenuOpen"
            >
              <IconMenu v-if="!isDesktopMenuOpen" class="w-5 h-5" aria-hidden="true" />
              <IconX v-else class="w-5 h-5" aria-hidden="true" />
            </button>

            <div
              v-if="isDesktopMenuOpen"
              class="absolute right-0 top-full mt-2 bg-white rounded-lg shadow-lg ring-1 ring-black/5 py-1 min-w-[160px] z-50"
            >
              <a
                class="flex items-center gap-2 px-3 py-2 hover:bg-gray-100 text-gray-700 hover:text-gray-900 transition text-sm"
                href="#"
                @click="isDesktopMenuOpen = false"
              >
                <IconUser class="w-4 h-4" aria-hidden="true" />
                {{ t('admin.layout.nav.profile') }}
              </a>
              
              <a
                class="flex items-center gap-2 px-3 py-2 hover:bg-gray-100 text-gray-700 hover:text-gray-900 transition text-sm"
                href="#"
                @click="isDesktopMenuOpen = false"
              >
                <IconSettings class="w-4 h-4" aria-hidden="true" />
                {{ t('admin.layout.nav.settings') }}
              </a>

              <div class="relative">
                <button
                  class="flex items-center gap-2 px-3 py-2 hover:bg-gray-100 text-gray-700 hover:text-gray-900 transition text-sm w-full text-left"
                  @click="isDesktopLanguageOpen = !isDesktopLanguageOpen"
                >
                  <IconLanguage class="w-4 h-4" aria-hidden="true" />
                  {{ t('admin.layout.language') }}: {{ currentLanguage }}
                  <IconChevronDown class="w-3 h-3 ml-auto" aria-hidden="true" />
                </button>
                <div
                  v-if="isDesktopLanguageOpen"
                  class="absolute right-0 top-full mt-1 bg-white rounded-lg shadow-lg ring-1 ring-black/5 py-1 min-w-[80px] z-50"
                >
                  <button
                    class="flex items-center px-3 py-2 hover:bg-gray-100 text-gray-700 transition text-sm w-full text-left"
                    :class="locale === 'pl' ? 'text-primary font-medium' : ''"
                    @click="setLanguage('pl'); isDesktopLanguageOpen = false; isDesktopMenuOpen = false"
                  >
                    PL
                  </button>
                  <button
                    class="flex items-center px-3 py-2 hover:bg-gray-100 text-gray-700 transition text-sm w-full text-left"
                    :class="locale === 'en' ? 'text-primary font-medium' : ''"
                    @click="setLanguage('en'); isDesktopLanguageOpen = false; isDesktopMenuOpen = false"
                  >
                    EN
                  </button>
                </div>
              </div>

              
              <a
                class="flex items-center gap-2 px-3 py-2 hover:bg-red-50 text-red-600 hover:text-red-700 transition text-sm"
                href="#"
                @click="isDesktopMenuOpen = false"
              >
                <IconLogout class="w-4 h-4" aria-hidden="true" />
                {{ t('admin.layout.logout') }}
              </a>
            </div>
          </div>
        </div>
      </div>
    </header>

    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="isMobileMenuOpen"
        class="fixed inset-0 z-40 md:hidden"
        role="dialog"
        aria-modal="true"
        :aria-label="t('admin.layout.nav.mobileAriaLabel')"
      >
        <div
          class="absolute inset-0 bg-black/50 backdrop-blur-sm"
          @click="isMobileMenuOpen = false"
        />

        <Transition
          enter-active-class="transition duration-200 ease-out"
          enter-from-class="-translate-y-4 opacity-0"
          enter-to-class="translate-y-0 opacity-100"
          leave-active-class="transition duration-150 ease-in"
          leave-from-class="translate-y-0 opacity-100"
          leave-to-class="-translate-y-4 opacity-0"
        >
          <div
            v-if="isMobileMenuOpen"
            class="relative bg-text mx-4 mt-4 rounded-2xl shadow-2xl ring-1 ring-white/10 overflow-hidden"
          >
            <div class="flex items-center justify-between px-5 py-4 border-b border-white/10">
              <img
                src="/logo.svg"
                alt="Applikuj logo"
                class="brightness-0 invert rounded-lg w-auto h-8"
              >
              <button
                class="flex justify-center items-center hover:bg-white/10 rounded-lg w-9 h-9 text-white/80 hover:text-white transition"
                :aria-label="t('admin.layout.nav.closeMenu')"
                @click="isMobileMenuOpen = false"
              >
                <IconX class="w-5 h-5" aria-hidden="true" />
              </button>
            </div>

            <nav class="px-4 pt-4" :aria-label="t('admin.layout.nav.mobileAriaLabel')">
              <ul class="flex flex-col gap-1">
                <li v-for="item in navItems" :key="`mobile-${item.key}`">
                  <a
                    :href="item.href"
                    :aria-current="item.key === props.activePage ? 'page' : undefined"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-sm transition"
                    :class="item.key === props.activePage
                      ? 'bg-primary/80 text-white'
                      : 'text-white/70 hover:bg-white/10 hover:text-white'"
                    @click="isMobileMenuOpen = false"
                  >
                    <component
                      :is="item.icon"
                      class="w-5 h-5 shrink-0"
                      :class="item.key === props.activePage ? 'text-white' : 'text-white/50'"
                      aria-hidden="true"
                    />
                    {{ item.label }}
                  </a>
                </li>
              </ul>
            </nav>

            <div class="mx-4 my-3 border-t border-white/10" />

            <div class="px-4 pb-4 flex flex-col gap-1">
              <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-white/5">
                <img
                  class="rounded-full ring-2 ring-primary/30 w-10 h-10 shrink-0"
                  src="https://www.gravatar.com/avatar?d=mp&s=48"
                  alt="admin"
                >
                <div>
                  <p class="font-semibold text-white text-sm">TestAdmin</p>
                  <p class="text-white/50 text-xs">{{ t('admin.layout.userRole') }}</p>
                </div>
                <div class="ml-auto">
                  <LanguageDropdown />
                </div>
              </div>

              
              <a
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/70 hover:bg-white/10 hover:text-white transition text-sm font-medium"
                href="#"
                @click="isMobileMenuOpen = false"
              >
                <IconUser class="w-5 h-5 shrink-0 text-white/40" aria-hidden="true" />
                {{ t('admin.layout.nav.profile') }}
              </a>
              
              <a
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/70 hover:bg-white/10 hover:text-white transition text-sm font-medium"
                href="#"
                @click="isMobileMenuOpen = false"
              >
                <IconSettings class="w-5 h-5 shrink-0 text-white/40" aria-hidden="true" />
                {{ t('admin.layout.nav.settings') }}
              </a>
              
              <a
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-red-400 hover:bg-red-500/20 hover:text-red-300 transition text-sm font-medium"
                href="#"
                @click="isMobileMenuOpen = false"
              >
                <IconLogout class="w-5 h-5 shrink-0" aria-hidden="true" />
                {{ t('admin.layout.logout') }}
              </a>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>

    <main id="main-content" class="relative flex flex-col flex-1 justify-start items-stretch p-4 md:p-6 overflow-y-auto">
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
