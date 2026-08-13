<script setup>
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import BaseButton from '@/Components/Base/BaseButton.vue'

const { t } = useI18n()

const props = defineProps({
  faculties: {
    type: Array,
    default: () => [],
  },
  limit: {
    type: Number,
    default: null,
  },
})

const visibleCount = ref(props.limit)

const visibleFaculties = computed(
  () => (visibleCount.value === null ? props.faculties : props.faculties.slice(0, visibleCount.value)),
)
const hasMoreFaculties = computed(() => visibleCount.value !== null && props.faculties.length > visibleCount.value)

const loadMore = () => {
  visibleCount.value += props.limit
}
</script>

<template>
  <div v-if="faculties && faculties.length > 0" class="flex flex-col gap-3">
    <div
      v-for="faculty in visibleFaculties"
      :key="faculty.id"
      class="rounded-2xl border border-border bg-white p-4 shadow-sm transition-colors hover:border-primary/40 sm:p-5"
    >
      <h3 class="font-semibold text-text text-base">{{ faculty.name }}</h3>

      <ul v-if="faculty.study_fields && faculty.study_fields.length > 0" class="mt-3 flex flex-wrap gap-2">
        <li
          v-for="field in faculty.study_fields"
          :key="field.id"
          class="rounded-lg border border-blue-100 bg-blue-50 px-3 py-1.5 font-medium text-primary text-sm"
        >
          {{ field.name }}
        </li>
      </ul>
      <p v-else class="mt-2 text-additional text-sm italic">
        {{ t('profiles.university.noStudyFields') }}
      </p>
    </div>

    <BaseButton
      v-if="hasMoreFaculties"
      variant="secondary"
      class="mt-4 w-full justify-center"
      @click="loadMore"
    >
      {{ t('buttons.load_more') }}
    </BaseButton>
  </div>

  <div v-else class="text-gray-500 italic">
    {{ t('profiles.university.noFaculties') }}
  </div>
</template>
