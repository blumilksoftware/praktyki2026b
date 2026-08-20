<script setup>
import { ref, watch } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { IconSearch, IconMapPin, IconTag } from '@tabler/icons-vue'
import UniversityLayout from '@/Components/Layouts/UniversityLayout.vue'
import CompanyCard from '@/Components/University/CompanyCard.vue'
import FilterSuggestField from '@/Components/Partnership/FilterSuggestField.vue'
import Pagination from '@/Components/Common/Pagination.vue'
import { ROUTES } from '@/Helpers/routes'

const { t } = useI18n()

const props = defineProps({
  companies: {
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
  tagOptions: {
    type: Array,
    default: () => [],
  },
})

const nameFilter = ref(props.filters.name || '')
const cityFilter = ref(props.filters.city || '')
const tagFilter = ref(props.filters.tag || '')

function search() {
  router.get(ROUTES.UNIVERSITY_COMPANIES, {
    name: nameFilter.value,
    city: cityFilter.value,
    tag: tagFilter.value,
  }, {
    preserveState: true,
    replace: true,
  })
}

watch([nameFilter, cityFilter, tagFilter], search, { debounce: 300 })
</script>

<template>
  <Head :title="t('university.companies.title')" />
  <UniversityLayout active-page="companies">
    <h1 class="text-3xl font-bold text-text mb-6">{{ t('university.companies.title') }}</h1>

    <div class="mb-6 flex flex-col items-stretch gap-2 sm:flex-row sm:items-center">
      <div>
        <label for="companies-filter-name" class="mb-1 block text-xs font-medium text-additional">{{ t('university.companies.filters.name') }}</label>
        <div class="relative">
          <IconSearch class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-additional" aria-hidden="true" />
          <input
            id="companies-filter-name"
            v-model="nameFilter"
            type="text"
            :placeholder="t('university.companies.filters.namePlaceholder')"
            :aria-label="t('university.companies.filters.name')"
            class="w-full rounded-lg border border-border py-1.5 pl-8 pr-2 text-sm text-text placeholder:text-additional focus:outline-none focus:ring-2 focus:ring-primary/40 sm:w-56"
          >
        </div>
      </div>

      <FilterSuggestField
        id="companies-filter-city"
        v-model="cityFilter"
        :icon="IconMapPin"
        :options="cityOptions"
        :label="t('university.companies.filters.city')"
        :placeholder="t('university.companies.filters.cityPlaceholder')"
        :aria-label="t('university.companies.filters.city')"
      />

      <FilterSuggestField
        id="companies-filter-tag"
        v-model="tagFilter"
        :icon="IconTag"
        :options="tagOptions"
        :label="t('university.companies.filters.tag')"
        :placeholder="t('university.companies.filters.tagPlaceholder')"
        :aria-label="t('university.companies.filters.tag')"
      />
    </div>

    <div v-if="companies.data.length > 0" class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
      <CompanyCard
        v-for="company in companies.data"
        :key="company.id"
        :company="company"
      />
    </div>

    <div v-else class="py-12 text-center text-additional">
      {{ t('university.companies.empty.title') }}
    </div>

    <Pagination :meta="companies" />
  </UniversityLayout>
</template>
