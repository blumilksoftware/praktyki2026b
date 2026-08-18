<script setup>
import { ref, watch, nextTick, onMounted, onUnmounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { IconDotsVertical, IconUserCog, IconLock, IconLockOpen } from '@tabler/icons-vue'

const MENU_WIDTH = 176
const VIEWPORT_MARGIN = 8

const { t } = useI18n()

const props = defineProps({
  user: {
    type: Object,
    required: true,
  },
  isOpen: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['toggle', 'change-role', 'toggle-block', 'close'])

const triggerRef = ref(null)
const menuStyle = ref({})
const isTriggerVisible = ref(false)

function positionMenu() {
  const trigger = triggerRef.value

  if (!trigger) return

  const rect = trigger.getBoundingClientRect()

  isTriggerVisible.value = rect.width > 0 && rect.height > 0

  if (!isTriggerVisible.value) return

  const left = Math.min(
    Math.max(VIEWPORT_MARGIN, rect.right - MENU_WIDTH),
    window.innerWidth - MENU_WIDTH - VIEWPORT_MARGIN,
  )

  menuStyle.value = {
    top: `${rect.bottom + 4}px`,
    left: `${left}px`,
    width: `${MENU_WIDTH}px`,
  }
}

watch(() => props.isOpen, async (isOpen) => {
  if (!isOpen) {
    isTriggerVisible.value = false

    return
  }

  await nextTick()
  positionMenu()
})

function closeOnScroll() {
  if (props.isOpen) {
    emit('close')
  }
}

onMounted(() => window.addEventListener('scroll', closeOnScroll, true))
onUnmounted(() => window.removeEventListener('scroll', closeOnScroll, true))
</script>

<template>
  <div class="inline-flex items-center" :data-user-menu="user.id">
    <button
      ref="triggerRef"
      type="button"
      class="flex justify-center items-center hover:bg-gray-100 rounded-lg w-9 h-9 text-additional cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
      :aria-label="t('admin.users.actionsMenu', { name: props.user.email })"
      :aria-expanded="isOpen"
      @click="emit('toggle', props.user.id)"
    >
      <IconDotsVertical class="w-5 h-5" />
    </button>

    <Teleport to="body">
      <div
        v-if="isOpen && isTriggerVisible"
        class="z-50 fixed bg-white shadow-lg py-1 border border-border rounded-lg"
        :style="menuStyle"
        role="menu"
        data-user-menu-dropdown
      >
        <button
          type="button"
          class="flex items-center gap-2 hover:bg-gray-50 px-3 py-2 w-full text-text text-left cursor-pointer"
          @click="emit('change-role', props.user)"
        >
          <IconUserCog class="w-4 h-4" />
          {{ t('admin.users.changeRole') }}
        </button>

        <button
          type="button"
          class="flex items-center gap-2 hover:bg-red-50 px-3 py-2 w-full text-red-600 text-left cursor-pointer"
          @click="emit('toggle-block', props.user)"
        >
          <IconLockOpen v-if="props.user.status === 'blocked'" class="w-4 h-4" />
          <IconLock v-else class="w-4 h-4" />
          {{ props.user.status === 'blocked' ? t('admin.users.unblock') : t('admin.users.block') }}
        </button>
      </div>
    </Teleport>
  </div>
</template>
