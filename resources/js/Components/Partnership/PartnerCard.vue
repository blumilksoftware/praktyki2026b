<script setup>
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { IconCheck, IconX, IconBan, IconLink } from '@tabler/icons-vue'
import PartnerConfirmModal from '@/Components/Partnership/PartnerConfirmModal.vue'
import { usePartnershipStatus } from '@/Composables/usePartnershipStatus'

const ACTION_ICONS = {
  propose: IconLink,
  cancel: IconX,
  end: IconBan,
}

const props = defineProps({
  partner: { type: Object, required: true },
  namespace: { type: String, required: true },
  actionUrl: { type: String, required: true },
  acceptUrl: { type: String, required: true },
})

const { t } = useI18n()
const { statusClass, statusLabel } = usePartnershipStatus(props.namespace)

const partnerInitial = computed(() => props.partner.name?.charAt(0) || '?')
const hasOffersCount = computed(() => props.partner.active_offers_count !== undefined)

const isProcessing = ref(false)
const isConfirmModalOpen = ref(false)
const modalAction = ref('propose')
const modalError = ref(null)
const acceptError = ref(null)

const status = computed(() => props.partner.partnership_status)
const isIncoming = computed(() => status.value === 'pending_incoming')

const primaryAction = computed(() => {
  switch (status.value) {
  case 'pending_outgoing':
    return 'cancel'
  case 'none':
    return 'propose'
  case 'pending_incoming':
    return null
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

  router.patch(props.acceptUrl, {}, {
    preserveScroll: true,
    onError: (errors) => {
      acceptError.value = errors.company ?? errors.university ?? null
    },
    onFinish: () => {
      isProcessing.value = false
    },
  })
}

function confirmAction() {
  const options = {
    preserveScroll: true,
    onSuccess: () => {
      isConfirmModalOpen.value = false
    },
    onError: (errors) => {
      modalError.value = errors.company ?? errors.university ?? null
    },
    onFinish: () => {
      isProcessing.value = false
    },
  }

  isProcessing.value = true
  modalError.value = null

  if (modalAction.value === 'propose') {
    router.post(props.actionUrl, {}, options)
  } else {
    router.delete(props.actionUrl, options)
  }
}
</script>

<template>
  <article
    class="flex flex-col gap-4 rounded-2xl border p-5 transition"
    :class="status === 'active' ? 'border-primary/30 bg-primary/5' : 'border-border bg-white'"
  >
    <div class="flex items-start gap-3">
      <img
        v-if="partner.logo_path"
        :src="partner.logo_path"
        :alt="t(`${namespace}.card.logoAlt`, { company: partner.name, university: partner.name })"
        class="h-10 w-10 shrink-0 rounded-xl object-cover"
      >
      <div v-else class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-background text-sm font-semibold text-additional">
        {{ partnerInitial }}
      </div>

      <div class="min-w-0 flex-1">
        <h3 class="line-clamp-2 text-lg font-semibold text-text">{{ partner.name }}</h3>
        <p class="text-sm text-additional">{{ partner.city }}</p>
        <span
          class="mt-1 inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold"
          :class="statusClass(status)"
        >
          {{ statusLabel(status) }}
        </span>
      </div>
    </div>

    <p v-if="partner.description" class="line-clamp-2 text-sm text-additional">
      {{ partner.description }}
    </p>

    <div v-if="partner.tags?.length" class="flex flex-wrap gap-1.5">
      <span
        v-for="tag in partner.tags"
        :key="tag"
        class="rounded-full bg-slate-100 px-2.5 py-1 text-xs text-slate-700"
      >
        {{ tag }}
      </span>
    </div>

    <div class="mt-auto flex flex-col gap-2 pt-2">
      <div class="flex items-center gap-3" :class="hasOffersCount ? 'justify-between' : 'justify-end'">
        <span v-if="hasOffersCount" class="text-sm text-additional">
          {{ t(`${namespace}.card.offersCount`, { count: partner.active_offers_count }) }}
        </span>

        <div class="flex shrink-0 gap-2">
          <button
            v-if="isIncoming"
            type="button"
            class="inline-flex cursor-pointer items-center justify-center gap-1.5 rounded-xl border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-700 transition hover:bg-red-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 disabled:cursor-not-allowed disabled:opacity-60"
            :disabled="isProcessing"
            @click="openConfirmModal('decline')"
          >
            <IconX class="h-4 w-4" aria-hidden="true" />
            {{ t(`${namespace}.card.decline`) }}
          </button>

          <button
            v-if="isIncoming"
            type="button"
            class="inline-flex cursor-pointer items-center justify-center gap-1.5 rounded-xl border border-primary/20 bg-primary px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 disabled:cursor-not-allowed disabled:opacity-60"
            :disabled="isProcessing"
            @click="acceptPartnership"
          >
            <IconCheck class="h-4 w-4" aria-hidden="true" />
            {{ t(`${namespace}.card.accept`) }}
          </button>

          <button
            v-if="!isIncoming"
            type="button"
            class="inline-flex cursor-pointer items-center justify-center gap-1.5 rounded-xl border px-4 py-2.5 text-sm font-semibold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 disabled:cursor-not-allowed disabled:opacity-60"
            :class="primaryAction === 'propose'
              ? 'border-primary/20 bg-primary text-white hover:bg-primary/90'
              : 'border-red-200 bg-red-50 text-red-700 hover:bg-red-100'"
            :disabled="isProcessing"
            @click="openConfirmModal(primaryAction)"
          >
            <component :is="ACTION_ICONS[primaryAction]" class="h-4 w-4" aria-hidden="true" />
            {{ t(`${namespace}.card.${primaryAction}`) }}
          </button>
        </div>
      </div>

      <p v-if="acceptError" class="text-right text-error text-sm" role="alert">
        {{ acceptError }}
      </p>
    </div>

    <PartnerConfirmModal
      :open="isConfirmModalOpen"
      :partner-name="partner.name"
      :processing="isProcessing"
      :action="modalAction"
      :namespace="namespace"
      :error="modalError"
      @close="closeConfirmModal"
      @confirm="confirmAction"
    />
  </article>
</template>
