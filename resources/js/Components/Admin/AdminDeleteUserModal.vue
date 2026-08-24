<script setup>
import { useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import BaseModal from '@/Components/Base/BaseModal.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import { ROUTES } from '@/Helpers/routes'

const props = defineProps({
  userId: { type: String, default: null },
  userName: { type: String, default: '' },
  currentStatus: { type: String, default: '' },
  open: { type: Boolean, required: true },
})

const emit = defineEmits(['close'])
const { t } = useI18n()
const form = useForm()

function submit() {
  form.delete(ROUTES.ADMIN_DELETE_USER(props.userId), {
    preserveScroll: true,
    onSuccess: () => emit('close'),
  })
}
</script>

<template>
  <BaseModal
    :open="open"
    :title="t('admin.users.deleteModal.deleteTitle')"
    max-width-class="max-w-lg"
    @close="emit('close')"
  >
    <form class="flex flex-col gap-6" novalidate @submit.prevent="submit">
      <p class="text-additional text-sm leading-relaxed">
        {{ t('admin.users.deleteModal.deleteDescription', { name: userName }) }}
      </p>
      <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <BaseButton type="button" variant="secondary" :disabled="form.processing" @click="emit('close')">
          {{ t('admin.users.deleteModal.cancel') }}
        </BaseButton>
        <BaseButton type="submit" :disabled="form.processing">
          {{ t('admin.users.deleteModal.confirmDelete') }}
        </BaseButton>
      </div>
    </form>
  </BaseModal>
</template>
