<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { IconWorld, IconChevronDown } from '@tabler/icons-vue'
import { useI18n } from 'vue-i18n'
import { router } from '@inertiajs/vue3'
import { ROUTES } from '@/Helpers/routes'

const props = defineProps({
  mobile: { type: Boolean, default: false },
  variant: { type: String, default: 'dark' },
})

const { t, locale } = useI18n()
const isOpen = ref(false)
const dropdownRef = ref(null)

const availableLocales = [
  { code: 'pl', label: 'PL', aria: 'admin.layout.languageSwitch.toPolish' },
  { code: 'en', label: 'EN', aria: 'admin.layout.languageSwitch.toEnglish' },
]

const currentLanguage = computed(() => (locale.value || 'pl').toUpperCase())

function setLanguage(lang) {
  const url = ROUTES.LANGUAGE_SWITCH.replace('{locale}', lang)

  router.post(url, {}, {
    preserveState: false,
    preserveScroll: true,
    onSuccess: () => {
      locale.value = lang
      isOpen.value = false
    },
  })
}

const closeDropdown = (e) => {
  if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
    isOpen.value = false
  }
}

onMounted(() => document.addEventListener('click', closeDropdown))
onUnmounted(() => document.removeEventListener('click', closeDropdown))
</script>

<template>
  <div ref="dropdownRef" class="relative">
    <button
      :class="[
        'flex items-center gap-1.5 rounded-lg focus-visible:outline-none focus-visible:ring-2 transition-colors hover:cursor-pointer',
        props.mobile ? 'px-3 py-2 font-medium text-sm' : 'px-2 py-1.5',
        props.variant === 'light'
          ? 'bg-white border border-border hover:bg-background text-text hover:text-primary focus-visible:ring-primary/30 shadow-sm'
          : props.mobile
            ? 'bg-white/10 hover:bg-white/20 text-white/70 hover:text-white focus-visible:ring-white/30'
            : 'hover:bg-white/10 text-slate-300 hover:text-white focus-visible:ring-white/30'
      ]"
      @click="isOpen = !isOpen"
    >
      <IconWorld class="w-5 h-5 opacity-90" aria-hidden="true" />
      <span class="font-bold text-sm tracking-wide">{{ currentLanguage }}</span>
      <IconChevronDown 
        class="w-4 h-4 opacity-80 transition-transform duration-200" 
        :class="{ 'rotate-180': isOpen }"
        aria-hidden="true" 
      />
    </button>

    <transition
      enter-active-class="transition ease-out duration-100"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div
        v-show="isOpen"
        class="absolute right-0 top-full z-50 mt-2 min-w-20 overflow-hidden rounded-lg bg-white shadow-lg ring-1 ring-black/5"
      >
        <div class="py-1">
          <button
            v-for="lang in availableLocales"
            :key="lang.code"
            class="flex w-full items-center px-4 py-2 text-sm transition-colors focus-visible:bg-slate-50 focus-visible:outline-none hover:bg-slate-50 hover:cursor-pointer"
            :class="locale === lang.code ? 'text-primary font-bold bg-slate-50/50' : 'text-slate-600'"
            :aria-label="t(lang.aria)"
            @click="setLanguage(lang.code)"
          >
            {{ lang.label }}
          </button>
        </div>
      </div>
    </transition>
  </div>
</template>
