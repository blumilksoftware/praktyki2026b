<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import StarRating from '@/Components/Common/StarRating.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import ReviewActionModal from '@/Components/Profiles/ReviewActionModal.vue'
import { companyReviewHide, companyReviewUnhide, adminReviewDelete } from '@/Helpers/routes'

const props = defineProps({
  reviews: { type: Object, required: true },
})

const { t } = useI18n()

const pendingReviewId = ref(null)
const pendingAction = ref(null)
const processing = ref(false)

function requestAction(reviewId, action) {
  pendingReviewId.value = reviewId
  pendingAction.value = action
}

function closeModal() {
  pendingReviewId.value = null
  pendingAction.value = null
}

function confirmAction() {
  const reviewId = pendingReviewId.value
  const action = pendingAction.value
  processing.value = true

  const options = {
    preserveScroll: true,
    onFinish: () => {
      processing.value = false
      closeModal()
    },
  }

  if (action === 'hide') {
    router.patch(companyReviewHide(reviewId), {}, options)
  } else if (action === 'unhide') {
    router.patch(companyReviewUnhide(reviewId), {}, options)
  } else if (action === 'delete') {
    router.delete(adminReviewDelete(reviewId), options)
  }
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
              @click="requestAction(review.id, 'hide')"
            >
              {{ t('profiles.reviews.hide') }}
            </BaseButton>

            <BaseButton
              v-if="reviews.canModerate && review.hidden"
              variant="secondary"
              class="px-3 py-1.5 text-xs"
              @click="requestAction(review.id, 'unhide')"
            >
              {{ t('profiles.reviews.unhide') }}
            </BaseButton>

            <BaseButton
              v-if="reviews.canDelete"
              variant="secondary"
              class="px-3 py-1.5 text-xs"
              @click="requestAction(review.id, 'delete')"
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

    <ReviewActionModal
      :open="pendingAction !== null"
      :action="pendingAction ?? 'hide'"
      :processing="processing"
      @close="closeModal"
      @confirm="confirmAction"
    />
  </div>
</template>
