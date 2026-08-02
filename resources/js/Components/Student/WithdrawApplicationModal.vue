<script setup>
import { useI18n } from 'vue-i18n'
import BaseModal from '@/Components/Common/BaseModal.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'

defineProps({
  open: { type: Boolean, required: true },
  offerTitle: { type: String, default: '' },
  processing: { type: Boolean, default: false },
})

const emit = defineEmits(['close', 'confirm'])
const { t } = useI18n()
</script>

<template>
  <BaseModal
    :open="open"
    :title="t('student.applications.withdraw.modalTitle')"
    max-width-class="max-w-lg"
    @close="emit('close')"
  >
    <p class="text-additional text-sm">
      {{ t('student.applications.withdraw.confirmation', { title: offerTitle }) }}
    </p>
    <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
      <BaseButton type="button" variant="secondary" :disabled="processing" @click="emit('close')">
        {{ t('student.applications.withdraw.cancel') }}
      </BaseButton>
      <BaseButton type="button" :disabled="processing" @click="emit('confirm')">
        {{ t('student.applications.withdraw.confirm') }}
      </BaseButton>
    </div>
  </BaseModal>
</template>
