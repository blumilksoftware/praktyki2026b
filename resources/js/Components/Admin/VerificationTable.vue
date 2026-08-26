<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { IconSearch } from '@tabler/icons-vue'
import DataTable from '@/Components/Common/DataTable.vue'
import Pagination from '@/Components/Common/Pagination.vue'
import FilterDropdown from '@/Components/Common/FilterDropdown.vue'
import VerificationActionsMenu from '@/Components/Admin/VerificationActionsMenu.vue'
import AdminDeleteOrganizationModal from '@/Components/Admin/AdminDeleteOrganizationModal.vue'
import ProfilePageCard from '@/Components/Profile/ProfilePageCard.vue'
import { Teleport } from 'vue'
import { useVerificationStatus } from '@/Composables/useVerificationStatus'
import { useDebouncedSearch } from '@/Composables/useDebouncedSearch'
import { companyShow, universityShow } from '@/Helpers/routes'

const props = defineProps({
  companies: {
    type: Object,
    default: () => ({ data: [], links: {}, meta: {} }),
  },
  universities: {
    type: Object,
    default: () => ({ data: [], links: {}, meta: {} }),
  },
  companyStats: {
    type: Object,
    default: () => ({ pending: 0, verified: 0, rejected: 0 }),
  },
  universityStats: {
    type: Object,
    default: () => ({ pending: 0, verified: 0, rejected: 0 }),
  },
  filters: {
    type: Object,
    default: () => ({ status: 'all', search: '', sort_key: 'created_at', sort_dir: 'asc' }),
  },
})

const { t } = useI18n()
const { statusClass } = useVerificationStatus()

const entityType = ref('university')
const statusFilter = ref(props.filters.status || 'all')
const searchQuery = ref(props.filters.search || '')
const sortKey = ref(props.filters.sort_key || 'created_at')
const sortDir = ref(props.filters.sort_dir || 'asc')

const statusFilterOptions = computed(() => [
  { value: 'all', label: t('admin.verification.all') },
  { value: 'pending', label: t('admin.verification.pending') },
  { value: 'verified', label: t('admin.verification.verified') },
  { value: 'rejected', label: t('admin.verification.rejected') },
])

const acceptCompanyForm = useForm({ rejection_reason: '' })
const rejectCompanyForm = useForm({ rejection_reason: '' })
const acceptUniversityForm = useForm({ rejection_reason: '' })
const rejectUniversityForm = useForm({ rejection_reason: '' })

const showRejectModal = ref(false)
const itemToReject = ref(null)
const rejectReason = ref('')
const rejectError = ref('')
const rejectModalRef = ref(null)
const rejectTriggerRef = ref(null)

const currentItems = computed(() => {
  return entityType.value === 'company' ? props.companies.data : props.universities.data
})

const currentStats = computed(() => {
  const source = entityType.value === 'company' ? props.companyStats : props.universityStats
  return [
    { label: t('admin.verification.pending'), value: source.pending },
    { label: t('admin.verification.verified'), value: source.verified },
    { label: t('admin.verification.rejected'), value: source.rejected },
  ]
})

const itemToDelete = ref(null)

function openDeleteModal(item) {
  itemToDelete.value = item
}

function closeDeleteModal() {
  itemToDelete.value = null
}

function rowHref(item) {
  return entityType.value === 'company' ? companyShow(item.id) : universityShow(item.id)
}

function applyQuery() {
  router.get('/admin/applications', {
    status: statusFilter.value,
    search: searchQuery.value,
    sort_key: sortKey.value,
    sort_dir: sortDir.value,
  }, {
    preserveState: true,
    replace: true,
  })
}

function handleSort({ key, dir }) {
  sortKey.value = key
  sortDir.value = dir
  applyQuery()
}

watch(entityType, () => {
  sortKey.value = 'created_at'
  sortDir.value = 'asc'
})

