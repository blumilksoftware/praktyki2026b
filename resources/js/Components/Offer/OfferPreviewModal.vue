<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import BaseButton from '@/Components/Base/BaseButton.vue'
import BaseModal from '@/Components/Common/BaseModal.vue'
import VerifiedBadge from '@/Components/Common/VerifiedBadge.vue'

const props = defineProps({
  open: { type: Boolean, required: true },
  offer: { type: Object, required: true },
})

const emit = defineEmits(['close'])
const { t } = useI18n()

const workModeLabel = computed(() => t(`student.workModes.${props.offer.work_mode || 'onSite'}`))
const isClosed = computed(() => props.offer.status === 'closed' || props.offer.status === 'expired')

function formatDate(dateString) {
  if (!dateString) {
    return ''
  }

  const [year, month, day] = String(dateString).split('-')
  return `${day}.${month}.${year}`
}

const internshipPeriodLabel = computed(() => t('student.offers.detail.internshipPeriodValue', {
  start: formatDate(props.offer.start_date),
  end: formatDate(props.offer.end_date),
}))

const remainingSpotsLabel = computed(() => {
  const total = Number(props.offer.spots ?? 0)
  const remaining = Number(props.offer.remaining_spots ?? total)

  if (remaining <= 0) {
    return t('student.offers.detail.remainingSpotsNone', { total })
  }

  return t('student.offers.detail.remainingSpotsValue', {
    remaining,
    total,
  })
})

const compensationLabel = computed(() => {
  if (!props.offer.is_paid) {
    return t('student.offers.detail.unpaid')
  }

  if (props.offer.salary_min != null && props.offer.salary_max != null) {
    return t('student.offers.detail.salaryRange', {
      min: props.offer.salary_min,
      max: props.offer.salary_max,
    })
  }

  return t('student.offers.detail.paid')
})
</script>

<template>
  <BaseModal
    :open="open"
    :title="t('company.offers.form.previewAction')"
    max-width-class="max-w-5xl"
    @close="emit('close')"
  >
    <div class="space-y-6">
      <article class="rounded-3xl border border-border bg-white p-5 shadow-sm sm:p-8">
        <div class="flex flex-wrap items-center gap-2">
          <p class="text-sm font-semibold text-additional">
            {{ offer.company?.name ?? 'Your company' }}
          </p>
          <VerifiedBadge :verified="Boolean(offer.company?.is_verified)" />
        </div>

        <h1 class="mt-3 text-3xl font-semibold tracking-tight text-text sm:text-4xl">
          {{ offer.title || 'Untitled offer' }}
        </h1>

        <div class="mt-4 flex flex-wrap gap-2">
          <span class="inline-flex items-center rounded-full border border-primary/20 bg-primary/10 px-2.5 py-1 text-sm font-medium text-primary">
            {{ offer.city || 'City not set' }}
          </span>
          <span class="inline-flex items-center rounded-full border border-primary/20 bg-primary/10 px-2.5 py-1 text-sm font-medium text-primary">
            {{ workModeLabel }}
          </span>
        </div>

        <dl class="mt-6 grid gap-4 sm:grid-cols-2">
          <div class="rounded-2xl border border-border bg-background/60 px-4 py-3">
            <dt class="text-xs font-semibold uppercase tracking-wide text-additional">
              {{ t('student.offers.detail.internshipPeriod') }}
            </dt>
            <dd class="mt-1 text-sm font-medium text-text">{{ internshipPeriodLabel }}</dd>
          </div>
          <div class="rounded-2xl border border-border bg-background/60 px-4 py-3">
            <dt class="text-xs font-semibold uppercase tracking-wide text-additional">
              {{ t('student.offers.detail.remainingSpots') }}
            </dt>
            <dd class="mt-1 text-sm font-medium text-text">{{ remainingSpotsLabel }}</dd>
          </div>
          <div class="rounded-2xl border border-border bg-background/60 px-4 py-3">
            <dt class="text-xs font-semibold uppercase tracking-wide text-additional">
              {{ t('student.offers.detail.compensation') }}
            </dt>
            <dd class="mt-1 text-sm font-medium text-text">{{ compensationLabel }}</dd>
          </div>
        </dl>

        <section class="mt-8">
          <h2 class="text-lg font-semibold text-text">
            {{ t('student.offers.detail.description') }}
          </h2>
          <p class="mt-3 whitespace-pre-wrap text-sm leading-7 text-additional">
            {{ offer.description || 'No description yet.' }}
          </p>
        </section>

        <section class="mt-8">
          <h2 class="text-lg font-semibold text-text">
            {{ t('student.offers.detail.preferredUniversities') }}
          </h2>
          <ul v-if="offer.preferred_universities?.length" class="mt-3 flex flex-col gap-2">
            <li
              v-for="university in offer.preferred_universities"
              :key="university.id ?? university.name"
              class="rounded-xl border border-border bg-background/60 px-4 py-2 text-sm text-text"
            >
              {{ university.name }}
            </li>
          </ul>
          <p v-else class="mt-3 text-sm text-additional" role="status">
            {{ t('student.offers.detail.preferredUniversitiesEmpty') }}
          </p>
        </section>

        <section class="mt-8">
          <h2 class="text-lg font-semibold text-text">
            {{ t('student.offers.detail.studyFields') }}
          </h2>
          <ul v-if="offer.study_fields?.length" class="mt-3 flex flex-wrap gap-2">
            <li
              v-for="field in offer.study_fields"
              :key="field.id ?? field.name"
              class="inline-flex items-center rounded-full border border-border bg-white px-3 py-1 text-sm text-text"
            >
              {{ field.name }}
            </li>
          </ul>
          <p v-else class="mt-3 text-sm text-additional" role="status">
            {{ t('student.offers.detail.studyFieldsEmpty') }}
          </p>
        </section>

        <div class="mt-8 flex flex-wrap items-center gap-3 border-t border-border pt-6">
          <p
            v-if="isClosed"
            class="rounded-xl border border-border bg-background px-4 py-3 text-sm font-medium text-text"
            role="status"
          >
            {{
              offer.status === 'expired'
                ? t('student.offers.detail.expiredMessage')
                : t('student.offers.detail.closedMessage')
            }}
          </p>
        </div>
      </article>

      <div class="flex justify-end">
        <BaseButton type="button" variant="secondary" @click="emit('close')">
          {{ t('buttons.cancel') }}
        </BaseButton>
      </div>
    </div>
  </BaseModal>
</template>
