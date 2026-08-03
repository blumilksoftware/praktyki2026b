<script setup>
import { Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { IconCheck, IconHeart, IconHeartFilled } from '@tabler/icons-vue'
import BaseApplyButton from '@/Components/Base/BaseApplyButton.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import WithdrawApplicationModal from '@/Components/Student/WithdrawApplicationModal.vue'
import { ROUTES, studentOfferApply, studentOfferFavourite, studentOfferWithdraw } from '@/Helpers/routes'

const props = defineProps({
  offer: { type: Object, required: true },
  hasCv: { type: Boolean, default: true },
  guest: { type: Boolean, default: false },
})

const { t } = useI18n()

const companyInitial = computed(() => props.offer.company?.name?.charAt(0) || 'O')

const workModeLabel = computed(() => t(`student.workModes.${props.offer.work_mode}`))

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

function goToUploadCv() {
  router.visit(ROUTES.STUDENT_PROFILE_EDIT)
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

function showOnMap() {
  router.get(ROUTES.OFFERS, {
    view: 'map',
    offerId: props.offer.id,
  }, {
    preserveState: false,
    preserveScroll: true,
  })
}
</script>

<template>
  <article class="group overflow-hidden bg-white transition sm:rounded-3xl sm:border sm:border-border sm:shadow-[0_8px_30px_rgba(11,26,48,0.08)] sm:hover:-translate-y-0.5 sm:hover:shadow-[0_16px_45px_rgba(11,26,48,0.14)]">
    <div class="grid gap-3 lg:gap-0 lg:grid-cols-[96px_minmax(0,1fr)]">
      <div class="flex items-center justify-center bg-white pt-5 px-5 lg:p-4">
        <img
          v-if="offer.company.logo_path"
          :src="offer.company.logo_path"
          :alt="t('student.offers.card.logoAlt', { company: offer.company.name })"
          class="h-16 w-16 rounded-2xl object-cover ring-4 ring-white"
        >
        <div v-else class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white text-lg font-semibold text-additional ring-4 ring-white">
          {{ companyInitial }}
        </div>
      </div>

      <div class="p-5 sm:p-6">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
          <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
              <p class="text-sm font-semibold uppercase tracking-[0.18em] text-additional">
                {{ offer.company.name }}
              </p>
              <span
                v-if="offer.company.is_verified"
                class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2.5 py-1 text-xs font-semibold text-success"
                :aria-label="t('student.offers.card.verifiedAriaLabel')"
              >
                <IconCheck class="h-3.5 w-3.5" stroke-width="2" aria-hidden="true" />
                <span aria-hidden="true">{{ t('student.offers.card.verified') }}</span>
              </span>
            </div>
            <h3 class="mt-3 text-xl font-semibold tracking-tight text-text sm:text-2xl">
              {{ offer.title }}
            </h3>
          </div>

          <div class="flex flex-wrap gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-additional sm:justify-end">
            <span class="rounded-full border border-border bg-white px-3 py-1.5">{{ offer.city }}</span>
            <span class="rounded-full border border-border bg-white px-3 py-1.5">{{ workModeLabel }}</span>
          </div>
        </div>

        <div class="mt-6 grid gap-3 sm:grid-cols-3">
          <div class="rounded-2xl border border-border bg-white px-4 py-3">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-additional">{{ t('student.offers.card.dateRange') }}</p>
            <p class="mt-1 text-sm font-medium text-text">{{ offer.start_date }} - {{ offer.end_date }}</p>
          </div>

          <div class="rounded-2xl border border-border bg-white px-4 py-3">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-additional">{{ t('student.offers.card.remainingSpots') }}</p>
            <p class="mt-1 text-sm font-medium text-text">{{ offer.remaining_spots }}</p>
          </div>

          <div class="rounded-2xl border border-border bg-white px-4 py-3">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-additional">{{ t('student.offers.card.location') }}</p>
            <p class="mt-1 text-sm font-medium text-text">{{ offer.city }}</p>
          </div>
        </div>

        <div class="mt-5 flex flex-wrap items-center gap-3">
          <button
            type="button"
            class="inline-flex items-center justify-center rounded-xl border border-border cursor-pointer bg-white px-4 py-2.5 text-sm font-semibold text-text hover:border-primary/40 hover:bg-background transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30"
            @click="showOnMap"
          >
            {{ t('student.offers.card.showOnMap') }}
          </button>

          <button
            v-if="!guest"
            type="button"
            class="inline-flex items-center gap-2 rounded-xl border px-4 py-2.5 cursor-pointer text-sm font-semibold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 disabled:cursor-not-allowed disabled:opacity-60"
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
            v-if="guest"
            :href="ROUTES.LOGIN"
            class="inline-flex items-center justify-center rounded-lg bg-primary px-6 py-3 text-sm font-semibold text-white transition hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
          >
            {{ t('student.offers.card.loginToApply') }}
          </Link>

          <template v-else>
            <BaseApplyButton
              :has-cv="hasCv"
              :is-applied="isApplied"
              :applied-date="appliedDate"
              :is-loading="isApplying"
              @apply="applyToOffer"
              @upload-cv="goToUploadCv"
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
        </div>

        <p v-if="applyError" class="mt-3 text-error text-sm" role="alert">
          {{ applyError }}
        </p>
      </div>
    </div>

    <WithdrawApplicationModal
      v-if="!guest"
      :open="isWithdrawModalOpen"
      :offer-title="offer.title"
      :processing="isWithdrawing"
      @close="closeWithdrawModal"
      @confirm="confirmWithdraw"
    />
  </article>
</template>
