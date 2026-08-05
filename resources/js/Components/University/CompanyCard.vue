<script setup>
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import PartnerConfirmModal from '@/Components/University/PartnerConfirmModal.vue'
import { usePartnershipStatus } from '@/Composables/usePartnershipStatus'
import { universityCompanyPartnership } from '@/Helpers/routes'

const props = defineProps({
  company: { type: Object, required: true },
})

const { t } = useI18n()
const { statusClass, statusLabel } = usePartnershipStatus()

const companyInitial = computed(() => props.company.name?.charAt(0) || '?')

const isTogglingPartner = ref(false)
const isConfirmModalOpen = ref(false)

const isPartner = computed(() => props.company.partnership_status !== 'none')
const modalAction = computed(() => isPartner.value ? 'remove' : 'add')

function openConfirmModal() {
  isConfirmModalOpen.value = true
}

function closeConfirmModal() {
  isConfirmModalOpen.value = false
}

function confirmToggle() {
  const url = universityCompanyPartnership(props.company.id)
  const options = {
    preserveScroll: true,
    onSuccess: () => {
      isConfirmModalOpen.value = false
    },
    onFinish: () => {
      isTogglingPartner.value = false
    },
  }

  isTogglingPartner.value = true

  if (isPartner.value) {
    router.delete(url, options)
  } else {
    router.post(url, {}, options)
  }
}
</script>

<template>
  <article
    class="flex flex-col gap-4 rounded-2xl border p-5 transition"
    :class="isPartner ? 'border-primary/30 bg-primary/5' : 'border-border bg-white'"
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
        :class="statusClass(company.partnership_status)"
      >
        {{ statusLabel(company.partnership_status) }}
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

      <button
        type="button"
        class="inline-flex items-center justify-center rounded-xl border px-4 py-2.5 text-sm font-semibold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 disabled:cursor-not-allowed disabled:opacity-60"
        :class="isPartner
          ? 'border-red-200 bg-red-50 text-red-700 hover:bg-red-100'
          : 'border-primary/20 bg-primary text-white hover:bg-primary/90'"
        :disabled="isTogglingPartner"
        @click="openConfirmModal"
      >
        {{ isPartner ? t('university.companies.card.removePartner') : t('university.companies.card.addPartner') }}
      </button>
    </div>

    <PartnerConfirmModal
      :open="isConfirmModalOpen"
      :company-name="company.name"
      :processing="isTogglingPartner"
      :action="modalAction"
      @close="closeConfirmModal"
      @confirm="confirmToggle"
    />
  </article>
</template>
