<script setup>
import { ref, watch } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { IconSearch, IconMapPin } from '@tabler/icons-vue'
import BaseLayout from '@/Components/Layouts/BaseLayout.vue'
import UniversityCard from '@/Components/Company/UniversityCard.vue'
import FilterSuggestField from '@/Components/Partnership/FilterSuggestField.vue'
import Pagination from '@/Components/Common/Pagination.vue'
import { useCompanyPanelMenu } from '@/Composables/useCompanyPanelMenu'
import { ROUTES } from '@/Helpers/routes'

const { t } = useI18n()

const props = defineProps({
  universities: {
    type: Object,
    default: () => ({ data: [], links: {}, meta: {} }),
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
  cityOptions: {
    type: Array,
    default: () => [],
  },
})

const companyMenu = useCompanyPanelMenu('universities')

const nameFilter = ref(props.filters.name || '')
const cityFilter = ref(props.filters.city || '')

function search() {
  router.get(ROUTES.COMPANY_UNIVERSITIES, {
    name: nameFilter.value,
    city: cityFilter.value,
  }, {
    preserveState: true,
    replace: true,
  })
}

watch([nameFilter, cityFilter], search, { debounce: 300 })
</script>

<template>
  <Head :title="t('company.universities.title')" />
  <BaseLayout
    active-page="universities"
    :nav-items="companyMenu"
    :navigation-buttons="companyMenu"
  >
    <h1 class="text-3xl font-bold text-gray-900 mb-6">{{ t('company.universities.title') }}</h1>

    <div class="mb-6 flex flex-col rounded-3xl border border-border/80 bg-white/90 shadow-[0_14px_40px_rgba(11,26,48,0.08)] backdrop-blur-sm transition focus-within:ring-2 focus-within:ring-primary/20 sm:flex-row sm:items-center">
      <label class="flex flex-1 items-center gap-3 px-5 py-4 sm:border-r sm:border-border/80" for="universities-filter-name">
        <IconSearch class="h-5 w-5 shrink-0 text-additional" aria-hidden="true" />
        <input
          id="universities-filter-name"
          v-model="nameFilter"
          type="text"
          :placeholder="t('company.universities.filters.namePlaceholder')"
          :aria-label="t('company.universities.filters.name')"
          class="w-full border-none bg-transparent text-sm text-text placeholder:text-additional focus:outline-none focus:ring-0"
        >
      </label>

      <FilterSuggestField
        id="universities-filter-city"
        v-model="cityFilter"
        :icon="IconMapPin"
        :options="cityOptions"
        :placeholder="t('company.universities.filters.cityPlaceholder')"
        :aria-label="t('company.universities.filters.city')"
      />
    </div>

    <div v-if="universities.data.length > 0" class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
      <UniversityCard
        v-for="university in universities.data"
        :key="university.id"
        :university="university"
      />
    </div>

    <div v-else class="py-12 text-center text-slate-500">
      {{ t('company.universities.empty.title') }}
    </div>

    <Pagination :meta="universities" />
  </BaseLayout>
</template>
