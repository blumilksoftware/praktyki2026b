<script setup>
import BaseButton from '@/Components/Base/BaseButton.vue'
import BaseModal from '@/Components/Base/BaseModal.vue'

const props = defineProps({
  open: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    required: true,
  },
  memberName: {
    type: String,
    default: '',
  },
  processing: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['close', 'confirm'])
</script>

<template>
  <BaseModal
    :open="open"
    :title="title"
    @close="emit('close')"
  >
    <div class="flex flex-col gap-6">
      <p class="text-sm leading-relaxed text-additional">
        {{ $t('organization.team.removeModal.description', { name: memberName }) }}
      </p>

      <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <BaseButton type="button" variant="secondary" :disabled="processing" @click="emit('close')">
          {{ $t('organization.team.removeModal.cancel') }}
        </BaseButton>
        <BaseButton type="button" :disabled="processing" @click="emit('confirm')">
          {{ $t('organization.team.removeModal.confirm') }}
        </BaseButton>
      </div>
    </div>
  </BaseModal>
</template>
