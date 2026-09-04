<script setup>
import { computed, ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { ROUTES } from '@/Helpers/routes'

const props = defineProps({
  error: { type: String, default: undefined },
  email: { type: String, required: true },
  requiresVerification: { type: Boolean, default: false },
})

const emit = defineEmits(['resetForm'])
const { t } = useI18n()
const page = usePage()

const isUnverified = computed(() => {
  return page.props.flash?.requires_verification === true || props.requiresVerification === true
})

const status = computed(() => {
  return page.props.status || page.props.flash?.status
})

const isSending = ref(false)

const resendEmail = () => {
  router.post(ROUTES.EMAIL_VERIFICATION_RESEND, { email: props.email }, {
    preserveScroll: true,
    onStart: () => { isSending.value = true },
    onFinish: () => { isSending.value = false },
    onSuccess: () => {
      emit('resetForm')
    },
  })
}
</script>

<template>
  <div v-if="error || status" class="flex flex-col items-center w-full min-h-6 mt-2 sm:mt-4">
    <div 
      v-if="status" 
      class="w-full bg-success/10 border border-success/40 rounded-lg px-4 py-3 flex items-center justify-center shadow-sm"
    >
      <span class="text-success text-sm sm:text-base font-medium text-center leading-snug">
        {{ status === 'verification-resend' ? t('auth.verification.resendMessage') : status }}
      </span>
    </div>

    <template v-else>
      <div class="bg-error/10 border border-error w-fit rounded-lg px-4 py-3 flex flex-col items-center justify-center shadow-sm gap-1.5">
        <span class="text-error-dark text-xs sm:text-sm font-medium text-center">
          {{ error }}
        </span>
        
        <button
          v-if="isUnverified"
          type="button"
          :disabled="isSending"
          class="text-error-dark underline cursor-pointer text-xs sm:text-sm font-medium focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-error/40 rounded transition-colors disabled:opacity-50 hover:text-error/90"
          @click="resendEmail"
        >
          {{ t('auth.verification.resendVerification') }}
        </button>
      </div>
    </template>
  </div>
</template>
