<script setup>
import { ref, computed, watch } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import DataTable from '@/Components/Common/DataTable.vue'
import Pagination from '@/Components/Common/Pagination.vue'
import { Teleport } from 'vue'
import { useVerificationStatus } from '@/Composables/useVerificationStatus'

const props = defineProps({
  companies: {
    type: Object,
    default: () => ({ data: [], links: {}, meta: {} }),
  },
  universities: {
    type: Object,
    default: () => ({ data: [], links: {}, meta: {} }),
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

const acceptCompanyForm = useForm({ rejection_reason: '' })
const rejectCompanyForm = useForm({ rejection_reason: '' })
const acceptUniversityForm = useForm({ rejection_reason: '' })
const rejectUniversityForm = useForm({ rejection_reason: '' })

const showRejectModal = ref(false)
const itemToReject = ref(null)
const rejectReason = ref('')
const rejectError = ref('')

const currentItems = computed(() => {
  return entityType.value === 'company' ? props.companies.data : props.universities.data
})

function handleSort({ key, dir }) {
  sortKey.value = key
  sortDir.value = dir
  router.get('/admin/applications', {
    status: statusFilter.value,
    search: searchQuery.value,
    sort_key: key,
    sort_dir: dir,
  }, {
    preserveState: true,
    replace: true,
  })
}

watch(entityType, () => {
  sortKey.value = 'created_at'
  sortDir.value = 'asc'
})

watch([statusFilter, searchQuery], ([newStatus, newSearch]) => {
  router.get('/admin/applications', {
    status: newStatus,
    search: newSearch,
    sort_key: sortKey.value,
    sort_dir: sortDir.value,
  }, {
    preserveState: true,
    replace: true,
  })
}, { debounce: 300 })

const companyColumns = [
  { key: 'name', label: t('admin.verification.name'), sortable: true },
  { key: 'nip', label: t('admin.verification.nip') },
  { key: 'email', label: t('admin.verification.email'), sortable: true },
  { key: 'city', label: t('admin.verification.city'), sortable: true },
  { key: 'created_at', label: t('admin.verification.submittedAt'), sortable: true },
  { key: 'verification_status', label: t('table.status'), sortable: true },
  { key: 'actions', label: '', srLabel: t('admin.verification.actions'), align: 'right' },
]

const universityColumns = [
  { key: 'name', label: t('admin.verification.name'), sortable: true },
  { key: 'domain', label: t('admin.verification.domain'), sortable: true },
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

function openRejectModal(item) {
  itemToReject.value = item
  rejectReason.value = ''
  rejectError.value = ''
  showRejectModal.value = true
}

function closeRejectModal() {
  showRejectModal.value = false
  itemToReject.value = null
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
</script>

<template>
  <div class="space-y-6">
    <div class="flex lg:flex-row flex-col lg:justify-between lg:items-center gap-4">
      <div class="flex gap-2">
        <button
          :class="[
            'px-4 py-2 rounded-lg cursor-pointer font-medium text-sm transition',
            entityType === 'university' 
              ? 'bg-primary text-white hover:bg-primary/80 shadow-md shadow-primary/80 shadow-lg' 
              : 'bg-white/40 text-slate-700 hover:bg-white/60'
          ]"
          @click="entityType = 'university'"
        >
          {{ t('admin.verification.universities') }}
        </button>
        <button
          :class="[
            'px-4 py-2 rounded-lg cursor-pointer font-medium text-sm transition',
            entityType === 'company' 
              ? 'bg-primary text-white hover:bg-primary/80 shadow-md shadow-primary/80 shadow-lg' 
              : 'bg-white/40 text-slate-700 hover:bg-white/60'
          ]"
          @click="entityType = 'company'"
        >
          {{ t('admin.verification.companies') }}
        </button>
      </div>

      <div class="flex sm:flex-row flex-col gap-3">
        <select
          v-model="statusFilter"
          :aria-label="t('admin.verification.filterByStatusAriaLabel')"
          class="bg-white/40 px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/60 text-slate-700 text-sm"
        >
          <option value="all">{{ t('admin.verification.all') }}</option>
          <option value="pending">{{ t('admin.verification.pending') }}</option>
          <option value="verified">{{ t('admin.verification.verified') }}</option>
          <option value="rejected">{{ t('admin.verification.rejected') }}</option>
        </select>
        <div class="relative">
          <input
            v-model="searchQuery"
            type="text"
            :placeholder="t('admin.verification.search')"
            :aria-label="t('admin.verification.searchAriaLabel')"
            class="bg-white/40 px-4 py-2 pr-10 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/60 w-full text-slate-700 text-sm"
          >
          <button
            v-if="searchQuery"
            class="right-0 absolute inset-y-0 flex items-center pr-3 font-medium text-slate-400 hover:text-slate-600 text-xl"
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
      :columns="entityType === 'company' ? companyColumns : universityColumns"
      row-key="id"
      :caption="entityType === 'company' ? t('admin.verification.companies') : t('admin.verification.universities')"
      :sort-key="sortKey"
      :sort-dir="sortDir"
      @sort="handleSort"
    >
      <template #cell-email="{ item }">
        <a :href="`mailto:${item.email}`" class="hover:underline text-primary">{{ item.email }}</a>
      </template>
      <template #cell-phone="{ item }">
        <a :href="`tel:${item.phone}`" class="hover:underline text-primary">{{ item.phone }}</a>
      </template>
      <template #cell-city="{ item }">
        {{ item.city }}
      </template>
      <template #cell-created_at="{ item }">
        <span class="whitespace-nowrap">{{ formatDate(item.created_at) }}</span>
      </template>
      <template #cell-verification_status="{ item }">
        <span :class="['inline-flex rounded-full px-2.5 py-1 text-xs font-medium', statusClass(item.verification_status)]">
          {{ t(`admin.verification.${item.verification_status}`) }}
        </span>
      </template>
      <template #cell-actions="{ item }">
        <div class="flex justify-end gap-2">
          <button
            v-if="item.verification_status === 'pending'"
            :disabled="entityType === 'company' ? acceptCompanyForm.processing : acceptUniversityForm.processing"
            class="bg-green-600 cursor-pointer hover:bg-green-700 disabled:opacity-50 px-3 py-1.5 rounded-lg font-medium text-white text-white text-sm transition disabled:cursor-not-allowed"
            :aria-label="t('admin.verification.acceptAriaLabel', { name: item.name })"
            @click="entityType === 'company' ? acceptCompany(item) : acceptUniversity(item)"
          >
            {{ t('admin.verification.accept') }}
          </button>
          <button
            v-if="item.verification_status === 'pending'"
            :disabled="entityType === 'company' ? rejectCompanyForm.processing : rejectUniversityForm.processing"
            class="bg-red-500 cursor-pointer hover:bg-red-600 disabled:opacity-50 px-3 py-1.5 rounded-lg font-medium text-white text-sm transition disabled:cursor-not-allowed"
            :aria-label="t('admin.verification.rejectAriaLabel', { name: item.name })"
            @click="openRejectModal(item)"
          >
            {{ t('admin.verification.reject') }}
          </button>
        </div>
      </template>
    </DataTable>

    <Pagination
      :meta="entityType === 'company' ? companies : universities"
    />

    <div v-if="currentItems.length === 0" class="py-12 text-slate-500 text-center">
      {{ t('table.noData') }}
    </div>

    <Teleport to="body">
      <div
        v-if="showRejectModal && itemToReject"
        class="z-[9999] fixed inset-0 flex justify-center items-center bg-black/60 backdrop-blur-sm p-4"
        role="dialog"
        aria-modal="true"
        :aria-labelledby="'reject-modal-title'"
        @click.self="closeRejectModal"
      >
        <div class="bg-white shadow-2xl p-6 rounded-2xl w-full max-w-lg">
          <div class="flex justify-between items-center mb-4">
            <h3 id="reject-modal-title" class="font-semibold text-text text-xl">
              {{ t('admin.verification.rejectTitle') }}
            </h3>
            <button
              class="text-slate-400 hover:text-slate-600 text-3xl leading-none transition-colors"
              :aria-label="t('admin.verification.closeModal')"
              @click="closeRejectModal"
            >
              &times;
            </button>
          </div>
          
          <p class="mb-4 text-slate-600 text-sm">
            {{ t('admin.verification.rejectDescription', { name: itemToReject.name }) }}
          </p>

          <div class="space-y-2">
            <label for="rejectReasonInput" class="block font-medium text-slate-700 text-sm">
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
              :placeholder="t('admin.verification.rejectReasonPlaceholder')"
              :aria-invalid="!!rejectError"
              :aria-describedby="rejectError ? 'rejectErrorMsg' : undefined"
            />
            <p v-if="rejectError" id="rejectErrorMsg" class="text-red-500 text-sm">{{ rejectError }}</p>
          </div>

          <div class="flex sm:flex-row flex-col gap-3 mt-6">
            <button
              class="bg-slate-100 hover:bg-slate-200 px-5 py-2.5 rounded-xl font-medium text-slate-700 transition"
              @click="closeRejectModal"
            >
              {{ t('admin.verification.cancel') }}
            </button>
            <button
              class="flex-1 bg-red-500 hover:bg-red-600 disabled:opacity-50 px-5 py-2.5 rounded-xl font-medium text-white transition disabled:cursor-not-allowed"
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