watch([statusFilter, searchQuery], useDebouncedSearch(applyQuery))

const columns = [
  { key: 'name', label: t('admin.verification.name'), sortable: true },
  { key: 'city', label: t('admin.verification.city'), sortable: true },
  { key: 'email', label: t('admin.verification.email'), sortable: true },
  { key: 'phone', label: t('admin.verification.phone') },
  { key: 'created_at', label: t('admin.verification.submittedAt'), sortable: true },
  { key: 'verification_status', label: t('table.status'), sortable: true },
  { key: 'actions', label: '', srLabel: t('admin.verification.actions'), align: 'right' },
]

function acceptCompany(company) {
  acceptCompanyForm
    .transform(data => ({
      ...data,
      companies_page: props.companies.current_page,
      universities_page: props.universities.current_page,
      sort_key: sortKey.value,
      sort_dir: sortDir.value,
    }))
    .post(`/admin/verify/company/${company.id}/accept`, {
      preserveState: true,
      preserveScroll: true,
    })
}

function acceptUniversity(university) {
  acceptUniversityForm
    .transform(data => ({
      ...data,
      companies_page: props.companies.current_page,
      universities_page: props.universities.current_page,
      sort_key: sortKey.value,
      sort_dir: sortDir.value,
    }))
    .post(`/admin/verify/university/${university.id}/accept`, {
      preserveState: true,
      preserveScroll: true,
    })
}

function openRejectModal(item, event) {
  rejectTriggerRef.value = event?.target || document.activeElement
  itemToReject.value = item
  rejectReason.value = ''
  rejectError.value = ''
  showRejectModal.value = true
  nextTick(() => {
    const modal = rejectModalRef.value
    if (modal) {
      const focusableElements = modal.querySelectorAll(
        'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])',
      )
      if (focusableElements.length > 0) {
        focusableElements[0].focus()
      }
    }
  })
}

function closeRejectModal() {
  showRejectModal.value = false
  itemToReject.value = null
  nextTick(() => {
    if (rejectTriggerRef.value) {
      rejectTriggerRef.value.focus()
    }
  })
}

function handleEscapeKey(event) {
  if (event.key === 'Escape' && showRejectModal.value) {
    closeRejectModal()
  }
}

function handleTabKey(event) {
  const modalRef = rejectModalRef.value
  if (!modalRef || !showRejectModal.value) return

  const focusableElements = modalRef.querySelectorAll(
    'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])',
  )
  const firstElement = focusableElements[0]
  const lastElement = focusableElements[focusableElements.length - 1]

  if (event.key === 'Tab') {
    if (event.shiftKey) {
      if (document.activeElement === firstElement) {
        event.preventDefault()
        lastElement.focus()
      }
    } else {
      if (document.activeElement === lastElement) {
        event.preventDefault()
        firstElement.focus()
      }
    }
  }
}

function submitReject() {
  if (!rejectReason.value.trim()) {
    rejectError.value = t('admin.verification.rejectReasonRequired')
    return
  }

  const sharedTransform = data => ({
    ...data,
    companies_page: props.companies.current_page,
    universities_page: props.universities.current_page,
    sort_key: sortKey.value,
    sort_dir: sortDir.value,
  })

  if (entityType.value === 'company') {
    rejectCompanyForm.rejection_reason = rejectReason.value.trim()
    rejectCompanyForm
      .transform(sharedTransform)
      .post(`/admin/verify/company/${itemToReject.value.id}/reject`, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => closeRejectModal(),
      })
  } else {
    rejectUniversityForm.rejection_reason = rejectReason.value.trim()
    rejectUniversityForm
      .transform(sharedTransform)
      .post(`/admin/verify/university/${itemToReject.value.id}/reject`, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => closeRejectModal(),
      })
  }
}

function formatDate(dateString) {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('pl-PL', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  })
}

