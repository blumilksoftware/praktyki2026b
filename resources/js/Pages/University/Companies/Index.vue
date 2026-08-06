<script setup>
import { ref, watch } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { IconHome, IconUser, IconSearch, IconMapPin, IconTag } from '@tabler/icons-vue'
import BaseLayout from '@/Components/Layouts/BaseLayout.vue'
import CompanyCard from '@/Components/University/CompanyCard.vue'
import FilterSuggestField from '@/Components/University/FilterSuggestField.vue'
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

const navItems = [
  { key: 'dashboard', label: 'Dashboard', href: '/university/dashboard', icon: IconHome },
  { key: 'profile', label: 'Profile', href: '/university/profile', icon: IconUser },
]

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
  <BaseLayout active-page="companies" :nav-items="navItems">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">{{ t('university.companies.title') }}</h1>

    <form
      class="mb-6 flex flex-col rounded-3xl border border-border/80 bg-white/90 shadow-[0_14px_40px_rgba(11,26,48,0.08)] backdrop-blur-sm sm:flex-row sm:items-center"
      @submit.prevent="search"
    >
      <label class="flex flex-1 items-center gap-3 px-5 py-4 sm:border-r sm:border-border/80" for="companies-filter-name">
        <IconSearch class="h-5 w-5 shrink-0 text-additional" aria-hidden="true" />
        <input
          id="companies-filter-name"
          v-model="nameFilter"
          type="text"
          :placeholder="t('university.companies.filters.namePlaceholder')"
          :aria-label="t('university.companies.filters.name')"
          class="w-full border-none bg-transparent text-sm text-text placeholder:text-additional focus:outline-none focus:ring-0"
        >
      </label>

      <FilterSuggestField
        id="companies-filter-city"
        v-model="cityFilter"
        class="sm:border-r sm:border-border/80"
        :icon="IconMapPin"
        :options="cityOptions"
        :placeholder="t('university.companies.filters.cityPlaceholder')"
        :aria-label="t('university.companies.filters.city')"
      />

      <FilterSuggestField
        id="companies-filter-tag"
        v-model="tagFilter"
        :icon="IconTag"
        :options="tagOptions"
        :placeholder="t('university.companies.filters.tagPlaceholder')"
        :aria-label="t('university.companies.filters.tag')"
      />

      <button
        type="submit"
        class="m-2 shrink-0 rounded-2xl bg-primary px-6 py-3 text-sm font-semibold text-white transition hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
      >
        {{ t('university.companies.filters.search') }}
      </button>
    </form>

    <div v-if="companies.data.length > 0" class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
      <CompanyCard
        v-for="company in companies.data"
        :key="company.id"
        :company="company"
      />
    </div>

    <div v-else class="py-12 text-center text-slate-500">
      {{ t('university.companies.empty.title') }}
    </div>

    <Pagination :meta="companies" />
  </BaseLayout>
</template>
