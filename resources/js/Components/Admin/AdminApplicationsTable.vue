<script setup>
import DataTable from '@/Components/Common/DataTable.vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  applications: {
    type: Array,
    required: true,
  },
})

const { t } = useI18n()

function statusClass(status) {
  switch ((status || '').toLowerCase()) {
    case 'accepted':
      return 'bg-green-100 text-green-800'
    case 'rejected':
      return 'bg-red-100 text-red-800'
    case 'pending':
      return 'bg-yellow-100 text-yellow-800'
    default:
      return 'bg-slate-100 text-slate-800'
  }
}

const columns = [
  { key: 'id', label: t('table.id') },
  { key: 'student', label: t('table.student') },
  { key: 'company', label: t('table.company') },
  { key: 'status', label: t('table.status'), align: 'right' },
]
</script>

<template>
  <DataTable :items="applications" :columns="columns" rowKey="id" :caption="t('admin.applications.caption')">
    <template #cell-status="{ item }">
      <span :class="['inline-flex rounded-full px-2.5 py-1 text-xs font-medium', statusClass(item.status)]">
        {{ item.status }}
      </span>
    </template>
  </DataTable>
</template>
