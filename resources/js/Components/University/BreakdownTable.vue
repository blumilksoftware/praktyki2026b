<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  rows: {
    type: Object,
    default: () => ({ data: [], links: [], current_page: 1, last_page: 1, total: 0, from: 0, to: 0 }),
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
  paramPrefix: {
    type: String,
    required: true,
  },
  nameKey: {
    type: String,
    required: true,
  },
  idKey: {
    type: String,
    required: true,
  },
  nameLabel: {
    type: String,
    required: true,
  },
  searchPlaceholder: {
    type: String,
    required: true,
  },
})

const { t } = useI18n()

const pageParam = `${props.paramPrefix}Page`
const searchParam = `${props.paramPrefix}Search`
const sortParam = `${props.paramPrefix}Sort`
const directionParam = `${props.paramPrefix}Direction`

const searchQuery = ref(props.filters[searchParam] || '')
const currentSort = ref(props.filters[sortParam] || props.nameKey)
const currentDirection = ref(props.filters[directionParam] || 'asc')

let searchTimeout = null

const columns = [
  { key: props.nameKey, label: props.nameLabel, align: 'left' },
  { key: 'linkedStudents', label: t('university.dashboard.breakdown.students'), align: 'right' },
  { key: 'applicationsSubmitted', label: t('university.dashboard.breakdown.applications'), align: 'right' },
  { key: 'acceptedPlacements', label: t('university.dashboard.breakdown.accepted'), align: 'right' },
]

const visit = (params = {}) => {
  router.get(
    window.location.pathname,
    {
      ...props.filters,
      ...params,
    },
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    },
  )
}

watch(searchQuery, (value) => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    visit({
      [searchParam]: value || undefined,
      [pageParam]: 1,
    })
  }, 400)
})

const toggleSort = (column) => {
  const direction = currentSort.value === column && currentDirection.value === 'asc' ? 'desc' : 'asc'

  currentSort.value = column
  currentDirection.value = direction

  visit({
    [sortParam]: column,
    [directionParam]: direction,
    [pageParam]: 1,
  })
}

const changePage = (page) => {
  if (page < 1 || page > props.rows.last_page || page === props.rows.current_page) {
    return
  }

  visit({ [pageParam]: page })
}
</script>

<template>
  <div class="space-y-4">
    <div class="flex justify-end">
      <div class="relative w-full sm:w-64">
        <svg
          class="w-3.5 h-3.5 absolute left-2.5 top-1/2 -translate-y-1/2 text-additional pointer-events-none"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"
          />
        </svg>
        <input
          v-model="searchQuery"
          type="text"
          :placeholder="searchPlaceholder"
          class="w-full text-sm border border-border rounded-md px-3 py-1.5 pl-8 bg-background text-text placeholder:text-additional focus:outline-none focus:ring-1 focus:ring-primary"
        >
      </div>
    </div>

    <div v-if="!rows.data || !rows.data.length" class="text-center py-8 text-additional text-sm">
      {{ t('university.dashboard.breakdown.empty') }}
    </div>

    <template v-else>
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-text">
          <thead class="bg-background text-additional border-b border-border">
            <tr>
              <th
                v-for="column in columns"
                :key="column.key"
                class="py-3 px-4 font-medium select-none cursor-pointer hover:text-text transition-colors"
                :class="column.align === 'right' ? 'text-right' : ''"
                @click="toggleSort(column.key)"
              >
                <span
                  class="inline-flex items-center gap-1"
                  :class="column.align === 'right' ? 'justify-end' : ''"
                >
                  {{ column.label }}
                  <span v-if="currentSort === column.key" class="text-primary text-[10px]">
                    {{ currentDirection === 'asc' ? '▲' : '▼' }}
                  </span>
                </span>
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border">
            <tr v-for="item in rows.data" :key="item[idKey] ?? item[nameKey]" class="hover:bg-background/50">
              <td class="py-3 px-4 font-medium">
                {{ item[nameKey] || t('university.dashboard.breakdown.unknown') }}
              </td>
              <td class="py-3 px-4 text-right">{{ item.linkedStudents }}</td>
              <td class="py-3 px-4 text-right">{{ item.applicationsSubmitted }}</td>
              <td class="py-3 px-4 text-right font-medium text-primary">{{ item.acceptedPlacements }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="rows.last_page > 1" class="flex items-center justify-between pt-4 border-t border-border">
        <span class="text-xs text-additional">
          {{ t('university.dashboard.breakdown.showingResults', {
            from: rows.from || 1,
            to: rows.to || rows.data.length,
            total: rows.total
          }) }}
        </span>

        <div class="flex items-center gap-2">
          <button
            type="button"
            :disabled="rows.current_page === 1"
            class="px-3 py-1 text-xs border border-border rounded-md hover:bg-background disabled:opacity-50 disabled:cursor-not-allowed"
            @click="changePage(rows.current_page - 1)"
          >
            &larr;
          </button>
          <span class="text-xs font-medium px-2">
            {{ rows.current_page }} / {{ rows.last_page }}
          </span>
          <button
            type="button"
            :disabled="rows.current_page === rows.last_page"
            class="px-3 py-1 text-xs border border-border rounded-md hover:bg-background disabled:opacity-50 disabled:cursor-not-allowed"
            @click="changePage(rows.current_page + 1)"
          >
            &rarr;
          </button>
        </div>
      </div>
    </template>
  </div>
</template>
