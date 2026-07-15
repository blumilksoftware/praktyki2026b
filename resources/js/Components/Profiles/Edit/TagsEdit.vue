<script setup>
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import DynamicMultiSelect from '@/Components/Common/DynamicMultiSelect.vue' 

const { t } = useI18n()

const props = defineProps({
  modelValue: { type: Array, default: () => [] },
  maxTags: { type: Number, default: 10 },
  error: { type: String, default: undefined },
})

const emit = defineEmits(['update:modelValue'])

const availableTagsPool = ref([
  'Warszawa', 'Wrocław', 'Wadowice', 'Wronki', 'Web', 'Wdrażanie',
  'IT', 'Owocowe czwartki', 'Software house', 'jakiś tag', 'Programowanie',
  'Vue.js', 'Python', 'Django', 'React', 'Praca zdalna',
])

const selectedTags = computed({
  get: () => props.modelValue || [],
  set: (value) => emit('update:modelValue', value),
})
</script>

<template>
  <div class="flex flex-col gap-4">
    <h2 class="text-xl font-bold text-text">{{ t('profiles.activeTags') }}</h2>
    
    <p class="text-sm text-text/70">
      {{ selectedTags.length }} / {{ maxTags }}
    </p>

    <div class="w-full">
      <DynamicMultiSelect 
        v-model="selectedTags" 
        :options="availableTagsPool"
        :max="maxTags"
        :allow-custom="false"
        :placeholder="t('profiles.searchTags')"
        :error="error"
      />
    </div>
  </div>
</template>
