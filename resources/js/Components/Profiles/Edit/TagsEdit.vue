<script setup>
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { IconX, IconSearch } from '@tabler/icons-vue'

const { t } = useI18n()

const props = defineProps({
  modelValue: { type: Array, default: () => [] },
  maxTags: { type: Number, default: 20 },
})

const emit = defineEmits(['update:modelValue'])

const searchQuery = ref('')

const availableTagsPool = ref([
  'Warszawa', 'Wrocław', 'Wadowice', 'Wronki', 'Web', 'Wdrażanie',
  'IT', 'Owocowe czwartki', 'Software house', 'jakiś tag', 'Programowanie',
  'Vue.js', 'Python', 'Django', 'React', 'Praca zdalna',
])

const activeTags = computed({
  get: () => props.modelValue || [],
  set: (value) => emit('update:modelValue', value),
})

const filteredTags = computed(() => {
  const query = searchQuery.value.toLowerCase()
  return availableTagsPool.value.filter(tag => 
    tag.toLowerCase().includes(query) && !activeTags.value.includes(tag),
  )
})

const addTag = (tag) => {
  if (activeTags.value.length < props.maxTags) {
    activeTags.value = [...activeTags.value, tag]
  }
}

const removeTag = (tagToRemove) => {
  activeTags.value = activeTags.value.filter(tag => tag !== tagToRemove)
}
</script>

<template>
  <div class="grid grid-cols-1 lg:grid-cols-2 mt-10 gap-4 sm:gap-6 w-full">
    <!-- Lewy panel: Dostępne tagi -->
    <div class="border border-border rounded-xl overflow-hidden flex flex-col h-80 bg-white">
      <div class="p-3 border-b border-border">
        <div class="relative">
          <input
            v-model="searchQuery"
            type="text"
            :placeholder="t('profiles.searchTags')"
            class="w-full pl-4 pr-10 py-2 border border-border rounded-full text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-shadow"
          >
          <div
            v-if="!searchQuery"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400"
          >
            <IconSearch stroke="1.5" class="w-4 h-4" />
          </div>
          <button
            v-if="searchQuery"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
            @click="searchQuery = ''"
          >
            <IconX stroke="1.5" class="w-4 h-4" />
          </button>
        </div>
      </div>

      <div class="p-4 overflow-y-auto flex-1 flex flex-wrap content-start justify-center gap-3">
        <span
          v-for="tag in filteredTags"
          :key="tag"
          class="px-5 py-1.5 text-[13px] sm:text-sm font-semibold rounded-full transition-colors"
          :class="activeTags.length >= maxTags 
            ? 'bg-gray-50 text-gray-400 border border-gray-200 cursor-not-allowed' 
            : 'text-tag-text bg-tag border border-secondary cursor-pointer hover:bg-secondary hover:text-white'"
          @click="addTag(tag)"
        >
          {{ tag }}
        </span>
        
        <div v-if="filteredTags.length === 0" class="w-full text-center text-sm text-gray-400 mt-4">
          {{ t('profiles.noResults') }}
        </div>
      </div>
    </div>

    <div class="border border-border rounded-xl overflow-hidden flex flex-col h-80 bg-white relative">
      <div class="bg-[#0f172a] text-white py-3.5 text-center font-bold text-[15px] shrink-0">
        {{ t('profiles.activeTags') }}
      </div>

      <div class="p-4 overflow-y-auto flex-1 flex flex-wrap content-start justify-center gap-3 pb-12">
        <span
          v-for="tag in activeTags"
          :key="tag"
          class="px-5 py-1.5 text-[13px] sm:text-sm font-semibold text-tag-text bg-tag border border-secondary rounded-full transition-all cursor-pointer hover:bg-red-500 hover:text-white hover:border-red-500 group relative pr-7"
          @click="removeTag(tag)"
        >
          {{ tag }}
          <IconX stroke="2" class="w-3.5 h-3.5 absolute right-2.5 top-1/2 -translate-y-1/2 text-secondary group-hover:text-white transition-colors" />
        </span>

        <div v-if="activeTags.length === 0" class="w-full text-center text-sm text-gray-400 mt-4">
          {{ t('profiles.noActiveTags') }}
        </div>
      </div>      
      
      <!-- Licznik tagów na dole -->
      <div class="absolute bottom-0 left-0 right-0 bg-gray-50 border-t border-border py-2.5 flex justify-center items-center">
        <span 
          class="text-xs font-bold tracking-wide" 
          :class="activeTags.length >= maxTags ? 'text-red-500' : 'text-gray-500'"
        >
          {{ activeTags.length }} / {{ maxTags }}
        </span>
      </div>
    </div>
  </div>
</template>
