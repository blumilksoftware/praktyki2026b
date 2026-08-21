import { ref, watch, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import {
  IconSelector,
  IconChevronUp,
  IconChevronDown,
} from '@tabler/icons-vue'

export function isDateRangeInvalid(dateFrom, dateTo) {
  return !!(dateFrom && dateTo && dateTo < dateFrom)
}

export function useOffersFilters(initial) {
  const searchQuery = ref(initial.search)
  const statusFilter = ref(initial.status)
  const sortColumn = ref(initial.sort)
  const sortDirection = ref(initial.direction)

  let searchDebounceTimer = null

  function applyQuery(overrides = {}) {
    router.get(
      window.location.pathname,
      {
        sort: sortColumn.value,
        direction: sortDirection.value,
        search: searchQuery.value || undefined,
        status: statusFilter.value || undefined,
        ...overrides,
      },
      { preserveScroll: true, replace: true },
    )
  }

  watch(searchQuery, () => {
    clearTimeout(searchDebounceTimer)
    searchDebounceTimer = setTimeout(() => {
      applyQuery({ page: undefined })
    }, 350)
  })

  function clearSearch() {
    searchQuery.value = ''
  }

  function onStatusFilterChange() {
    applyQuery({ page: undefined })
  }

  function sortBy(column) {
    const newDirection = sortColumn.value === column && sortDirection.value === 'asc' ? 'desc' : 'asc'
    sortColumn.value = column
    sortDirection.value = newDirection
    applyQuery({ sort: column, direction: newDirection, page: undefined })
  }

  function sortIcon(column) {
    if (sortColumn.value !== column) return IconSelector
    return sortDirection.value === 'asc' ? IconChevronUp : IconChevronDown
  }

  onUnmounted(() => {
    clearTimeout(searchDebounceTimer)
  })

  return {
    searchQuery,
    statusFilter,
    sortColumn,
    sortDirection,
    applyQuery,
    clearSearch,
    onStatusFilterChange,
    sortBy,
    sortIcon,
  }
}
