<script setup>
import { useI18n } from 'vue-i18n'
import { IconUserCog, IconLock, IconLockOpen } from '@tabler/icons-vue'

const { t } = useI18n()

const props = defineProps({
  user: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['change-role', 'toggle-block'])
</script>

<template>
  <div class="flex items-center justify-end gap-1">
    <button
      type="button"
      class="p-1.5 rounded-md text-additional hover:bg-gray-100 hover:text-text cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
      :title="t('admin.users.changeRole')"
      :aria-label="t('admin.users.changeRoleAriaLabel', { name: props.user.email })"
      @click="emit('change-role', props.user)"
    >
      <IconUserCog class="w-4 h-4" aria-hidden="true" />
    </button>

    <button
      type="button"
      class="p-1.5 rounded-md text-red-600 hover:bg-red-50 cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
      :title="props.user.status === 'blocked' ? t('admin.users.unblock') : t('admin.users.block')"
      :aria-label="props.user.status === 'blocked'
        ? t('admin.users.unblockAriaLabel', { name: props.user.email })
        : t('admin.users.blockAriaLabel', { name: props.user.email })"
      @click="emit('toggle-block', props.user)"
    >
      <IconLockOpen v-if="props.user.status === 'blocked'" class="w-4 h-4" aria-hidden="true" />
      <IconLock v-else class="w-4 h-4" aria-hidden="true" />
    </button>
  </div>
</template>
