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
  member: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['close'])
</script>

<template>
  <BaseModal
    :open="open"
    :title="title"
    @close="emit('close')"
  >
    <div v-if="member" class="flex flex-col gap-5">
      <div class="flex items-center gap-3">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-background text-lg font-semibold text-additional">
          {{ member.name?.charAt(0) || 'U' }}
        </div>
        <div class="min-w-0">
          <p class="truncate text-lg font-semibold text-text">
            {{ member.name }}
          </p>
          <p class="text-sm text-additional">
            {{ $t(`organization.team.roles.${member.role}`) }}
          </p>
        </div>
      </div>

      <dl class="space-y-3">
        <div>
          <dt class="text-xs font-medium uppercase tracking-wide text-additional">
            {{ $t('organization.team.memberPreview.email') }}
          </dt>
          <dd class="mt-1 text-sm text-text">
            {{ member.email }}
          </dd>
        </div>
        <div>
          <dt class="text-xs font-medium uppercase tracking-wide text-additional">
            {{ $t('organization.team.memberPreview.joined') }}
          </dt>
          <dd class="mt-1 text-sm text-text">
            {{ $t('organization.team.joinDate', { date: member.joinedAt ? new Intl.DateTimeFormat(undefined, { year: 'numeric', month: 'short', day: 'numeric' }).format(new Date(member.joinedAt)) : '' }) }}
          </dd>
        </div>
      </dl>

      <div class="flex justify-end">
        <BaseButton type="button" variant="secondary" @click="emit('close')">
          {{ $t('organization.team.memberPreview.close') }}
        </BaseButton>
      </div>
    </div>
  </BaseModal>
</template>
