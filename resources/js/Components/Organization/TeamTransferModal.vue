<script setup>
import { ref, watch } from 'vue'
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
  members: {
    type: Array,
    default: () => [],
  },
  processing: {
    type: Boolean,
    default: false,
  },
  error: {
    type: String,
    default: '',
  },
})

const emit = defineEmits(['close', 'confirm'])

const selectedMemberId = ref('')

watch(() => props.open, (isOpen) => {
  if (isOpen) {
    selectedMemberId.value = props.members[0]?.id ?? ''
  }
})

function handleConfirm() {
  if (!selectedMemberId.value) {
    return
  }

  emit('confirm', selectedMemberId.value)
}
</script>

<template>
  <BaseModal
    :open="open"
    :title="title"
    @close="emit('close')"
  >
    <div class="flex flex-col gap-6">
      <p class="text-sm leading-relaxed text-additional">
        {{ $t('organization.team.transferModal.description') }}
      </p>

      <div v-if="members.length === 0" class="rounded-xl border border-dashed border-border bg-background px-4 py-6 text-center text-sm text-additional">
        {{ $t('organization.team.transferModal.noMembers') }}
      </div>

      <div v-else class="flex flex-col gap-1.5">
        <label for="transfer-member" class="text-sm font-medium text-text">
          {{ $t('organization.team.transferModal.selectLabel') }}
        </label>
        <select
          id="transfer-member"
          v-model="selectedMemberId"
          class="w-full rounded-lg border border-border bg-white px-4 py-2.5 text-sm text-text outline-none transition focus:border-primary/50 focus:ring-2 focus:ring-primary/20"
        >
          <option v-for="member in members" :key="member.id" :value="member.id">
            {{ member.name }} ({{ member.email }})
          </option>
        </select>
      </div>

      <p v-if="error" class="text-sm text-error" role="alert">
        {{ error }}
      </p>

      <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <BaseButton type="button" variant="secondary" :disabled="processing" @click="emit('close')">
          {{ $t('organization.team.transferModal.cancel') }}
        </BaseButton>
        <BaseButton
          type="button"
          :disabled="processing || members.length === 0 || !selectedMemberId"
          @click="handleConfirm"
        >
          {{ $t('organization.team.transferModal.confirm') }}
        </BaseButton>
      </div>
    </div>
  </BaseModal>
</template>
