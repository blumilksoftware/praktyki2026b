<template>
  <div class="min-h-screen bg-background flex flex-col">
    <a
      href="#main-content"
      class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:m-3 focus:rounded-md focus:bg-white focus:px-3 focus:py-2 focus:text-sm focus:font-medium focus:text-primary"
    >
      {{ t('common.skipToContent') }}
    </a>

    <BaseNavbar
      :show-hamburger="true"
      :menu-items="menuItems"
      :show-navigation-buttons="true"
      :navigation-buttons="resolvedNavigationButtons"
      :navigation-variant="navigationVariant || 'default'"
      @navigation-click="handleNavigationClick"
    />

    <main id="main-content" class="flex-1">
      <div class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <slot />
      </div>
    </main>

    <BaseToast ref="toastRef" />
  </div>
</template>

<script setup>
import { computed, onMounted, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import BaseNavbar from '@/Components/Navigation/BaseNavbar.vue'
import BaseToast from '@/Components/Base/BaseToast.vue'
import { useCurrentPanelMenu } from '@/Composables/useCurrentPanelMenu'
import { useToast } from '@/Composables/useToast'

const page = usePage()
const { t } = useI18n()
const { toastRef, toastSuccess, toastError } = useToast()

const props = defineProps({
  activePage: {
    type: String,
    default: null,
  },
  navItems: {
    type: Array,
    default: null,
  },
  navigationButtons: {
    type: Array,
    default: null,
  },
  navigationVariant: {
    type: String,
    default: 'default',
  },
})

const emit = defineEmits(['navigationClick'])

const currentPanelMenu = useCurrentPanelMenu(computed(() => props.activePage))

const definesOwnMenu = computed(() => props.navItems !== null || props.navigationButtons !== null)

const resolvedNavItems = computed(
  () => props.navItems ?? (definesOwnMenu.value ? [] : currentPanelMenu.value),
)
const resolvedNavigationButtons = computed(
  () => props.navigationButtons ?? (definesOwnMenu.value ? [] : currentPanelMenu.value),
)

const menuItems = computed(() => {
  return resolvedNavItems.value.map(item => ({
    ...item,
    isActive: item.key === props.activePage ||
              (item.href && page.url === item.href),
  }))
})

const handleNavigationClick = (item) => {
  emit('navigationClick', item)
}

const showFlashToast = () => {
  const status = page.props.flash?.status

  if (status) {
    toastSuccess(status)
  }
}

const showFlashErrorToast = () => {
  const error = page.props.flash?.error

  if (error) {
    toastError(error)
  }
}

onMounted(() => {
  showFlashToast()
  showFlashErrorToast()
})
watch(() => page.props.flash?.status, showFlashToast)
watch(() => page.props.flash?.error, showFlashErrorToast)
</script>
