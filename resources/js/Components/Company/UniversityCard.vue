<script setup>
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { IconCheck, IconX, IconBan, IconLink } from '@tabler/icons-vue'
import PartnerConfirmModal from '@/Components/Partnership/PartnerConfirmModal.vue'
import { usePartnershipStatus } from '@/Composables/usePartnershipStatus'
import { companyUniversityPartnership, companyUniversityPartnershipAccept } from '@/Helpers/routes'

const ACTION_ICONS = {
  propose: IconLink,
  cancel: IconX,
  decline: IconX,
  end: IconBan,
}

const NAMESPACE = 'company.universities'

const props = defineProps({
  university: { type: Object, required: true },
})

const { t } = useI18n()
const { statusClass, statusLabel } = usePartnershipStatus(NAMESPACE)

const universityInitial = computed(() => props.university.name?.charAt(0) || '?')

const isProcessing = ref(false)
const isConfirmModalOpen = ref(false)
const modalAction = ref('propose')
const modalError = ref(null)
const acceptError = ref(null)

const status = computed(() => props.university.partnership_status)
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
  modalError.value = null
  isConfirmModalOpen.value = true
}

function closeConfirmModal() {
  isConfirmModalOpen.value = false
}

function acceptPartnership() {
  isProcessing.value = true
  acceptError.value = null

  router.patch(companyUniversityPartnershipAccept(props.university.id), {}, {
    preserveScroll: true,
    onError: (errors) => {
      acceptError.value = errors.university ?? null
    },
    onFinish: () => {
      isProcessing.value = false
    },
  })
}

function confirmAction() {
  const url = companyUniversityPartnership(props.university.id)
  const options = {
    preserveScroll: true,
    onSuccess: () => {
      isConfirmModalOpen.value = false
    },
    onError: (errors) => {
      modalError.value = errors.university ?? null
    },
    onFinish: () => {
      isProcessing.value = false
    },
  }

  isProcessing.value = true
  modalError.value = null

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
          v-if="university.logo_path"
          :src="university.logo_path"
          :alt="t('company.universities.card.logoAlt', { university: university.name })"
          class="h-10 w-10 shrink-0 rounded-xl object-cover"
        >
        <div v-else class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-background text-sm font-semibold text-additional">
          {{ universityInitial }}
        </div>

        <div class="min-w-0">
          <h3 class="line-clamp-2 text-lg font-semibold text-text">{{ university.name }}</h3>
          <p class="text-sm text-additional">{{ university.city }}</p>
        </div>
      </div>

      <span
        class="inline-flex shrink-0 items-center rounded-full px-2.5 py-1 text-xs font-semibold"
        :class="statusClass(status)"
      >
        {{ statusLabel(status) }}
      </span>
    </div>

    <div class="mt-auto flex flex-col gap-2 pt-2">
      <div class="flex items-center justify-end gap-2">
        <button
          v-if="isIncoming"
          type="button"
          class="inline-flex cursor-pointer items-center justify-center gap-1.5 rounded-xl border border-primary/20 bg-primary px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 disabled:cursor-not-allowed disabled:opacity-60"
          :disabled="isProcessing"
          @click="acceptPartnership"
        >
          <IconCheck class="h-4 w-4" aria-hidden="true" />
          {{ t('company.universities.card.accept') }}
        </button>

        <button
          type="button"
          class="inline-flex cursor-pointer items-center justify-center gap-1.5 rounded-xl border px-4 py-2.5 text-sm font-semibold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 disabled:cursor-not-allowed disabled:opacity-60"
          :class="primaryAction === 'propose'
            ? 'border-primary/20 bg-primary text-white hover:bg-primary/90'
            : 'border-red-200 bg-red-50 text-red-700 hover:bg-red-100'"
          :disabled="isProcessing"
          @click="openConfirmModal(primaryAction)"
        >
          <component :is="ACTION_ICONS[primaryAction]" class="h-4 w-4" aria-hidden="true" />
          {{ t(`company.universities.card.${primaryAction}`) }}
        </button>
      </div>

      <p v-if="acceptError" class="text-right text-error text-sm" role="alert">
        {{ acceptError }}
      </p>
    </div>

    <PartnerConfirmModal
      :open="isConfirmModalOpen"
      :partner-name="university.name"
      :processing="isProcessing"
      :action="modalAction"
      :namespace="NAMESPACE"
      :error="modalError"
      @close="closeConfirmModal"
      @confirm="confirmAction"
    />
  </article>
</template>
