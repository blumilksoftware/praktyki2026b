<script setup>
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { IconSearch, IconX } from '@tabler/icons-vue'

const { t } = useI18n()

const props = defineProps({
  modelValue: { type: Array, default: () => [] },
  maxTags: { type: Number, default: 20 },
  error: { type: String, default: undefined },
})

const emit = defineEmits(['update:modelValue'])

const availableTagsPool = ref([
  'Warszawa', 'Wrocław', 'Wadowice', 'Wronki', 'Web', 'Wdrażanie',
  'IT', 'Owocowe czwartki', 'Software house', 'jakiś tag', 'Programowanie',
  'Vue.js', 'Python', 'Django', 'React', 'Praca zdalna',
])

const searchQuery = ref('')

const selectedTags = computed(() => props.modelValue || [])

const isAtLimit = computed(() => selectedTags.value.length >= props.maxTags)

const filteredAvailableTags = computed(() => {
  const query = searchQuery.value.trim().toLowerCase()
  return availableTagsPool.value.filter((tag) => {
    if (selectedTags.value.includes(tag)) return false
    return !query || tag.toLowerCase().includes(query)
  })
})

function addTag(tag) {
  if (isAtLimit.value || selectedTags.value.includes(tag)) return
  emit('update:modelValue', [...selectedTags.value, tag])
}

function removeTag(tag) {
  emit('update:modelValue', selectedTags.value.filter((item) => item !== tag))
}

function clearSearch() {
  searchQuery.value = ''
}
</script>

<template>
  <div class="flex flex-col gap-4">
    <h2 class="text-xl font-bold text-text">{{ t('profiles.activeTags') }}</h2>

    <p class="text-sm text-text/70">
      {{ selectedTags.length }} / {{ maxTags }}
    </p>

    <div class="flex flex-wrap items-center gap-2">
      <span
        v-for="tag in selectedTags"
        :key="tag"
        class="inline-flex cursor-pointer items-center gap-1 rounded-full border border-primary/20 bg-primary/10 px-2.5 py-1 text-sm font-medium text-primary"
        @click="removeTag(tag)"
      >
        {{ tag }}
        <IconX class="h-3.5 w-3.5" aria-hidden="true" />
      </span>
    </div>

    <p v-if="error" class="text-error text-sm" role="alert">
      {{ error }}
    </p>

    <div class="relative w-full">
      <IconSearch class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-additional" aria-hidden="true" />
      <input
        v-model="searchQuery"
        type="text"
        :placeholder="t('profiles.searchTags')"
        :aria-label="t('profiles.searchTags')"
        class="w-full rounded-lg border border-border bg-white py-2 pl-9 pr-9 text-sm text-text outline-none focus:border-primary"
      >
      <button
        v-if="searchQuery"
        type="button"
        class="absolute right-2 top-1/2 -translate-y-1/2 text-additional transition-colors hover:text-text"
        :aria-label="t('dynamicList.accessibility.clearAll')"
        @click="clearSearch"
      >
        <IconX class="h-4 w-4" aria-hidden="true" />
      </button>
    </div>

    <div class="flex flex-wrap gap-2">
      <span
        v-for="tag in filteredAvailableTags"
        :key="tag"
        class="inline-flex items-center rounded-full border border-border bg-white px-2.5 py-1 text-sm text-text transition-colors"
        :class="isAtLimit ? 'cursor-not-allowed opacity-50' : 'cursor-pointer hover:border-primary hover:text-primary'"
        @click="addTag(tag)"
      >
        {{ tag }}
      </span>
    </div>
  </div>
</template>
