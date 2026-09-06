<script setup>
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import BaseModal from '@/Components/Base/BaseModal.vue'
import BaseInput from '@/Components/Base/BaseInput.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import BaseCheckbox from '@/Components/Base/BaseCheckbox.vue'

const props = defineProps({
  open: { type: Boolean, required: true },
  i18nPrefix: { type: String, required: true },
  accountDeleteRoute: { type: String, required: true },
})

const emit = defineEmits(['close'])
const { t } = useI18n()

const form = useForm({
  password: '',
  confirmation: false,
})

const canDelete = computed(() => form.confirmation && form.password.length > 0)
const fieldError = (field) => form.errors[field]

function submit() {
  form.delete(props.accountDeleteRoute, {
    onSuccess: () => emit('close'),
  })
}
</script>

<template>
  <BaseModal
    :open="open"
    :title="t(`${i18nPrefix}.delete.modalTitle`)"
    max-width-class="max-w-lg"
    @close="emit('close')"
  >
    <form class="flex flex-col gap-4" novalidate @submit.prevent="submit">
      <BaseCheckbox
        id="delete_confirmation"
        v-model="form.confirmation"
        :label="t(`${i18nPrefix}.delete.confirmationLabel`)"
      />
      <BaseInput
        id="delete_password"
        v-model="form.password"
        type="password"
        :label="t(`${i18nPrefix}.delete.password`)"
        autocomplete="current-password"
        :error="fieldError('password')"
      />
      <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <BaseButton type="button" variant="secondary" @click="emit('close')">
          {{ t(`${i18nPrefix}.delete.cancel`) }}
        </BaseButton>
        <BaseButton type="submit" :disabled="!canDelete || form.processing">
          {{ t(`${i18nPrefix}.delete.confirm`) }}
        </BaseButton>
      </div>
    </form>
  </BaseModal>
</template>
