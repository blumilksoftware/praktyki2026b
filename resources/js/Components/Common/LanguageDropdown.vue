<script setup>
import { computed, ref } from 'vue'
import { IconLanguage, IconChevronDown } from '@tabler/icons-vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  mobile: { type: Boolean, default: false },
})

const { t, locale } = useI18n()

const isOpen = ref(false)
const currentLanguage = computed(() => (locale.value || 'pl').toUpperCase())

function setLanguage(lang) {
  locale.value = lang
  localStorage.setItem('locale', lang)
  isOpen.value = false
}
</script>

<template>
  <div class="relative">
    <button
      :class="[
        'flex items-center gap-1 rounded-lg focus-visible:outline-none focus-visible:ring-2 transition',
        props.mobile 
          ? 'bg-white/10 hover:bg-white/20 px-3 py-2 text-white/70 hover:text-white focus-visible:ring-white/30 font-medium text-sm whitespace-nowrap'
          : 'hover:bg-white/10 px-2 py-1.5 text-white/90 hover:text-white focus-visible:ring-white/30'
      ]"
      @click="isOpen = !isOpen"
    >
      <IconLanguage class="w-4 h-4" aria-hidden="true" />
      <span class="font-medium text-sm">{{ currentLanguage }}</span>
      <IconChevronDown class="w-3 h-3" aria-hidden="true" />
    </button>
    <div
      v-if="isOpen"
      class="top-full right-0 z-50 absolute bg-white shadow-lg mt-1 py-1 rounded-lg ring-1 ring-black/5 min-w-20"
    >
      <button
        class="hover:bg-slate-50 focus-visible:bg-slate-50 px-3 py-2 focus-visible:outline-none w-full text-sm text-left transition"
        :class="locale === 'pl' ? 'text-primary font-medium' : 'text-slate-600'"
        :aria-label="t('admin.layout.languageSwitch.toPolish')"
        @click="setLanguage('pl')"
      >
        PL
      </button>
      <button
        class="hover:bg-slate-50 focus-visible:bg-slate-50 px-3 py-2 focus-visible:outline-none w-full text-sm text-left transition"
        :class="locale === 'en' ? 'text-primary font-medium' : 'text-slate-600'"
        :aria-label="t('admin.layout.languageSwitch.toEnglish')"
        @click="setLanguage('en')"
      >
        EN
      </button>
    </div>
  </div>
</template>
