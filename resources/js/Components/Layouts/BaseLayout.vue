<template>
  <div class="min-h-screen bg-background flex flex-col">
    <BaseNavbar
      :show-hamburger="true"
      :menu-items="menuItems"
      :show-navigation-buttons="true"
      :navigation-buttons="resolvedNavigationButtons"
      :navigation-variant="navigationVariant || 'default'"
      @navigation-click="handleNavigationClick"
    />

    <main class="flex-1">
      <div class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <slot />
      </div>
    </main>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import BaseNavbar from '@/Components/Navigation/BaseNavbar.vue'
import { useCurrentPanelMenu } from '@/Composables/useCurrentPanelMenu'

const page = usePage()

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
</script>
