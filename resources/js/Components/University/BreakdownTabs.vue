<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import ProfilePageCard from '@/Components/Profile/ProfilePageCard.vue'
import BreakdownTable from '@/Components/University/BreakdownTable.vue'

defineProps({
  faculties: {
    type: Object,
    default: () => ({ data: [], links: [], current_page: 1, last_page: 1, total: 0 }),
  },
  fields: {
    type: Object,
    default: () => ({ data: [], links: [], current_page: 1, last_page: 1, total: 0 }),
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
})

const { t, te } = useI18n()
const activeTab = ref('faculty')
</script>

<template>
  <ProfilePageCard class="overflow-hidden">
    <div class="border-b border-border px-6 pt-5 pb-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h2 class="text-base font-semibold text-text">
          {{ t('common.titles.departmentStatistics') }}
        </h2>
        <p v-if="te('university.dashboard.breakdown.subtitle')" class="text-xs text-additional mt-0.5">
          {{ t('university.dashboard.breakdown.subtitle') }}
        </p>
      </div>

      <div class="flex items-center gap-1 bg-background p-1 rounded-lg border border-border self-start md:self-auto">
        <button
          type="button"
          :class="[
            'px-3 py-1.5 text-xs font-medium rounded-md transition-all',
            activeTab === 'faculty'
              ? 'bg-primary/10 text-primary font-semibold shadow-xs'
              : 'text-additional hover:text-text hover:bg-background/60',
          ]"
          @click="activeTab = 'faculty'"
        >
          {{ t('university.dashboard.breakdown.byFaculty') }}
        </button>

        <button
          type="button"
          :class="[
            'px-3 py-1.5 text-xs font-medium rounded-md transition-all',
            activeTab === 'field'
              ? 'bg-primary/10 text-primary font-semibold shadow-xs'
              : 'text-additional hover:text-text hover:bg-background/60',
          ]"
          @click="activeTab = 'field'"
        >
          {{ t('university.dashboard.breakdown.byField') }}
        </button>
      </div>
    </div>

    <div class="p-6">
      <BreakdownTable
        v-if="activeTab === 'faculty'"
        :rows="faculties"
        :filters="filters"
        param-prefix="faculty"
        name-key="facultyName"
        id-key="facultyId"
        :name-label="t('common.fields.faculty')"
        :search-placeholder="t('university.dashboard.breakdown.searchPlaceholder')"
      />

      <BreakdownTable
        v-else
        :rows="fields"
        :filters="filters"
        param-prefix="field"
        name-key="fieldName"
        id-key="fieldId"
        :name-label="t('common.fields.studyField')"
        :search-placeholder="t('university.dashboard.breakdown.searchPlaceholder')"
      />
    </div>
  </ProfilePageCard>
</template>
