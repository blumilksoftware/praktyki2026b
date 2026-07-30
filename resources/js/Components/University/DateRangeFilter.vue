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

const from = ref(props.filters.from || '')
const to = ref(props.filters.to || '')

watch(
  () => props.filters,
  (newFilters) => {
    from.value = newFilters.from || ''
    to.value = newFilters.to || ''
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
  from.value = ''
  to.value = ''
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
        :placeholder="t('university.dashboard.filter.from')"
      >
      <span class="text-additional text-sm">—</span>
      <input
        v-model="to"
        type="date"
        :min="from"
        class="px-3 py-1.5 text-sm bg-background border border-border rounded-lg text-text focus:outline-none focus:ring-2 focus:ring-primary/50"
        :placeholder="t('university.dashboard.filter.to')"
      >
    </div>

    <button
      type="button"
      class="px-3 py-1.5 text-sm font-semibold bg-primary/10 text-primary rounded-lg hover:bg-primary/20 transition-colors"
      @click="applyFilter"
    >
      {{ t('university.dashboard.filter.apply') }}
    </button>

    <button
      v-if="from || to"
      type="button"
      class="px-3 py-1.5 text-sm font-medium text-additional hover:text-text transition-colors"
      @click="clearFilter"
    >
      {{ t('university.dashboard.filter.reset') }}
    </button>
  </div>
</template>
