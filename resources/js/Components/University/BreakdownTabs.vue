<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import ProfilePageCard from '@/Components/Profile/ProfilePageCard.vue'

const props = defineProps({
  faculties: {
    type: Array,
    default: () => [],
  },
  fields: {
    type: Object,
    default: () => ({ data: [], links: [], current_page: 1, last_page: 1 }),
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
})

const { t, te } = useI18n()
const activeTab = ref('faculty')

const changeFieldPage = (page) => {
  if (page < 1 || page > props.fields.last_page || page === props.fields.current_page) {
    return
  }

  router.get(
    window.location.pathname,
    {
      ...props.filters,
      fieldPage: page,
    },
    {
      preserveState: true,
      preserveScroll: true,
    },
  )
}
</script>

<template>
  <ProfilePageCard class="overflow-hidden">
    <div class="border-b border-border px-6 pt-5 pb-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h2 class="text-base font-semibold text-text">
          {{ t('university.dashboard.breakdown.title') }}
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

    <div v-if="activeTab === 'faculty'" class="p-6">
      <div v-if="!faculties.length" class="text-center py-8 text-additional text-sm">
        {{ t('university.dashboard.breakdown.empty') }}
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left text-sm text-text">
          <thead class="bg-background text-additional border-b border-border">
            <tr>
              <th class="py-3 px-4 font-medium">{{ t('university.dashboard.breakdown.faculty') }}</th>
              <th class="py-3 px-4 font-medium text-right">{{ t('university.dashboard.breakdown.students') }}</th>
              <th class="py-3 px-4 font-medium text-right">{{ t('university.dashboard.breakdown.applications') }}</th>
              <th class="py-3 px-4 font-medium text-right">{{ t('university.dashboard.breakdown.accepted') }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border">
            <tr v-for="item in faculties" :key="item.facultyId || 'unknown'" class="hover:bg-background/50">
              <td class="py-3 px-4 font-medium">
                {{ item.facultyName || t('university.dashboard.breakdown.unknown') }}
              </td>
              <td class="py-3 px-4 text-right">{{ item.linkedStudents }}</td>
              <td class="py-3 px-4 text-right">{{ item.applicationsSubmitted }}</td>
              <td class="py-3 px-4 text-right font-medium text-primary">{{ item.acceptedPlacements }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="activeTab === 'field'" class="p-6">
      <div v-if="!fields.data || !fields.data.length" class="text-center py-8 text-additional text-sm">
        {{ t('university.dashboard.breakdown.empty') }}
      </div>

      <div v-else class="space-y-4">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm text-text">
            <thead class="bg-background text-additional border-b border-border">
              <tr>
                <th class="py-3 px-4 font-medium">{{ t('university.dashboard.breakdown.field') }}</th>
                <th class="py-3 px-4 font-medium text-right">{{ t('university.dashboard.breakdown.students') }}</th>
                <th class="py-3 px-4 font-medium text-right">{{ t('university.dashboard.breakdown.applications') }}</th>
                <th class="py-3 px-4 font-medium text-right">{{ t('university.dashboard.breakdown.accepted') }}</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-border">
              <tr v-for="item in fields.data" :key="item.fieldId || item.fieldName" class="hover:bg-background/50">
                <td class="py-3 px-4 font-medium">
                  {{ item.fieldName || item.fieldId || t('university.dashboard.breakdown.unknown') }}
                </td>
                <td class="py-3 px-4 text-right">{{ item.linkedStudents }}</td>
                <td class="py-3 px-4 text-right">{{ item.applicationsSubmitted }}</td>
                <td class="py-3 px-4 text-right font-medium text-primary">{{ item.acceptedPlacements }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="fields.last_page > 1" class="flex items-center justify-between pt-4 border-t border-border">
          <span class="text-xs text-additional">
            {{ t('university.dashboard.breakdown.showingResults', {
              from: fields.from || 1,
              to: fields.to || fields.data.length,
              total: fields.total
            }) }}
          </span>

          <div class="flex items-center gap-2">
            <button
              type="button"
              :disabled="fields.current_page === 1"
              class="px-3 py-1 text-xs border border-border rounded-md hover:bg-background disabled:opacity-50 disabled:cursor-not-allowed"
              @click="changeFieldPage(fields.current_page - 1)"
            >
              &larr;
            </button>
            <span class="text-xs font-medium px-2">
              {{ fields.current_page }} / {{ fields.last_page }}
            </span>
            <button
              type="button"
              :disabled="fields.current_page === fields.last_page"
              class="px-3 py-1 text-xs border border-border rounded-md hover:bg-background disabled:opacity-50 disabled:cursor-not-allowed"
              @click="changeFieldPage(fields.current_page + 1)"
            >
              &rarr;
            </button>
          </div>
        </div>
      </div>
    </div>
  </ProfilePageCard>
</template>
