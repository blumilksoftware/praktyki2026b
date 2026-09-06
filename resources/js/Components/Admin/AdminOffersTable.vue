<script setup>
import { ref, computed, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { IconSearch, IconBan, IconX } from '@tabler/icons-vue'
import DataTable from '@/Components/Common/DataTable.vue'
import Pagination from '@/Components/Common/Pagination.vue'
import FilterDropdown from '@/Components/Common/FilterDropdown.vue'
import AdminTakeDownOfferModal from '@/Components/Admin/AdminTakeDownOfferModal.vue'
import { useOfferStatus } from '@/Composables/useOfferStatus'
import { useDebouncedSearch } from '@/Composables/useDebouncedSearch'
import { adminOffersForCompany, offerPreview } from '@/Helpers/routes'

const props = defineProps({
  offers: {
    type: Object,
    default: () => ({ data: [], links: {}, meta: {} }),
  },
  filters: {
    type: Object,
    default: () => ({ status: 'all', search: '' }),
  },
  statuses: {
    type: Array,
    default: () => [],
  },
  filterCompany: {
    type: Object,
    default: null,
  },
})

const { t } = useI18n()
const { statusClass } = useOfferStatus()

const statusFilter = ref(props.filters.status || 'all')
const searchQuery = ref(props.filters.search || '')
const companyFilter = ref(props.filters.company || '')

const statusFilterOptions = computed(() => [
  { value: 'all', label: t('admin.offers.all') },
  ...props.statuses.map(status => ({ value: status, label: t(`admin.offers.statuses.${status}`) })),
])

const sortKey = ref(props.filters.sort_key || 'created_at')
const sortDir = ref(props.filters.sort_dir || 'desc')

const columns = [
  { key: 'title', label: t('admin.offers.offer'), sortable: true },
  { key: 'company', label: t('admin.offers.company'), sortable: true },
  { key: 'city', label: t('admin.offers.city'), sortable: true },
  { key: 'status', label: t('admin.offers.status'), sortable: true },
  { key: 'actions', label: '', srLabel: t('admin.offers.actions'), align: 'right' },
]

const offerToTakeDown = ref(null)

function openTakeDownModal(offer) {
  offerToTakeDown.value = offer
}

function closeTakeDownModal() {
  offerToTakeDown.value = null
}

function applyQuery() {
  router.get('/admin/offers', {
    status: statusFilter.value,
    search: searchQuery.value,
    company: companyFilter.value,
    sort_key: sortKey.value,
    sort_dir: sortDir.value,
  }, {
    preserveState: true,
    replace: true,
  })
}

function clearCompanyFilter() {
  companyFilter.value = ''
  applyQuery()
}

function handleSort({ key, dir }) {
  sortKey.value = key
  sortDir.value = dir
  applyQuery()
}

watch([statusFilter, searchQuery], useDebouncedSearch(applyQuery))
</script>

<template>
  <div class="space-y-6">
    <div class="flex lg:flex-row flex-col lg:justify-between lg:items-center gap-4">
      <FilterDropdown
        v-model="statusFilter"
        :options="statusFilterOptions"
        :aria-label="t('admin.offers.filterByStatusAriaLabel')"
      />

      <div class="relative">
        <div class="left-3 absolute inset-y-0 flex items-center pointer-events-none">
          <IconSearch class="w-4 h-4 text-slate-400" aria-hidden="true" />
        </div>
        <input
          v-model="searchQuery"
          type="text"
          :placeholder="t('admin.offers.search')"
          :aria-label="t('admin.offers.searchAriaLabel')"
          class="bg-white px-4 py-2 pr-10 pl-9 border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/60 w-full text-slate-700 text-sm"
        >
      </div>
    </div>

    <div v-if="filterCompany" class="flex flex-wrap items-center gap-2">
      <span class="inline-flex items-center gap-2 bg-primary/10 px-3 py-1 rounded-full font-medium text-primary text-sm">
        {{ t('admin.offers.filteredByCompany', { company: filterCompany.name }) }}
        <button
          type="button"
          class="hover:bg-primary/20 p-0.5 rounded-full cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
          :aria-label="t('admin.offers.clearCompanyFilter')"
          @click="clearCompanyFilter"
        >
          <IconX class="w-3.5 h-3.5" aria-hidden="true" />
        </button>
      </span>
    </div>

    <DataTable
      v-if="offers.data.length > 0"
      :items="offers.data"
      :columns="columns"
      row-key="id"
      card-title-key="title"
      :caption="t('admin.offers.title')"
      :row-href="(item) => offerPreview(item.id)"
      :sort-key="sortKey"
      :sort-dir="sortDir"
      @sort="handleSort"
    >
      <template #cell-company="{ item }">
        <Link
          v-if="item.company"
          :href="adminOffersForCompany(item.company.id)"
          class="rounded text-primary hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
          :aria-label="t('admin.offers.filterByCompanyAriaLabel', { company: item.company.name })"
        >
          {{ item.company.name }}
        </Link>
        <template v-else>-</template>
      </template>
      <template #cell-city="{ item }">
        {{ item.city || '-' }}
      </template>
      <template #cell-status="{ item }">
        <span :class="['inline-flex px-2.5 py-1 rounded-full font-medium text-xs', statusClass(item.status)]">
          {{ t(`admin.offers.statuses.${item.status}`) }}
        </span>
      </template>
      <template #cell-actions="{ item }">
        <button
          v-if="item.status === 'published'"
          type="button"
          class="p-1.5 rounded-md text-red-600 hover:bg-red-50 cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
          :title="t('admin.offers.takeDown')"
          :aria-label="t('admin.offers.takeDownAriaLabel', { title: item.title })"
          @click="openTakeDownModal(item)"
        >
          <IconBan class="w-4 h-4" aria-hidden="true" />
        </button>
      </template>
    </DataTable>

    <Pagination :meta="offers" />

    <div v-if="offers.data.length === 0" class="py-12 text-slate-500 text-center">
      {{ t('table.noData') }}
    </div>

    <AdminTakeDownOfferModal
      :key="offerToTakeDown?.id"
      :open="!!offerToTakeDown"
      :offer-id="offerToTakeDown?.id"
      :offer-title="offerToTakeDown?.title"
      @close="closeTakeDownModal"
    />
  </div>
</template>
