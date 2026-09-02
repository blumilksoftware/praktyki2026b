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
const triggerRef = ref(null)

const availableLocales = [
  { code: 'pl', label: 'PL' },
  { code: 'en', label: 'EN' },
]

const currentLocale = computed(() => locale.value || 'pl')
const currentLabel = computed(() => currentLocale.value.toUpperCase())
const triggerAriaLabel = computed(() => `${t('admin.layout.language')}: ${currentLabel.value}`)

function setLanguage(lang) {
  if (lang === currentLocale.value) {
    isOpen.value = false

    return
  }

  router.post(ROUTES.LANGUAGE_SWITCH.replace('{locale}', lang), {}, {
    preserveState: false,
    preserveScroll: true,
    onSuccess: () => {
      locale.value = lang
      isOpen.value = false
    },
  })
}

function closeAndRestoreFocus() {
  isOpen.value = false
  triggerRef.value?.focus()
}

function handleClickOutside(event) {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    isOpen.value = false
  }
}

onMounted(() => document.addEventListener('mousedown', handleClickOutside))
onUnmounted(() => document.removeEventListener('mousedown', handleClickOutside))
</script>

<template>
  <div ref="dropdownRef" class="relative inline-block" @keydown.escape="closeAndRestoreFocus">
    <button
      ref="triggerRef"
      type="button"
      class="inline-flex items-center gap-1.5 rounded-lg transition-colors focus:outline-none focus-visible:ring-2 hover:cursor-pointer"
      :class="[
        props.mobile ? 'px-3 py-2 text-sm font-medium' : 'px-2 py-1.5',
        props.variant === 'light'
          ? 'border border-border bg-white text-text shadow-sm hover:bg-background hover:text-primary focus-visible:ring-primary/30'
          : 'text-white/80 hover:bg-white/10 hover:text-white focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-primary'
      ]"
      :aria-label="triggerAriaLabel"
      aria-haspopup="true"
      :aria-expanded="isOpen"
      @click="isOpen = !isOpen"
    >
      <IconWorld class="h-5 w-5" aria-hidden="true" />
      <span class="text-sm font-bold tracking-wide">{{ currentLabel }}</span>
      <IconChevronDown
        class="h-4 w-4 transition-transform duration-200"
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
        v-if="isOpen"
        class="absolute right-0 top-full z-50 mt-2 min-w-20 overflow-hidden rounded-lg border border-border bg-white shadow-md"
      >
        <div class="py-1">
          <button
            v-for="lang in availableLocales"
            :key="lang.code"
            type="button"
            class="flex w-full items-center px-4 py-2 text-sm transition-colors focus-visible:bg-background focus-visible:outline-none hover:bg-background hover:cursor-pointer"
            :class="currentLocale === lang.code ? 'bg-primary/5 font-bold text-primary' : 'text-additional'"
            :aria-current="currentLocale === lang.code ? 'true' : undefined"
            @click="setLanguage(lang.code)"
          >
            {{ lang.label }}
          </button>
        </div>
      </div>
    </transition>
  </div>
</template>
