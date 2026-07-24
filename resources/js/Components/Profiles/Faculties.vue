<script setup>
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

defineProps({
  faculties: { 
    type: Array, 
    default: () => [], 
  },
})
</script>

<template>
  <div v-if="faculties && faculties.length > 0" class="space-y-4">
    <div 
      v-for="faculty in faculties" 
      :key="faculty.id" 
      class="p-5 border border-gray-200 rounded-xl bg-gray-50/50 shadow-sm"
    >
      <h4 class="font-bold text-lg text-secondary mb-3">{{ faculty.name }}</h4>
      
      <ul v-if="faculty.study_fields && faculty.study_fields.length > 0" class="flex flex-wrap gap-2">
        <li 
          v-for="field in faculty.study_fields" 
          :key="field.id"
          class="bg-blue-50 text-primary px-3 py-1.5 rounded-lg text-sm font-medium border border-blue-100"
        >
          {{ field.name }}
        </li>
      </ul>
      <div v-else class="text-sm text-gray-400 italic">
        {{ t('profiles.university.noStudyFields') }}
      </div>
    </div>
  </div>

  <div v-else class="text-gray-500 italic">
    {{ t('profiles.university.noFaculties') }}
  </div>
</template>
