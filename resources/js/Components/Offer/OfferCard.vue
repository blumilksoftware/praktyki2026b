<script setup>
import { Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { IconCheck, IconHeart, IconHeartFilled, IconMapPin } from '@tabler/icons-vue'
import BaseApplyButton from '@/Components/Base/BaseApplyButton.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import WithdrawApplicationModal from '@/Components/Student/WithdrawApplicationModal.vue'
import {
  ROUTES,
  studentOfferApply,
  studentOfferFavourite,
  studentOfferWithdraw,
  offerShow,
  companyShow
} from '@/Helpers/routes'
import { useToast } from '@/Composables/useToast'

const props = defineProps({
  offer: { type: Object, required: true },
  hasCv: { type: Boolean, default: true },
  guest: { type: Boolean, default: false },
  canApply: { type: Boolean, default: false },
})

const { t } = useI18n()
const { toastSuccess, toastError } = useToast()

const companyInitial = computed(() => props.offer.company?.name?.charAt(0) || 'O')

const workModeLabel = computed(() => t(`student.workModes.${props.offer.work_mode}`))

function formatDate(dateString) {
  if (!dateString) return ''
  const [year, month, day] = dateString.split('-')
  return `${day}.${month}.${year}`
}

const formattedDateRange = computed(() => `${formatDate(props.offer.start_date)} - ${formatDate(props.offer.end_date)}`)

const isApplying = ref(false)
const appliedLocally = ref(false)
const withdrawnLocally = ref(false)

const isApplied = computed(() => !withdrawnLocally.value && (props.offer.has_applied || appliedLocally.value))
const appliedDate = computed(() => props.offer.applied_at)

const stretchActions = computed(() => props.canApply && isApplied.value)
const actionWidthClass = computed(() => stretchActions.value ? 'w-full sm:flex-1' : 'w-full sm:w-fit')

function applyToOffer() {
  isApplying.value = true

  router.post(studentOfferApply(props.offer.id), {}, {
    preserveScroll: true,
    onSuccess: () => {
      appliedLocally.value = true
      withdrawnLocally.value = false
      toastSuccess(t('student.applications.applySuccess'))
    },
    onError: (errors) => {
      const message = errors.cv ?? errors.offer

      if (message) {
        toastError(message)
      }
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
      toastError(t('student.applications.withdrawSuccess'))
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
  <article class="group overflow-hidden rounded-2xl border border-border bg-white shadow-[0_4px_16px_rgba(11,26,48,0.06)] transition sm:rounded-3xl sm:shadow-[0_8px_30px_rgba(11,26,48,0.08)] sm:hover:-translate-y-0.5 sm:hover:shadow-[0_16px_45px_rgba(11,26,48,0.14)]">
    <div class="p-5 sm:p-6">
      <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
        <div class="min-w-0 flex gap-3">
          <Link
            :href="`${companyShow(offer.company.id)}?from=offers`"
            class="shrink-0 transition-opacity hover:opacity-80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 rounded-xl"
          >
            <img
              v-if="offer.company.logo_path"
              :src="offer.company.logo_path"
              :alt="t('student.offers.card.logoAlt', { company: offer.company.name })"
              class="h-10 w-10 rounded-xl object-cover"
            >
            <div v-else class="flex h-10 w-10 items-center justify-center rounded-xl bg-background text-sm font-medium text-additional">
              {{ companyInitial }}
            </div>
          </Link>

          <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
              <Link
                :href="`${companyShow(offer.company.id)}?from=offers`"
                class="text-sm font-medium text-additional transition-colors hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 rounded"
              >
                {{ offer.company.name }}
              </Link>

              <span
                v-if="offer.company.is_verified"
                class="inline-flex shrink-0 items-center gap-1 rounded-full bg-green-50 px-2.5 py-1 text-xs font-medium text-success"
                :aria-label="t('student.offers.card.verifiedAriaLabel')"
              >
                <IconCheck class="h-3.5 w-3.5" stroke-width="2" aria-hidden="true" />
                <span aria-hidden="true">{{ t('student.offers.card.verified') }}</span>
              </span>
            </div>
            <h3 class="mt-1 text-xl font-semibold tracking-tight text-text sm:text-2xl">
              <Link
                :href="offerShow(offer.id)"
                class="transition-colors hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 rounded"
              >
                {{ offer.title }}
              </Link>
            </h3>
          </div>
        </div>
        <div class="flex flex-wrap gap-2 sm:justify-end">
          <span class="inline-flex items-center rounded-full border border-primary/20 bg-primary/10 px-2.5 py-1 text-sm font-medium text-primary">{{ offer.city }}</span>
          <span class="inline-flex items-center rounded-full border border-primary/20 bg-primary/10 px-2.5 py-1 text-sm font-medium text-primary">{{ workModeLabel }}</span>
        </div>
      </div>

      <div class="mt-5 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-additional">
        <span>{{ t('student.offers.card.dateRange') }}: {{ formattedDateRange }}</span>
        <span aria-hidden="true">&middot;</span>
        <span>{{ t('student.offers.card.remainingSpots') }}: {{ offer.remaining_spots }}</span>
      </div>

      <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center sm:gap-2">
        <button
          type="button"
          class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-lg border border-border cursor-pointer bg-white px-4 py-3 text-sm font-semibold text-text hover:border-primary/40 hover:bg-background transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30"
          :class="actionWidthClass"
          @click="showOnMap"
        >
          <IconMapPin class="h-5 w-5" aria-hidden="true" />
          {{ t('student.offers.card.showOnMap') }}
        </button>

        <button
          v-if="canApply"
          type="button"
          class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-lg border px-4 py-3 cursor-pointer text-sm font-semibold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 disabled:cursor-not-allowed disabled:opacity-60"
          :class="[actionWidthClass, isFavorite
            ? 'border-red-200 bg-red-50 text-red-700 hover:bg-red-100'
            : 'border-border bg-white text-text hover:border-primary/40 hover:bg-background']"
          :disabled="isTogglingFavorite"
          :aria-pressed="isFavorite"
          :aria-label="isFavorite
            ? t('student.offers.card.removeFromFavorites')
            : t('student.offers.card.addToFavorites')"
          @click="toggleFavorite"
        >
          <component :is="isFavorite ? IconHeartFilled : IconHeart" class="h-5 w-5" aria-hidden="true" />
          {{ isFavorite ? t('student.offers.card.removeFromFavorites') : t('student.offers.card.addToFavorites') }}
        </button>

        <Link
          v-if="guest"
          :href="ROUTES.LOGIN"
          class="inline-flex w-full sm:w-fit items-center justify-center rounded-lg bg-primary px-4 py-3 text-sm font-semibold text-white transition hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
        >
          {{ t('student.offers.card.loginToApply') }}
        </Link>

        <template v-else-if="canApply">
          <BaseApplyButton
            :id="`apply-offer-${offer.id}`"
            class="whitespace-nowrap"
            :has-cv="hasCv"
            :is-applied="isApplied"
            :applied-date="appliedDate"
            :is-loading="isApplying"
            :width-class="actionWidthClass"
            @apply="applyToOffer"
          />

          <BaseButton
            v-if="isApplied"
            type="button"
            class="whitespace-nowrap"
            variant="secondary"
            :width-class="actionWidthClass"
            :disabled="isWithdrawing"
            @click="openWithdrawModal"
          >
            {{ t('student.applications.withdraw.action') }}
          </BaseButton>
        </template>
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
  </article>
</template>
