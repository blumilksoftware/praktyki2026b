<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import StarRating from '@/Components/Common/StarRating.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import { companyReviewHide, adminReviewDelete } from '@/Helpers/routes'

const props = defineProps({
  reviews: { type: Object, required: true },
})

const { t } = useI18n()

const hidingId = ref(null)
const deletingId = ref(null)

function hideReview(reviewId) {
  if (!window.confirm(t('profiles.reviews.hideConfirm'))) return

  hidingId.value = reviewId

  router.patch(companyReviewHide(reviewId), {}, {
    preserveScroll: true,
    onFinish: () => {
      hidingId.value = null
    },
  })
}

function deleteReview(reviewId) {
  if (!window.confirm(t('profiles.reviews.deleteConfirm'))) return

  deletingId.value = reviewId

  router.delete(adminReviewDelete(reviewId), {
    preserveScroll: true,
    onFinish: () => {
      deletingId.value = null
    },
  })
}

function formatDate(isoDate) {
  return new Date(isoDate).toLocaleDateString()
}
</script>

<template>
  <div class="flex flex-col gap-6">
    <div class="flex items-center justify-between gap-3">
      <h2 class="text-xl font-bold text-text">{{ t('profiles.reviews.title') }}</h2>
      <div v-if="reviews.count > 0" class="flex items-center gap-2 text-sm text-additional">
        <StarRating :rating="Math.round(reviews.averageRating)" />
        {{ t('profiles.reviews.average', { rating: reviews.averageRating.toFixed(1), count: reviews.count }) }}
      </div>
    </div>

    <div v-if="reviews.items.length" class="flex flex-col gap-3">
      <div
        v-for="review in reviews.items"
        :key="review.id"
        class="rounded-2xl border border-border bg-white p-4 shadow-sm sm:p-5"
      >
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div class="flex flex-col gap-1">
            <div class="flex items-center gap-2">
              <StarRating :rating="review.rating" />
              <span
                v-if="review.hidden"
                class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-additional"
              >
                {{ t('profiles.reviews.hiddenBadge') }}
              </span>
            </div>
            <p class="text-sm font-medium text-text">{{ review.studentName }}</p>
            <p class="text-xs text-additional">{{ formatDate(review.createdAt) }}</p>
          </div>

          <div class="flex shrink-0 gap-2">
            <BaseButton
              v-if="reviews.canModerate && !review.hidden"
              variant="secondary"
              class="px-3 py-1.5 text-xs"
              :disabled="hidingId === review.id"
              @click="hideReview(review.id)"
            >
              {{ t('profiles.reviews.hide') }}
            </BaseButton>

            <BaseButton
              v-if="reviews.canDelete"
              variant="secondary"
              class="px-3 py-1.5 text-xs"
              :disabled="deletingId === review.id"
              @click="deleteReview(review.id)"
            >
              {{ t('profiles.reviews.delete') }}
            </BaseButton>
          </div>
        </div>

        <p v-if="review.comment" class="mt-2 text-sm text-text">{{ review.comment }}</p>
      </div>
    </div>

    <div v-else class="text-additional italic text-sm">
      {{ t('profiles.reviews.empty') }}
    </div>
  </div>
</template>
