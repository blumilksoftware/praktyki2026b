<script setup>
import { computed, ref } from 'vue'
import { IconLanguage, IconChevronDown } from '@tabler/icons-vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  mobile: { type: Boolean, default: false },
  variant: { type: String, default: 'dark' },
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
        'flex items-center gap-1 rounded-lg focus-visible:outline-none focus-visible:ring-2 transition font-medium text-sm whitespace-nowrap',
        props.mobile ? 'px-3 py-2' : 'px-2 py-1.5',
        props.variant === 'light'
          ? 'bg-white border border-border hover:bg-background text-text hover:text-primary focus-visible:ring-primary/30 shadow-sm'
          : 'bg-white/10 hover:bg-white/20 text-white/70 hover:text-white focus-visible:ring-white/30',
      ]"
      @click="isOpen = !isOpen"
    >
      <IconLanguage class="w-4 h-4" aria-hidden="true" />
      <span class="font-medium text-sm">{{ currentLanguage }}</span>
      <IconChevronDown class="w-3 h-3" aria-hidden="true" />
    </button>

    <div
      v-if="isOpen"
      class="top-full right-0 z-50 absolute bg-white shadow-[0_18px_60px_rgba(11,26,48,0.14)] mt-1 py-1 rounded-lg ring-1 ring-border min-w-20"
    >
      <button
        class="hover:bg-background focus-visible:bg-background px-3 py-2 focus-visible:outline-none w-full text-sm text-left transition text-text"
        :class="locale === 'pl' ? 'font-semibold text-primary' : 'text-text'"
        :aria-label="t('admin.layout.languageSwitch.toPolish')"
        @click="setLanguage('pl')"
      >
        PL
      </button>
      <button
        class="hover:bg-background focus-visible:bg-background px-3 py-2 focus-visible:outline-none w-full text-sm text-left transition text-text"
        :class="locale === 'en' ? 'font-semibold text-primary' : 'text-text'"
        :aria-label="t('admin.layout.languageSwitch.toEnglish')"
        @click="setLanguage('en')"
      >
        EN
      </button>
    </div>
  </div>
</template>
