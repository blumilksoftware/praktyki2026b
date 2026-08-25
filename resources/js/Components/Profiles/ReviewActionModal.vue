<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import BaseModal from '@/Components/Base/BaseModal.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'

const props = defineProps({
  open: { type: Boolean, required: true },
  action: { type: String, default: 'hide' },
  processing: { type: Boolean, default: false },
})

const emit = defineEmits(['close', 'confirm'])
const { t } = useI18n()

const title = computed(() => t(`profiles.reviews.${props.action}ConfirmTitle`))
const confirmation = computed(() => t(`profiles.reviews.${props.action}Confirm`))
const confirmLabel = computed(() => t(`profiles.reviews.${props.action}`))
</script>

<template>
  <BaseModal
    :open="open"
    :title="title"
    max-width-class="max-w-lg"
    @close="emit('close')"
  >
    <p class="text-additional text-sm">
      {{ confirmation }}
    </p>
    <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
      <BaseButton type="button" variant="secondary" :disabled="processing" @click="emit('close')">
        {{ t('buttons.cancel') }}
      </BaseButton>
      <BaseButton type="button" :disabled="processing" @click="emit('confirm')">
        {{ confirmLabel }}
      </BaseButton>
    </div>
  </BaseModal>
</template>
