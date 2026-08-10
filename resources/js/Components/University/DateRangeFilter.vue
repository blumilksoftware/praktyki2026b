<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  filters: {
    type: Object,
    default: () => ({}),
  },
})

const { t } = useI18n()

const toDateStr = (date) => {
  const y = date.getFullYear()
  const m = String(date.getMonth() + 1).padStart(2, '0')
  const d = String(date.getDate()).padStart(2, '0')
  return `${y}-${m}-${d}`
}

const getCurrentMonthRange = () => {
  const now = new Date()
  const start = new Date(now.getFullYear(), now.getMonth(), 1)
  const end = new Date(now.getFullYear(), now.getMonth() + 1, 0)
  return { start: toDateStr(start), end: toDateStr(end) }
}

const defaultRange = getCurrentMonthRange()

const from = ref(props.filters.from || defaultRange.start)
const to = ref(props.filters.to || defaultRange.end)

watch(
  () => props.filters,
  (newFilters) => {
    from.value = newFilters.from || defaultRange.start
    to.value = newFilters.to || defaultRange.end
  },
  { deep: true },
)

const applyFilter = () => {
  router.get(
    window.location.pathname,
    {
      ...props.filters,
      from: from.value || undefined,
      to: to.value || undefined,
      fieldPage: 1,
    },
    {
      preserveState: true,
      preserveScroll: true,
    },
  )
}

const clearFilter = () => {
  from.value = defaultRange.start
  to.value = defaultRange.end
  router.get(
    window.location.pathname,
    {},
    {
      preserveState: true,
      preserveScroll: true,
    },
  )
}
</script>

<template>
  <div class="flex flex-wrap items-center gap-2">
    <div class="flex items-center gap-2">
      <input
        v-model="from"
        type="date"
        class="px-3 py-1.5 text-sm bg-background border border-border rounded-lg text-text focus:outline-none focus:ring-2 focus:ring-primary/50"
        :placeholder="t('common.filters.from')"
      >
      <span class="text-additional text-sm">—</span>
      <input
        v-model="to"
        type="date"
        :min="from"
        class="px-3 py-1.5 text-sm bg-background border border-border rounded-lg text-text focus:outline-none focus:ring-2 focus:ring-primary/50"
        :placeholder="t('common.filters.to')"
      >
    </div>
    <button
      type="button"
      class="px-3 py-1.5 text-sm font-semibold bg-primary/10 text-primary rounded-lg hover:bg-primary/20 transition-colors"
      @click="applyFilter"
    >
      {{ t('common.actions.applyFilter') }}
    </button>
    <button
      v-if="from !== defaultRange.start || to !== defaultRange.end"
      type="button"
      class="px-3 py-1.5 text-sm font-medium text-additional hover:text-text transition-colors"
      @click="clearFilter"
    >
      {{ t('common.actions.clear') }}
    </button>
  </div>
</template>
