<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import BaseModal from '@/Components/Base/BaseModal.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'

const props = defineProps({
  open: { type: Boolean, required: true },
  partnerName: { type: String, default: '' },
  processing: { type: Boolean, default: false },
  action: { type: String, default: 'propose' },
  namespace: { type: String, required: true },
  error: { type: String, default: null },
})

const emit = defineEmits(['close', 'confirm'])
const { t } = useI18n()

const title = computed(() => t(`${props.namespace}.${props.action}.modalTitle`))
const confirmation = computed(() => t(`${props.namespace}.${props.action}.confirmation`, {
  partner: props.partnerName,
}))
const confirmLabel = computed(() => t(`${props.namespace}.${props.action}.confirm`))
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
    <p v-if="error" class="mt-3 text-error text-sm" role="alert">
      {{ error }}
    </p>
    <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
      <BaseButton type="button" variant="secondary" :disabled="processing" @click="emit('close')">
        {{ t(`${namespace}.cancelAction`) }}
      </BaseButton>
      <BaseButton type="button" :disabled="processing" @click="emit('confirm')">
        {{ confirmLabel }}
      </BaseButton>
    </div>
  </BaseModal>
</template>
