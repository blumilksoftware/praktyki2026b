<script setup>
import BaseButton from '@/Components/Base/BaseButton.vue'
import BaseModal from '@/Components/Common/BaseModal.vue'

const props = defineProps({
  open: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    required: true,
  },
  email: {
    type: String,
    default: '',
  },
  emailError: {
    type: String,
    default: '',
  },
  processing: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['close', 'submit', 'update:email'])
</script>

<template>
  <BaseModal
    :open="open"
    :title="title"
    @close="emit('close')"
  >
    <form class="flex flex-col gap-6" novalidate @submit.prevent="emit('submit')">
      <div class="flex flex-col gap-2">
        <label class="text-sm font-medium text-text" for="team-invite-email">
          {{ $t('organization.team.inviteModal.emailLabel') }}
          <span aria-hidden="true" class="text-error">*</span>
        </label>
        <input
          id="team-invite-email"
          :value="email"
          type="email"
          autocomplete="email"
          required
          :aria-invalid="emailError ? true : undefined"
          :class="[
            'w-full rounded-lg border bg-white px-4 py-3 text-sm text-text outline-none transition focus:ring-2',
            emailError ? 'border-error focus:border-error focus:ring-error/30' : 'border-border focus:border-primary focus:ring-primary/20',
          ]"
          :placeholder="$t('organization.team.inviteModal.emailPlaceholder')"
          @input="emit('update:email', $event.target.value)"
        >
        <p v-if="emailError" class="text-sm text-error">
          {{ emailError }}
        </p>
      </div>

      <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <BaseButton type="button" variant="secondary" :disabled="processing" @click="emit('close')">
          {{ $t('organization.team.inviteModal.cancel') }}
        </BaseButton>
        <BaseButton type="submit" :disabled="processing">
          {{ $t('organization.team.inviteModal.submit') }}
        </BaseButton>
      </div>
    </form>
  </BaseModal>
</template>
