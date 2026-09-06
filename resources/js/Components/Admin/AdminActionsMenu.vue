<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { IconDotsVertical } from '@tabler/icons-vue'

const props = defineProps({
  label: { type: String, required: true },
})

const isOpen = ref(false)
const menuRef = ref(null)

function toggle() {
  isOpen.value = !isOpen.value
}

function close() {
  isOpen.value = false
}

function handleClickOutside(event) {
  if (menuRef.value && !menuRef.value.contains(event.target)) {
    close()
  }
}

function handleKeydown(event) {
  if (event.key === 'Escape' && isOpen.value) {
    close()
  }
}

onMounted(() => {
  document.addEventListener('mousedown', handleClickOutside)
  document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  document.removeEventListener('mousedown', handleClickOutside)
  document.removeEventListener('keydown', handleKeydown)
})

defineExpose({ close })
</script>

<template>
  <div ref="menuRef" class="relative inline-flex items-center text-left">
    <button
      type="button"
      class="flex h-9 w-9 items-center justify-center rounded-lg text-additional hover:bg-gray-100 cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
      :aria-label="props.label"
      :aria-expanded="isOpen"
      aria-haspopup="true"
      @click="toggle"
    >
      <IconDotsVertical class="w-5 h-5" aria-hidden="true" />
    </button>

    <div
      v-if="isOpen"
      class="absolute right-0 top-full z-50 mt-1 w-44 rounded-lg border border-border bg-white shadow-lg py-1"
      role="menu"
      @click="close"
    >
      <slot />
    </div>
  </div>
</template>
