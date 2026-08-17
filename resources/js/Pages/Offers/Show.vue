<script setup>
import { computed, ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { IconHeart, IconHeartFilled } from '@tabler/icons-vue'
import StudentPanelLayout from '@/Components/Student/StudentPanelLayout.vue'
import BaseLayout from '@/Components/Layouts/BaseLayout.vue'
import BaseApplyButton from '@/Components/Base/BaseApplyButton.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import VerifiedBadge from '@/Components/Common/VerifiedBadge.vue'
import WithdrawApplicationModal from '@/Components/Student/WithdrawApplicationModal.vue'
import SimilarOfferCard from '@/Components/Offer/SimilarOfferCard.vue'
import {
  ROUTES,
  companyShow,
  studentOfferApply,
  studentOfferFavourite,
  studentOfferWithdraw,
  universityShow,
} from '@/Helpers/routes'

const props = defineProps({
  offer: { type: Object, required: true },
  similarOffers: { type: Array, default: () => [] },
  hasCv: { type: Boolean, default: false },
  isGuest: { type: Boolean, default: true },
  canApply: { type: Boolean, default: false },
})

const { t } = useI18n()

const layoutComponent = computed(() => (props.canApply ? StudentPanelLayout : BaseLayout))
const layoutProps = computed(() => (props.canApply ? { activePage: 'offers' } : {}))

const workModeLabel = computed(() => t(`student.workModes.${props.offer.work_mode}`))
const isClosed = computed(() => props.offer.status === 'closed' || props.offer.status === 'expired')
const acceptsApplications = computed(() => props.offer.status === 'published')

function formatDate(dateString) {
  if (!dateString) {
    return ''
  }

  const [year, month, day] = dateString.split('-')

  return `${day}.${month}.${year}`
}

const internshipPeriodLabel = computed(() => t('student.offers.detail.internshipPeriodValue', {
  start: formatDate(props.offer.start_date),
  end: formatDate(props.offer.end_date),
}))

const remainingSpotsLabel = computed(() => {
  if (props.offer.remaining_spots <= 0) {
    return t('student.offers.detail.remainingSpotsNone', { total: props.offer.spots })
  }

  return t('student.offers.detail.remainingSpotsValue', {
    remaining: props.offer.remaining_spots,
    total: props.offer.spots,
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

const companyHref = computed(() => (
  props.offer.company?.is_verified
    ? companyShow(props.offer.company.id)
    : null
))

const isApplying = ref(false)
const appliedLocally = ref(false)
const withdrawnLocally = ref(false)
const applyError = ref(null)

const isApplied = computed(() => !withdrawnLocally.value && (props.offer.has_applied || appliedLocally.value))
const appliedDate = computed(() => props.offer.applied_at)

function applyToOffer() {
  applyError.value = null
  isApplying.value = true

  router.post(studentOfferApply(props.offer.id), {}, {
    preserveScroll: true,
    onSuccess: () => {
      appliedLocally.value = true
      withdrawnLocally.value = false
    },
    onError: (errors) => {
      applyError.value = errors.cv ?? errors.offer ?? null
    },
    onFinish: () => {
      isApplying.value = false
    },
  })
}

const isTogglingFavorite = ref(false)
const favoritedLocally = ref(false)
const unfavoritedLocally = ref(false)

const isFavorite = computed(() => !unfavoritedLocally.value && (props.offer.is_favorite || favoritedLocally.value))

function toggleFavorite() {
  const wasFavorite = isFavorite.value
  const url = studentOfferFavourite(props.offer.id)
  const options = {
    preserveScroll: true,
    onSuccess: () => {
      favoritedLocally.value = !wasFavorite
      unfavoritedLocally.value = wasFavorite
    },
    onFinish: () => {
      isTogglingFavorite.value = false
    },
  }

  isTogglingFavorite.value = true

  if (wasFavorite) {
    router.delete(url, options)
  } else {
    router.post(url, {}, options)
  }
}

const isWithdrawModalOpen = ref(false)
const isWithdrawing = ref(false)

function openWithdrawModal() {
  isWithdrawModalOpen.value = true
}

function closeWithdrawModal() {
  isWithdrawModalOpen.value = false
}

function confirmWithdraw() {
  isWithdrawing.value = true

  router.post(studentOfferWithdraw(props.offer.id), {}, {
    preserveScroll: true,
    onSuccess: () => {
      withdrawnLocally.value = true
      appliedLocally.value = false
      isWithdrawModalOpen.value = false
    },
    onFinish: () => {
      isWithdrawing.value = false
    },
  })
}
</script>

<template>
  <Head :title="offer.title" />

  <component :is="layoutComponent" v-bind="layoutProps">
    <div class="bg-background min-h-screen py-6">
      <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <Link
          :href="ROUTES.OFFERS"
          class="inline-flex items-center gap-2 rounded-full border border-border bg-white px-4 py-2 text-sm font-semibold text-text transition hover:border-primary/40 hover:bg-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30"
        >
          <span aria-hidden="true">←</span>
          {{ t('student.offers.detail.backToOffers') }}
        </Link>

        <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-start">
          <article class="rounded-3xl border border-border bg-white p-5 shadow-sm sm:p-8">
            <div class="flex flex-wrap items-center gap-2">
              <component
                :is="companyHref ? Link : 'p'"
                v-bind="companyHref
                  ? {
                    href: companyHref,
                    class: 'text-sm font-semibold text-additional transition hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 rounded',
                    'aria-label': t('student.offers.detail.companyProfileAria', { company: offer.company.name }),
                  }
                  : { class: 'text-sm font-semibold text-additional' }"
              >
                {{ offer.company.name }}
              </component>
              <VerifiedBadge :verified="Boolean(offer.company.is_verified)" />
            </div>

            <h1 class="mt-3 text-3xl font-semibold tracking-tight text-text sm:text-4xl">
              {{ offer.title }}
            </h1>

            <div class="mt-4 flex flex-wrap gap-2">
              <span class="inline-flex items-center rounded-full border border-primary/20 bg-primary/10 px-2.5 py-1 text-sm font-medium text-primary">
                {{ offer.city }}
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
                {{ offer.description }}
              </p>
            </section>

            <section class="mt-8">
              <h2 class="text-lg font-semibold text-text">
                {{ t('student.offers.detail.preferredUniversities') }}
              </h2>
              <ul
                v-if="offer.preferred_universities?.length"
                class="mt-3 flex flex-col gap-2"
              >
                <li
                  v-for="university in offer.preferred_universities"
                  :key="university.id"
                >
                  <component
                    :is="university.is_verified ? Link : 'span'"
                    v-bind="university.is_verified
                      ? {
                        href: universityShow(university.id),
                        class: 'block rounded-xl border border-border bg-background/60 px-4 py-2 text-sm text-text transition hover:border-primary/40 hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40',
                        'aria-label': t('student.offers.detail.universityProfileAria', { university: university.name }),
                      }
                      : { class: 'block rounded-xl border border-border bg-background/60 px-4 py-2 text-sm text-text' }"
                  >
                    {{ university.name }}
                  </component>
                </li>
              </ul>
              <p
                v-else
                class="mt-3 text-sm text-additional"
                role="status"
              >
                {{ t('student.offers.detail.preferredUniversitiesEmpty') }}
              </p>
            </section>

            <section class="mt-8">
              <h2 class="text-lg font-semibold text-text">
                {{ t('student.offers.detail.studyFields') }}
              </h2>
              <ul
                v-if="offer.study_fields?.length"
                class="mt-3 flex flex-wrap gap-2"
              >
                <li
                  v-for="field in offer.study_fields"
                  :key="field.id"
                  class="inline-flex items-center rounded-full border border-border bg-white px-3 py-1 text-sm text-text"
                >
                  {{ field.name }}
                </li>
              </ul>
              <p
                v-else
                class="mt-3 text-sm text-additional"
                role="status"
              >
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

              <template v-else>
                <button
                  v-if="canApply"
                  type="button"
                  class="inline-flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-semibold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 disabled:cursor-not-allowed disabled:opacity-60 cursor-pointer"
                  :class="isFavorite
                    ? 'border-red-200 bg-red-50 text-red-700 hover:bg-red-100'
                    : 'border-border bg-white text-text hover:border-primary/40 hover:bg-background'"
                  :disabled="isTogglingFavorite"
                  :aria-pressed="isFavorite"
                  :aria-label="isFavorite
                    ? t('student.offers.card.removeFromFavorites')
                    : t('student.offers.card.addToFavorites')"
                  @click="toggleFavorite"
                >
                  <component :is="isFavorite ? IconHeartFilled : IconHeart" class="h-4 w-4" aria-hidden="true" />
                  {{ isFavorite ? t('student.offers.card.removeFromFavorites') : t('student.offers.card.addToFavorites') }}
                </button>

                <Link
                  v-if="isGuest"
                  :href="ROUTES.LOGIN"
                  class="inline-flex items-center justify-center rounded-lg bg-primary px-6 py-3 text-sm font-semibold text-white transition hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
                >
                  {{ t('student.offers.card.loginToApply') }}
                </Link>

                <template v-else-if="canApply && acceptsApplications">
                  <BaseApplyButton
                    :id="`apply-offer-${offer.id}`"
                    :has-cv="hasCv"
                    :is-applied="isApplied"
                    :applied-date="appliedDate"
                    :is-loading="isApplying"
                    @apply="applyToOffer"
                  />

                  <BaseButton
                    v-if="isApplied"
                    type="button"
                    variant="secondary"
                    :disabled="isWithdrawing"
                    @click="openWithdrawModal"
                  >
                    {{ t('student.applications.withdraw.action') }}
                  </BaseButton>
                </template>
              </template>
            </div>

            <p v-if="applyError" class="mt-3 text-error text-sm" role="alert">
              {{ applyError }}
            </p>
          </article>

          <aside
            v-if="similarOffers.length > 0"
            class="rounded-3xl border border-border bg-white p-5 shadow-sm lg:sticky lg:top-6"
            aria-labelledby="similar-offers-heading"
          >
            <h2
              id="similar-offers-heading"
              class="text-lg font-semibold text-text"
            >
              {{ t('student.offers.detail.similarOffers') }}
            </h2>
            <ul class="mt-4 flex flex-col gap-3">
              <li
                v-for="similarOffer in similarOffers"
                :key="similarOffer.id"
              >
                <SimilarOfferCard :offer="similarOffer" />
              </li>
            </ul>
          </aside>
        </div>
      </div>
    </div>

    <WithdrawApplicationModal
      v-if="canApply"
      :open="isWithdrawModalOpen"
      :offer-title="offer.title"
      :processing="isWithdrawing"
      @close="closeWithdrawModal"
      @confirm="confirmWithdraw"
    />
  </component>
</template>
