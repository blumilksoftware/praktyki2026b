<script setup>
import { ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AuthLayout from '@/Components/Layouts/AuthLayout.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import { useResendCooldown } from '@/composables/useResendCooldown'

const props = defineProps({
  email: { type: String, required: true },
})

const { t } = useI18n()
const { remaining, canResend, startCooldown } = useResendCooldown()

const showSuccess = ref(false)

const resendForm = useForm({
  email: props.email,
})

const resend = () => {
  if (!canResend.value || resendForm.processing) {
    return
  }

  resendForm.post('/email/resend', {
    preserveScroll: true,
    onSuccess: () => {
      showSuccess.value = true
      startCooldown()
    },
  })
}
</script>

<template>
  <Head :title="t('auth.verification.waiting.title')" />

  <AuthLayout :title="t('auth.verification.waiting.heading')">
    <div class="flex flex-col gap-6 text-center">
      <p class="text-sm text-text">
        {{ t('auth.verification.waiting.sentTo') }}
      </p>

      <p class="text-lg font-semibold text-text break-all">
        {{ email }}
      </p>

      <p class="text-sm text-additional">
        {{ t('auth.verification.waiting.expiresInfo') }}
      </p>

      <p
        v-if="showSuccess"
        class="rounded-lg border border-green-200 bg-green-50
               px-4 py-3 text-sm text-green-800"
        role="status"
      >
        {{ t('auth.verification.waiting.resendSuccess') }}
      </p>

      <BaseButton
        type="button"
        :disabled="!canResend || resendForm.processing"
        @click="resend"
      >
        <template v-if="canResend">
          {{ t('auth.verification.waiting.resend') }}
        </template>
        <template v-else>
          {{ t('auth.verification.waiting.resendCooldown', {
            seconds: remaining,
          }) }}
        </template>
      </BaseButton>

      <Link
        href="/login"
        class="text-sm text-primary hover:underline
               focus-visible:outline-none focus-visible:ring-2
               focus-visible:ring-primary/40 rounded"
      >
        {{ t('auth.verification.waiting.backToLogin') }}
      </Link>
    </div>
  </AuthLayout>
</template>
