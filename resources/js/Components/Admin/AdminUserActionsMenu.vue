<script setup>
import { useI18n } from 'vue-i18n'
import { IconUserCog, IconLock, IconLockOpen, IconX } from '@tabler/icons-vue'
import AdminActionsMenu from '@/Components/Admin/AdminActionsMenu.vue'

const { t } = useI18n()

const props = defineProps({
  user: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['change-role', 'toggle-block', 'delete-user'])
</script>

<template>
  <AdminActionsMenu :label="t('admin.users.actionsMenu', { name: props.user.email })">
    <button
      type="button"
      class="flex items-center gap-2 w-full px-3 py-2 text-left text-text hover:bg-gray-50 cursor-pointer"
      role="menuitem"
      @click="emit('change-role', props.user)"
    >
      <IconUserCog class="w-4 h-4" aria-hidden="true" />
      {{ t('admin.users.changeRole') }}
    </button>

    <button
      type="button"
      class="flex items-center gap-2 w-full px-3 py-2 text-left text-red-600 hover:bg-red-50 cursor-pointer"
      role="menuitem"
      @click="emit('toggle-block', props.user)"
    >
      <IconLockOpen v-if="props.user.status === 'blocked'" class="w-4 h-4" aria-hidden="true" />
      <IconLock v-else class="w-4 h-4" aria-hidden="true" />
      {{ props.user.status === 'blocked' ? t('admin.users.unblock') : t('admin.users.block') }}
    </button>

    <button
      type="button"
      class="flex items-center gap-2 w-full px-3 py-2 text-left text-red-600 hover:bg-red-50 cursor-pointer"
      role="menuitem"
      @click="emit('delete-user', props.user)"
    >
      <IconX class="w-4 h-4" aria-hidden="true" />
      {{ t('admin.users.deleteModal.confirmDelete') }}
    </button>
  </AdminActionsMenu>
</template>
