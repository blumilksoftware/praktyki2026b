<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import StarRating from '@/Components/Common/StarRating.vue'
import BaseTextarea from '@/Components/Base/BaseTextarea.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import { companyReviewsStore } from '@/Helpers/routes'

const props = defineProps({
  companyId: { type: String, required: true },
})

const { t } = useI18n()

const rating = ref(0)
const comment = ref('')
const errors = ref({})
const processing = ref(false)

function submit() {
  errors.value = {}
  processing.value = true

  router.post(companyReviewsStore(props.companyId), {
    rating: rating.value,
    comment: comment.value,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      rating.value = 0
      comment.value = ''
    },
    onError: (serverErrors) => {
      errors.value = serverErrors
    },
    onFinish: () => {
      processing.value = false
    },
  })
}
</script>

<template>
  <form class="flex flex-col gap-4" novalidate @submit.prevent="submit">
    <div class="flex flex-col gap-1.5">
      <span class="text-sm font-medium text-text">{{ t('profiles.reviews.ratingLabel') }}</span>
      <StarRating :rating="rating" interactive @update:rating="rating = $event" />
      <p v-if="errors.rating" class="text-sm text-error" role="alert">{{ errors.rating }}</p>
    </div>

    <BaseTextarea
      id="review-comment"
      v-model="comment"
      :label="t('profiles.reviews.commentLabel')"
      :maxlength="1000"
      :rows="3"
      :error="errors.comment"
    />

    <p v-if="errors.company" class="text-sm text-error" role="alert">{{ errors.company }}</p>

    <BaseButton
      type="submit"
      variant="primary"
      class="w-full justify-center sm:w-auto"
      :disabled="processing || rating === 0"
    >
      {{ processing ? t('profiles.reviews.submitting') : t('profiles.reviews.submit') }}
    </BaseButton>
  </form>
</template>