onMounted(() => {
  window.addEventListener('keydown', handleEscapeKey)
  window.addEventListener('keydown', handleTabKey)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleEscapeKey)
  window.removeEventListener('keydown', handleTabKey)
})
</script>

<template>
  <div class="space-y-6">
    <section class="gap-4 grid grid-cols-1 sm:grid-cols-3">
      <ProfilePageCard v-for="stat in currentStats" :key="stat.label" centered>
        <div class="font-medium text-additional text-sm">{{ stat.label }}</div>
        <div class="mt-2 font-bold text-text text-3xl">{{ stat.value }}</div>
      </ProfilePageCard>
    </section>

    <div class="flex lg:flex-row flex-col lg:justify-between lg:items-center gap-4">
      <div class="flex items-center gap-1 bg-background p-1 rounded-lg border border-border self-start">
        <button
          type="button"
          :class="[
            'px-3 py-1.5 rounded-md cursor-pointer font-medium text-sm transition-all',
            entityType === 'university'
              ? 'bg-primary/10 text-primary font-semibold shadow-xs'
              : 'text-additional hover:text-text hover:bg-background/60'
          ]"
          @click="entityType = 'university'"
        >
          {{ t('admin.verification.universities') }}
        </button>
        <button
          type="button"
          :class="[
            'px-3 py-1.5 rounded-md cursor-pointer font-medium text-sm transition-all',
            entityType === 'company'
              ? 'bg-primary/10 text-primary font-semibold shadow-xs'
              : 'text-additional hover:text-text hover:bg-background/60'
          ]"
          @click="entityType = 'company'"
        >
          {{ t('admin.verification.companies') }}
        </button>
      </div>

      <div class="flex sm:flex-row flex-col gap-3">
        <FilterDropdown
          v-model="statusFilter"
          :options="statusFilterOptions"
          :aria-label="t('admin.verification.filterByStatusAriaLabel')"
        />
        <div class="relative">
          <div class="left-3 absolute inset-y-0 flex items-center pointer-events-none">
            <IconSearch class="w-4 h-4 text-additional" />
          </div>
          <input
            v-model="searchQuery"
            type="text"
            :placeholder="t('admin.verification.search')"
            :aria-label="t('admin.verification.searchAriaLabel')"
            class="bg-white px-4 py-2 pr-10 pl-9 border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/60 w-full text-text text-sm"
          >
          <button
            v-if="searchQuery"
            class="right-0 absolute inset-y-0 flex items-center pr-3 font-medium text-additional hover:text-text text-xl"
            @click="searchQuery = ''"
          >
            &times;
          </button>
        </div>
      </div>
    </div>

    <DataTable
      v-if="currentItems.length > 0"
      :items="currentItems"
      :columns="columns"
      row-key="id"
      :caption="entityType === 'company' ? t('admin.verification.companies') : t('admin.verification.universities')"
      :sort-key="sortKey"
      :sort-dir="sortDir"
      :row-href="rowHref"
      @sort="handleSort"
    >
      <template #cell-email="{ item }">
        <a :href="`mailto:${item.email}`" class="text-primary hover:underline">{{ item.email }}</a>
      </template>
      <template #cell-phone="{ item }">
        <a :href="`tel:${item.phone}`" class="text-primary hover:underline">{{ item.phone }}</a>
      </template>
      <template #cell-city="{ item }">
        {{ item.city }}
      </template>
      <template #cell-created_at="{ item }">
        <span class="whitespace-nowrap">{{ formatDate(item.created_at) }}</span>
      </template>
      <template #cell-verification_status="{ item }">
        <div class="flex items-center gap-2">
          <span :class="['inline-flex rounded-full px-2.5 py-1 text-xs font-medium', statusClass(item.verification_status)]">
            {{ t(`admin.verification.${item.verification_status}`) }}
          </span>
          <span
            v-if="item.verification_status === 'rejected' && item.rejection_reason"
            class="group relative cursor-help"
          >
            <span class="inline-flex justify-center items-center bg-red-100 rounded-full w-4 h-4 font-bold text-red-500 text-xs">?</span>
            <div class="bottom-full left-1/2 z-10 absolute bg-slate-800 opacity-0 group-hover:opacity-100 shadow-lg mb-2 px-3 py-2 rounded-lg w-56 text-white text-xs transition-opacity -translate-x-1/2 pointer-events-none">
              <p class="mb-1 font-medium">{{ t('admin.verification.rejectReason') }}</p>
              <p class="text-slate-300">{{ item.rejection_reason }}</p>
            </div>
          </span>
        </div>
      </template>
      <template #cell-actions="{ item }">
        <VerificationActionsMenu
          :item="item"
          :processing="entityType === 'company'
            ? (acceptCompanyForm.processing || rejectCompanyForm.processing)
            : (acceptUniversityForm.processing || rejectUniversityForm.processing)"
          @accept="entityType === 'company' ? acceptCompany(item) : acceptUniversity(item)"
          @reject="openRejectModal(item, $event)"
          @delete="openDeleteModal(item)"
        />
      </template>
    </DataTable>

    <Pagination
      :meta="entityType === 'company' ? companies : universities"
    />

    <div v-if="currentItems.length === 0" class="py-12 text-additional text-center">
      {{ t('table.noData') }}
    </div>

    <AdminDeleteOrganizationModal
      :key="itemToDelete?.id"
      :open="!!itemToDelete"
      :organization-id="itemToDelete?.id"
      :organization-name="itemToDelete?.name"
      :entity-type="entityType"
      @close="closeDeleteModal"
    />

    <Teleport to="body">
      <div
        v-if="showRejectModal && itemToReject"
        class="z-[9999] fixed inset-0 flex justify-center items-center bg-black/60 backdrop-blur-sm p-4"
        role="dialog"
        aria-modal="true"
        :aria-labelledby="'reject-modal-title'"
        @click.self="closeRejectModal"
      >
        <div ref="rejectModalRef" class="bg-white shadow-2xl p-6 rounded-2xl w-full max-w-lg">
          <h3 id="reject-modal-title" class="mb-4 font-semibold text-text text-xl">
            {{ t('admin.verification.rejectTitle') }}
          </h3>
          
          <p class="mb-4 text-additional text-sm">
            {{ t('admin.verification.rejectDescription', { name: itemToReject.name }) }}
          </p>

          <div class="space-y-2">
            <label for="rejectReasonInput" class="block font-medium text-text text-sm">
              {{ t('admin.verification.rejectReason') }}
            </label>
            <textarea
              id="rejectReasonInput"
              v-model="rejectReason"
              rows="4"
              class="bg-slate-50 p-3 border rounded-xl focus:outline-none focus:ring-2 w-full transition-all resize-none"
              :class="rejectError 
                ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' 
                : 'border-slate-300 focus:border-primary focus:ring-primary/20' "
              :aria-invalid="!!rejectError"
              :aria-describedby="rejectError ? 'rejectErrorMsg' : undefined"
            />
            <p v-if="rejectError" id="rejectErrorMsg" class="text-red-500 text-sm">{{ rejectError }}</p>
          </div>

          <div class="flex sm:flex-row flex-col gap-3 mt-6">
            <button
              class="bg-slate-100 hover:bg-slate-200 px-5 py-2.5 rounded-xl font-medium text-text transition"
              @click="closeRejectModal"
            >
              {{ t('admin.verification.cancel') }}
            </button>
            <button
              class="flex-1 bg-red-500 hover:bg-red-600 disabled:opacity-50 px-5 py-2.5 rounded-xl font-medium text-white transition cursor-pointer disabled:cursor-not-allowed"
              :disabled="entityType === 'company' ? rejectCompanyForm.processing : rejectUniversityForm.processing"
              @click="submitReject"
            >
              {{ t('admin.verification.confirmReject') }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>
