<script setup>
import { computed, ref, watch } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import BaseInput from '@/Components/Base/BaseInput.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import ProfileSectionCard from '@/Components/Profile/ProfileSectionCard.vue'
import StudentDeleteAccountModal from '@/Components/Student/StudentDeleteAccountModal.vue'
import { ROUTES } from '@/Helpers/routes'

const props = defineProps({
  email: { type: String, required: true },
  emailVerifiedAt: { type: String, default: null },
  embedded: { type: Boolean, default: false },
})

const { t } = useI18n()
const showDeleteModal = ref(false)
const isSending = ref(false)

const isEmailUnverified = computed(() => !props.emailVerifiedAt)

const passwordForm = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
})

const emailForm = useForm({
  email: props.email,
})

watch(() => props.email, (nextEmail) => {
  emailForm.email = nextEmail
})

const passwordError = (field) => passwordForm.errors[field]
const emailError = (field) => emailForm.errors[field]

function submitPassword() {
  passwordForm.put(ROUTES.STUDENT_PASSWORD_UPDATE, {
    preserveScroll: true,
    onSuccess: () => passwordForm.reset(),
  })
}

function submitEmail() {
  emailForm.patch(ROUTES.STUDENT_EMAIL_UPDATE, { preserveScroll: true })
}

function resendVerification() {
  router.post(ROUTES.EMAIL_VERIFICATION_RESEND, { email: emailForm.email }, {
    preserveScroll: true,
    onStart: () => { isSending.value = true },
    onFinish: () => { isSending.value = false },
  })
}
</script>

<template>
  <component
    :is="embedded ? 'section' : ProfileSectionCard"
    v-bind="embedded
      ? { 'aria-label': t('student.profile.account.title') }
      : {
        title: t('student.profile.account.title'),
        description: t('student.profile.account.description'),
      }"
  >
    <header v-if="embedded" class="mb-4">
      <h2 class="font-semibold text-text text-base">
        {{ t('student.profile.account.title') }}
      </h2>
      <p class="mt-1 text-additional text-sm">
        {{ t('student.profile.account.description') }}
      </p>
    </header>

    <form class="flex flex-col gap-4 border-b border-border pb-6" novalidate @submit.prevent="submitPassword">
      <h3 class="font-medium text-text text-sm">
        {{ t('student.profile.password.title') }}
      </h3>
      <BaseInput
        id="current_password"
        v-model="passwordForm.current_password"
        type="password"
        :label="t('student.profile.password.current')"
        autocomplete="current-password"
        :error="passwordError('current_password')"
      />
      <BaseInput
        id="new_password"
        v-model="passwordForm.password"
        type="password"
        :label="t('student.profile.password.new')"
        autocomplete="new-password"
        :error="passwordError('password')"
      />
      <BaseInput
        id="password_confirmation"
        v-model="passwordForm.password_confirmation"
        type="password"
        :label="t('student.profile.password.confirmation')"
        autocomplete="new-password"
        :error="passwordError('password_confirmation')"
      />
      <BaseButton type="submit" :disabled="passwordForm.processing || !passwordForm.isDirty">
        {{ t('student.profile.actions.save') }}
      </BaseButton>
    </form>

    <form class="mt-6 flex flex-col gap-4 border-b border-border pb-6" novalidate @submit.prevent="submitEmail">
      <h3 class="font-medium text-text text-sm">
        {{ t('student.profile.email.title') }}
      </h3>
      <p
        v-if="isEmailUnverified"
        class="rounded-lg bg-amber-50 px-4 py-3 text-amber-900 text-sm ring-1 ring-amber-200"
        role="status"
      >
        {{ t('student.profile.email.unverifiedNotice', { email: emailForm.email }) }}
      </p>
      <BaseInput
        id="account_email"
        v-model="emailForm.email"
        type="email"
        :label="t('student.profile.email.label')"
        autocomplete="email"
        :error="emailError('email')"
      />
      <div class="flex flex-col gap-3 sm:flex-row">
        <BaseButton type="submit" :disabled="emailForm.processing || !emailForm.isDirty">
          {{ t('student.profile.actions.save') }}
        </BaseButton>
        <BaseButton
          v-if="isEmailUnverified"
          type="button"
          variant="secondary"
          :disabled="isSending"
          @click="resendVerification"
        >
          {{ t('student.profile.email.resend') }}
        </BaseButton>
      </div>
    </form>

    <div class="mt-6">
      <h3 class="font-medium text-text text-sm">
        {{ t('student.profile.delete.title') }}
      </h3>
      <p class="mt-2 text-additional text-sm">
        {{ t('student.profile.delete.warning') }}
      </p>
      <BaseButton
        type="button"
        variant="secondary"
        class="mt-3 border-error text-error"
        @click="showDeleteModal = true"
      >
        {{ t('student.profile.delete.openModal') }}
      </BaseButton>
    </div>
  </component>

  <StudentDeleteAccountModal
    :open="showDeleteModal"
    @close="showDeleteModal = false"
  />
</template>
