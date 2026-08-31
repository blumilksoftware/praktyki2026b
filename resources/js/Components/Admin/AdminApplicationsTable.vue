<script setup>
import DataTable from '@/Components/Common/DataTable.vue'
import { useI18n } from 'vue-i18n'
import { useApplicationStatus } from '@/Composables/useApplicationStatus'

defineProps({
  applications: {
    type: Array,
    default: () => [],
  },
})

const { t } = useI18n()
const { statusClass, statusLabel } = useApplicationStatus()

const columns = [
  { key: 'student', label: t('table.student') },
  { key: 'company', label: t('table.company') },
  { key: 'status', label: t('table.status'), align: 'right' },
]
</script>

<template>
  <DataTable
    :items="applications"
    :columns="columns"
    row-key="id"
    card-title-key="student"
    :caption="t('admin.applications.caption')"
  >
    <template #cell-status="{ item }">
      <span :class="['inline-flex rounded-full px-2.5 py-1 text-xs font-medium', statusClass(item.status)]">
        {{ statusLabel(item.status) }}
      </span>
    </template>
  </DataTable>
</template>
