<script setup>
import { ref, watch } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { IconSearch, IconMapPin } from '@tabler/icons-vue'
import AppLayout from '@/Components/Layouts/AppLayout.vue'
import UniversityCard from '@/Components/Company/UniversityCard.vue'
import FilterSuggestField from '@/Components/Partnership/FilterSuggestField.vue'
import Pagination from '@/Components/Common/Pagination.vue'
import { useDebouncedSearch } from '@/Composables/useDebouncedSearch'
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

const statusFilterValues = {
  all: 'all',
  pendingIncoming: 'pending_incoming',
  pendingOutgoing: 'pending_outgoing',
  active: 'active',
}

const nameFilter = ref(props.filters.name || '')
const cityFilter = ref(props.filters.city || '')
const statusFilter = ref(
  Object.keys(statusFilterValues).find((key) => statusFilterValues[key] === props.filters.status) || 'all',
)

const statusTabs = ['all', 'active', 'pendingIncoming', 'pendingOutgoing']


function search() {
  router.get(ROUTES.COMPANY_UNIVERSITIES, {
    name: nameFilter.value,
    city: cityFilter.value,
    status: statusFilter.value === 'all' ? undefined : statusFilterValues[statusFilter.value],
  }, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  })
}

function selectStatusTab(tab) {
  statusFilter.value = tab
  search()
}

watch([nameFilter, cityFilter], useDebouncedSearch(search))
</script>

<template>
  <Head :title="t('company.universities.title')" />
  <AppLayout active-page="universities">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">{{ t('company.universities.title') }}</h1>

    <div class="mb-6 flex flex-col items-stretch gap-2 sm:flex-row sm:items-center">
      <div>
        <label for="universities-filter-name" class="mb-1 block text-xs font-medium text-additional">{{ t('company.universities.filters.name') }}</label>
        <div class="relative">
          <IconSearch class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-additional" aria-hidden="true" />
          <input
            id="universities-filter-name"
            v-model="nameFilter"
            type="text"
            :placeholder="t('company.universities.filters.namePlaceholder')"
            :aria-label="t('company.universities.filters.name')"
            class="w-full rounded-lg border border-border py-1.5 pl-8 pr-2 text-sm text-text placeholder:text-additional focus:outline-none focus:ring-2 focus:ring-primary/40 sm:w-56"
          >
        </div>
      </div>

      <FilterSuggestField
        id="universities-filter-city"
        v-model="cityFilter"
        :icon="IconMapPin"
        :options="cityOptions"
        :label="t('company.universities.filters.city')"
        :placeholder="t('company.universities.filters.cityPlaceholder')"
        :aria-label="t('company.universities.filters.city')"
      />
    </div>

    <div role="tablist" class="mb-6 flex gap-2 overflow-x-auto border-b border-border" :aria-label="t('company.universities.title')">
      <button
        v-for="tab in statusTabs"
        :key="tab"
        type="button"
        role="tab"
        :aria-selected="statusFilter === tab"
        class="shrink-0 cursor-pointer whitespace-nowrap border-b-2 px-3 py-2 text-sm font-semibold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30"
        :class="statusFilter === tab ? 'border-primary text-primary' : 'border-transparent text-additional hover:text-text'"
        @click="selectStatusTab(tab)"
      >
        {{ t(`company.universities.filters.status.${tab}`) }}
      </button>
    </div>

    <div v-if="universities.data.length > 0" class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
      <UniversityCard
        v-for="university in universities.data"
        :key="university.id"
        :university="university"
      />
    </div>

    <div v-else class="py-12 text-center text-additional">
      {{ t('company.universities.empty.title') }}
    </div>

    <Pagination :meta="universities" />
  </AppLayout>
</template>
