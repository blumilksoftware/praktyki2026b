<script setup>
import BaseButton from '@/Components/Base/BaseButton.vue'
import BaseModal from '@/Components/Base/BaseModal.vue'

const props = defineProps({
  open: {
    type: Boolean,
    default: false,
  },
  user: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['close'])

function fullName(user) {
  return [user.first_name, user.last_name].filter(Boolean).join(' ') || user.email
}

function formattedDate(date) {
  if (!date) return ''
  return new Intl.DateTimeFormat(undefined, { year: 'numeric', month: 'short', day: 'numeric' }).format(new Date(date))
}
</script>

<template>
  <BaseModal
    :open="open"
    :title="user ? fullName(user) : ''"
    @close="emit('close')"
  >
    <div v-if="user" class="flex flex-col gap-5">
      <div class="flex items-center gap-3">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-background text-lg font-semibold text-additional">
          {{ fullName(user).charAt(0) || 'U' }}
        </div>
        <div class="min-w-0">
          <p class="truncate text-lg font-semibold text-text">
            {{ fullName(user) }}
          </p>
          <p class="text-sm text-additional">
            {{ $t(`admin.users.roles.${user.role}`) }}
          </p>
        </div>
      </div>
      <dl class="space-y-3">
        <div>
          <dt class="text-xs font-medium uppercase tracking-wide text-additional">
            {{ $t('admin.users.email') }}
          </dt>
          <dd class="mt-1 text-sm text-text">
            {{ user.email }}
          </dd>
        </div>
        <div>
          <dt class="text-xs font-medium uppercase tracking-wide text-additional">
            {{ $t('admin.users.status') }}
          </dt>
          <dd class="mt-1 text-sm text-text">
            {{ $t(`admin.users.statuses.${user.status}`) }}
          </dd>
        </div>
        <div>
          <dt class="text-xs font-medium uppercase tracking-wide text-additional">
            {{ $t('admin.users.joined') }}
          </dt>
          <dd class="mt-1 text-sm text-text">
            {{ formattedDate(user.created_at) }}
          </dd>
        </div>
      </dl>
      <div class="flex justify-end">
        <BaseButton type="button" variant="secondary" @click="emit('close')">
          {{ $t('admin.users.close') }}
        </BaseButton>
      </div>
    </div>
  </BaseModal>
</template>
