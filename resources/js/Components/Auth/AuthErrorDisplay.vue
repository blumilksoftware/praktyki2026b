<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  error: { type: String, default: undefined },
  email: { type: String, required: true },
  requiresVerification: { type: Boolean, default: false },
})

const { t } = useI18n()
const page = usePage()

const isUnverified = computed(() => {
  return page.props.flash?.requires_verification === true || props.requiresVerification === true
})

const status = computed(() => {
  const pageProps = page.props
  return pageProps.status || pageProps.flash?.status
})
</script>

<template>
  <div class="flex flex-col items-center justify-between mt-2 sm:mt-4 w-full min-h-[1.5rem]">
    <span v-if="error" class="text-error text-xs sm:text-sm font-medium">
      {{ error }}
    </span>

    <span v-if="status" class="text-green-600 text-xs sm:text-sm font-medium mt-1">
      {{ status === 'verification-resend' ? t('auth.verification.resend_message') : status }}
    </span>

    <Link
      v-if="isUnverified"
      href="/email/resend"
      method="post"
      as="button"
      :data="{ email: email }"
      class="text-link justify-end hover:underline cursor-pointer text-xs sm:text-sm font-medium focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 rounded mt-1.5 transition-colors"
    >
      {{ t('auth.verification.resendVerification') }}
    </Link>
  </div>
</template>
