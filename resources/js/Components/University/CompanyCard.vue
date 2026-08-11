<script setup>
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import PartnerConfirmModal from '@/Components/Partnership/PartnerConfirmModal.vue'
import { usePartnershipStatus } from '@/Composables/usePartnershipStatus'
import { universityCompanyPartnership, universityCompanyPartnershipAccept } from '@/Helpers/routes'

const NAMESPACE = 'university.companies'

const props = defineProps({
  company: { type: Object, required: true },
})

const { t } = useI18n()
const { statusClass, statusLabel } = usePartnershipStatus(NAMESPACE)

const companyInitial = computed(() => props.company.name?.charAt(0) || '?')

const isProcessing = ref(false)
const isConfirmModalOpen = ref(false)
const modalAction = ref('propose')

const status = computed(() => props.company.partnership_status)
const isIncoming = computed(() => status.value === 'pending_incoming')

const primaryAction = computed(() => {
  switch (status.value) {
  case 'pending_outgoing':
    return 'cancel'
  case 'pending_incoming':
    return 'decline'
  case 'none':
    return 'propose'
  default:
    return 'end'
  }
})

function openConfirmModal(action) {
  modalAction.value = action
  isConfirmModalOpen.value = true
}

function closeConfirmModal() {
  isConfirmModalOpen.value = false
}

function acceptPartnership() {
  isProcessing.value = true

  router.patch(universityCompanyPartnershipAccept(props.company.id), {}, {
    preserveScroll: true,
    onFinish: () => {
      isProcessing.value = false
    },
  })
}

function confirmAction() {
  const url = universityCompanyPartnership(props.company.id)
  const options = {
    preserveScroll: true,
    onSuccess: () => {
      isConfirmModalOpen.value = false
    },
    onFinish: () => {
      isProcessing.value = false
    },
  }

  isProcessing.value = true

  if (modalAction.value === 'propose') {
    router.post(url, {}, options)
  } else {
    router.delete(url, options)
  }
}
</script>

<template>
  <article
    class="flex flex-col gap-4 rounded-2xl border p-5 transition"
    :class="status === 'active' ? 'border-primary/30 bg-primary/5' : 'border-border bg-white'"
  >
    <div class="flex items-start justify-between gap-3">
      <div class="flex min-w-0 gap-3">
        <img
          v-if="company.logo_path"
          :src="company.logo_path"
          :alt="t('university.companies.card.logoAlt', { company: company.name })"
          class="h-10 w-10 shrink-0 rounded-xl object-cover"
        >
        <div v-else class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-background text-sm font-semibold text-additional">
          {{ companyInitial }}
        </div>

        <div class="min-w-0">
          <h3 class="truncate text-lg font-semibold text-text">{{ company.name }}</h3>
          <p class="text-sm text-additional">{{ company.city }}</p>
        </div>
      </div>

      <span
        class="inline-flex shrink-0 items-center rounded-full px-2.5 py-1 text-xs font-semibold"
        :class="statusClass(status)"
      >
        {{ statusLabel(status) }}
      </span>
    </div>

    <p v-if="company.description" class="line-clamp-2 text-sm text-additional">
      {{ company.description }}
    </p>

    <div v-if="company.tags?.length" class="flex flex-wrap gap-1.5">
      <span
        v-for="tag in company.tags"
        :key="tag"
        class="rounded-full bg-slate-100 px-2.5 py-1 text-xs text-slate-700"
      >
        {{ tag }}
      </span>
    </div>

    <div class="mt-auto flex items-center justify-between gap-3 pt-2">
      <span class="text-sm text-additional">
        {{ t('university.companies.card.offersCount', { count: company.active_offers_count }) }}
      </span>

      <div class="flex shrink-0 gap-2">
        <button
          v-if="isIncoming"
          type="button"
          class="inline-flex items-center justify-center rounded-xl border border-primary/20 bg-primary px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 disabled:cursor-not-allowed disabled:opacity-60"
          :disabled="isProcessing"
          @click="acceptPartnership"
        >
          {{ t('university.companies.card.accept') }}
        </button>

        <button
          type="button"
          class="inline-flex items-center justify-center rounded-xl border px-4 py-2.5 text-sm font-semibold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 disabled:cursor-not-allowed disabled:opacity-60"
          :class="primaryAction === 'propose'
            ? 'border-primary/20 bg-primary text-white hover:bg-primary/90'
            : 'border-red-200 bg-red-50 text-red-700 hover:bg-red-100'"
          :disabled="isProcessing"
          @click="openConfirmModal(primaryAction)"
        >
          {{ t(`university.companies.card.${primaryAction}`) }}
        </button>
      </div>
    </div>

    <PartnerConfirmModal
      :open="isConfirmModalOpen"
      :partner-name="company.name"
      :processing="isProcessing"
      :action="modalAction"
      :namespace="NAMESPACE"
      @close="closeConfirmModal"
      @confirm="confirmAction"
    />
  </article>
</template>
