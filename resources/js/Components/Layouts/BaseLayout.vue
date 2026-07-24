<template>
  <div class="min-h-screen bg-background flex flex-col">
    <BaseNavbar 
      :show-hamburger="true"
      :menu-items="menuItems"
      :show-navigation-buttons="true"
      :navigation-buttons="navigationButtons"
      :navigation-variant="navigationVariant || 'default'"
      @navigation-click="handleNavigationClick"
    />
    
    <main class="flex-1">
      <slot />
    </main>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import BaseNavbar from '@/Components/Navigation/BaseNavbar.vue'

const page = usePage()

const props = defineProps({
  activePage: {
    type: String,
    default: null,
  },
  navItems: {
    type: Array,
    default: () => [],
  },
  navigationButtons: {
    type: Array,
    default: () => [],
  },
  navigationVariant: {
    type: String,
    default: 'default',
  },
})

const emit = defineEmits(['navigationClick'])

const menuItems = computed(() => {
  return props.navItems.map(item => ({
    ...item,
    isActive: item.key === props.activePage || 
              (item.href && page.url === item.href),
  }))
})

const handleNavigationClick = (item) => {
  emit('navigationClick', item)
}
</script>
