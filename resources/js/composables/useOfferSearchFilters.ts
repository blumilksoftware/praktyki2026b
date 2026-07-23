import { nextTick, ref, watch, type Ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { ROUTES } from '@/Helpers/routes'

export type WorkModeValue = 'onSite' | 'hybrid' | 'remote'

export type OfferSearchFilters = {
  study_field_ids?: string[]
  work_mode?: WorkModeValue | null
  date_from?: string | null
  date_to?: string | null
}

export type LocalOfferFilters = {
  studyFieldIds: string[]
  workMode: WorkModeValue | null
  dateFrom: string
  dateTo: string
}

function normalizeIncomingFilters(filters: OfferSearchFilters): LocalOfferFilters {
  return {
    studyFieldIds: [...(filters.study_field_ids ?? [])],
    workMode: filters.work_mode ?? null,
    dateFrom: filters.date_from ?? '',
    dateTo: filters.date_to ?? '',
  }
}

function buildQueryParams(local: LocalOfferFilters): Record<string, unknown> {
  const params: Record<string, unknown> = {}

  if (local.studyFieldIds.length > 0) {
    params.study_field_ids = local.studyFieldIds
  }

  if (local.workMode) {
    params.work_mode = local.workMode
  }

  if (local.dateFrom) {
    params.date_from = local.dateFrom
  }

  if (local.dateTo) {
    params.date_to = local.dateTo
  }

  return params
}

function isDateRangeValid(dateFrom: string, dateTo: string): boolean {
  if (!dateFrom || !dateTo) {
    return true
  }

  return dateTo >= dateFrom
}

export function useOfferSearchFilters(incomingFilters: Ref<OfferSearchFilters>) {
  const localFilters = ref<LocalOfferFilters>(normalizeIncomingFilters(incomingFilters.value))
  const dateRangeError = ref<string | null>(null)
  const isSyncingFromServer = ref(false)

  watch(incomingFilters, (next) => {
    isSyncingFromServer.value = true
    localFilters.value = normalizeIncomingFilters(next)
    dateRangeError.value = null
    nextTick(() => {
      isSyncingFromServer.value = false
    })
  }, { deep: true })

  watch(
    () => [localFilters.value.dateFrom, localFilters.value.dateTo],
    () => {
      if (isSyncingFromServer.value) {
        return
      }
      applyFilters()
    },
  )

  function applyFilters(): void {
    if (isSyncingFromServer.value) {
      return
    }

    if (!isDateRangeValid(localFilters.value.dateFrom, localFilters.value.dateTo)) {
      dateRangeError.value = 'offers.filters.dateRangeInvalid'
      return
    }

    dateRangeError.value = null

    router.get(
      ROUTES.OFFERS_SEARCH,
      buildQueryParams(localFilters.value),
      {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['offers', 'mapPoints', 'filters'],
      },
    )
  }

  function updateStudyFieldIds(ids: string[]): void {
    if (isSyncingFromServer.value) {
      return
    }
    localFilters.value.studyFieldIds = ids
    applyFilters()
  }

  function selectWorkMode(value: WorkModeValue | null): void {
    if (isSyncingFromServer.value) {
      return
    }
    localFilters.value.workMode = value
    applyFilters()
  }

  function clearFilters(): void {
    isSyncingFromServer.value = true
    localFilters.value = {
      studyFieldIds: [],
      workMode: null,
      dateFrom: '',
      dateTo: '',
    }
    dateRangeError.value = null

    router.get(
      ROUTES.OFFERS_SEARCH,
      {},
      {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['offers', 'mapPoints', 'filters'],
      },
    )

    nextTick(() => {
      isSyncingFromServer.value = false
    })
  }

  function hasActiveFilters(): boolean {
    return localFilters.value.studyFieldIds.length > 0
      || localFilters.value.workMode !== null
      || localFilters.value.dateFrom !== ''
      || localFilters.value.dateTo !== ''
  }

  return {
    localFilters,
    dateRangeError,
    updateStudyFieldIds,
    selectWorkMode,
    clearFilters,
    hasActiveFilters,
  }
}
