<script setup>
import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { IconSearch, IconBan } from '@tabler/icons-vue'
import DataTable from '@/Components/Common/DataTable.vue'
import Pagination from '@/Components/Common/Pagination.vue'
import FilterDropdown from '@/Components/Common/FilterDropdown.vue'
import AdminTakeDownOfferModal from '@/Components/Admin/AdminTakeDownOfferModal.vue'
import { useOfferStatus } from '@/Composables/useOfferStatus'
import { offerShow } from '@/Helpers/routes'

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
})

const { t } = useI18n()
const { statusClass } = useOfferStatus()

const statusFilter = ref(props.filters.status || 'all')
const searchQuery = ref(props.filters.search || '')

const statusFilterOptions = computed(() => [
  { value: 'all', label: t('admin.offers.all') },
  ...props.statuses.map(status => ({ value: status, label: t(`admin.offers.statuses.${status}`) })),
])

const columns = [
  { key: 'title', label: t('admin.offers.offer') },
  { key: 'company', label: t('admin.offers.company') },
  { key: 'city', label: t('admin.offers.city') },
  { key: 'status', label: t('admin.offers.status') },
  { key: 'actions', label: '', srLabel: t('admin.offers.actions'), align: 'right' },
]

const offerToTakeDown = ref(null)

function openTakeDownModal(offer) {
  offerToTakeDown.value = offer
}

function closeTakeDownModal() {
  offerToTakeDown.value = null
}

watch([statusFilter, searchQuery], ([newStatus, newSearch]) => {
  router.get('/admin/offers', {
    status: newStatus,
    search: newSearch,
  }, {
    preserveState: true,
    replace: true,
  })
}, { debounce: 300 })
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
          <IconSearch class="w-4 h-4 text-slate-400" />
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

    <DataTable
      v-if="offers.data.length > 0"
      :items="offers.data"
      :columns="columns"
      row-key="id"
      :caption="t('admin.offers.title')"
      :row-href="(item) => offerShow(item.id)"
    >
      <template #cell-title="{ item }">
        {{ item.title }}
      </template>
      <template #cell-company="{ item }">
        {{ item.company?.name || '-' }}
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
