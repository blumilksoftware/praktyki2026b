<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import ApplicationStatusBadge from '@/Components/Student/ApplicationStatusBadge.vue'
import WithdrawApplicationModal from '@/Components/Student/WithdrawApplicationModal.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import { offerShow } from '@/Helpers/routes'

defineProps({
  applications: { type: Array, default: () => [] },
  processing: { type: Boolean, default: false },
})

const emit = defineEmits(['withdraw'])
const { t, locale } = useI18n()

const withdrawTarget = ref(null)

function formatAppliedAt(iso) {
  if (!iso) {
    return ''
  }

  return new Date(iso).toLocaleDateString(locale.value === 'en' ? 'en-GB' : 'pl-PL', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

function openWithdraw(application) {
  withdrawTarget.value = application
}

function closeWithdraw() {
  withdrawTarget.value = null
}

function confirmWithdraw() {
  if (!withdrawTarget.value) {
    return
  }
  emit('withdraw', withdrawTarget.value)
  closeWithdraw()
}
</script>

<template>
  <div>
    <p
      v-if="applications.length === 0"
      class="rounded-2xl border border-border bg-white p-6 text-additional text-sm shadow-sm"
      role="status"
    >
      {{ t('student.applications.empty') }}
    </p>

    <ul
      v-else
      class="flex flex-col gap-4"
      :aria-label="t('student.applications.listAriaLabel')"
    >
      <li
        v-for="application in applications"
        :key="application.id"
        class="relative rounded-2xl border border-border bg-white p-4 shadow-sm transition hover:border-primary/40 hover:-translate-y-0.5 hover:shadow-[0_16px_45px_rgba(11,26,48,0.14)] sm:p-5"
      >
        <Link
          v-if="application.offer_id && !application.offer_deleted"
          :href="offerShow(application.offer_id)"
          class="absolute inset-0 rounded-2xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30"
          :aria-label="t('student.offers.card.openOfferAria', { title: application.offer_title })"
        />

        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
          <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
              <h2 class="font-semibold text-text text-base">
                {{ application.offer_title }}
              </h2>
              <ApplicationStatusBadge :status="application.status" />
            </div>
            <p class="mt-1 text-additional text-sm">
              {{ application.company_name }}
              <template v-if="application.remaining_spots !== null && application.remaining_spots !== undefined">
                &middot; {{ t('student.offers.card.remainingSpots') }}: {{ application.remaining_spots }}
              </template>
            </p>
            <p class="mt-2 text-additional text-xs">
              {{ t('student.applications.appliedAt', { date: formatAppliedAt(application.date_applied) }) }}
            </p>
          </div>

          <BaseButton
            v-if="application.status === 'pending'"
            type="button"
            variant="secondary"
            class="relative z-10 w-full justify-center sm:w-auto"
            :disabled="processing"
            @click="openWithdraw(application)"
          >
            {{ t('student.applications.withdraw.action') }}
          </BaseButton>
        </div>
      </li>
    </ul>

    <WithdrawApplicationModal
      :open="Boolean(withdrawTarget)"
      :offer-title="withdrawTarget?.offer_title ?? ''"
      :processing="processing"
      @close="closeWithdraw"
      @confirm="confirmWithdraw"
    />
  </div>
</template>
