<script setup>
import { ref, computed, nextTick, onMounted, onUnmounted } from 'vue'
import { IconChevronDown, IconSearch } from '@tabler/icons-vue'

const props = defineProps({
  modelValue: { type: String, required: true },
  options: { type: Array, required: true },
  ariaLabel: { type: String, default: '' },
  searchable: { type: Boolean, default: false },
  searchPlaceholder: { type: String, default: '' },
  emptyLabel: { type: String, default: '' },
})

const emit = defineEmits(['update:modelValue'])

const isOpen = ref(false)
const dropdownRef = ref(null)
const searchInput = ref(null)
const searchQuery = ref('')

const selectedLabel = computed(() => props.options.find(option => option.value === props.modelValue)?.label ?? '')

const visibleOptions = computed(() => {
  const query = searchQuery.value.trim().toLowerCase()

  if (!props.searchable || query === '') {
    return props.options
  }

  return props.options.filter(option => option.label.toLowerCase().includes(query))
})

function toggle() {
  isOpen.value = !isOpen.value

  if (isOpen.value && props.searchable) {
    searchQuery.value = ''
    nextTick(() => searchInput.value?.focus())
  }
}

function select(value) {
  emit('update:modelValue', value)
  isOpen.value = false
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
      type="button"
      class="flex items-center justify-between gap-2 bg-white px-4 py-2 border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/60 min-w-40 text-slate-700 text-sm"
      :aria-label="ariaLabel"
      @click="toggle"
    >
      <span>{{ selectedLabel }}</span>
      <IconChevronDown
        class="w-4 h-4 opacity-60 transition-transform duration-200"
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
        class="absolute left-0 top-full z-50 mt-2 min-w-full overflow-hidden rounded-lg bg-white shadow-lg ring-1 ring-black/5"
      >
        <div v-if="searchable" class="relative border-b border-border">
          <div class="left-3 absolute inset-y-0 flex items-center pointer-events-none">
            <IconSearch class="w-4 h-4 text-slate-400" aria-hidden="true" />
          </div>
          <input
            ref="searchInput"
            v-model="searchQuery"
            type="text"
            :placeholder="searchPlaceholder"
            :aria-label="searchPlaceholder"
            class="bg-white px-4 py-2 pl-9 focus:outline-none w-full text-slate-700 text-sm"
          >
        </div>

        <div class="py-1 max-h-64 overflow-y-auto">
          <button
            v-for="option in visibleOptions"
            :key="option.value"
            type="button"
            class="flex items-center hover:bg-slate-50 px-4 py-2 w-full text-sm text-left whitespace-nowrap hover:cursor-pointer transition-colors"
            :class="option.value === modelValue ? 'text-primary font-bold bg-slate-50/50' : 'text-slate-600'"
            @click="select(option.value)"
          >
            {{ option.label }}
          </button>

          <p
            v-if="visibleOptions.length === 0"
            class="px-4 py-2 text-slate-500 text-sm"
            role="status"
          >
            {{ emptyLabel }}
          </p>
        </div>
      </div>
    </transition>
  </div>
</template>
