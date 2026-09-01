<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { usePage, router, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { IconBell } from '@tabler/icons-vue'
import { ROUTES, notificationRead } from '@/Helpers/routes'
import { useToast } from '@/Composables/useToast'

const POLL_INTERVAL_MS = 30000

const { t, locale } = useI18n()
const { toastInfo } = useToast()
const page = usePage()
const isOpen = ref(false)
const dropdownRef = ref(null)
let pollTimer = null
const unreadCount = computed(() => page.props.notificationsUnreadCount ?? 0)
const notifications = computed(() => page.props.notifications ?? [])
const hasUnread = computed(() => unreadCount.value > 0)
const unreadBadge = computed(() => (unreadCount.value > 9 ? '9+' : String(unreadCount.value)))
const bellAriaLabel = computed(() => (hasUnread.value
  ? t('notifications.bell.unreadAriaLabel', { count: unreadCount.value })
  : t('notifications.bell.ariaLabel')))

function loadNotifications() {
  router.reload({
    only: ['notifications', 'notificationsUnreadCount'],
    preserveScroll: true,
    preserveState: true,
  })
}

function toggle() {
  isOpen.value = !isOpen.value

  if (isOpen.value) {
    loadNotifications()
  }
}

function markAllAsRead() {
  router.patch(ROUTES.NOTIFICATIONS_READ_ALL, {}, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => loadNotifications(),
  })
}

function notificationLabel(item) {
  const data = item.data || {}

  switch (item.type) {
  case 'verification_request':
    return t('notifications.types.verificationRequest', { name: data.entity_name })
  case 'new_application':
    return t('notifications.types.newApplication', { student: data.student_name, offer: data.offer_title })
  case 'application_status_changed':
    return t('notifications.types.statusChanged', {
      offer: data.offer_title,
      status: t(`student.applications.status.${data.status}`),
    })
  case 'offer_unavailable':
    return t(`notifications.types.offerUnavailable.${data.reason}`, { offer: data.offer_title })
  case 'partnership_requested':
    return t('notifications.types.partnershipRequested', { name: data.proposer_name })
  case 'partnership_accepted':
    return t('notifications.types.partnershipAccepted', { name: data.acceptor_name })
  case 'partnership_cancelled':
    return t('notifications.types.partnershipCancelled', { name: data.canceller_name })
  case 'partnership_declined':
    return t('notifications.types.partnershipDeclined', { name: data.decliner_name })
  case 'partnership_ended':
    return t('notifications.types.partnershipEnded', { name: data.ender_name })
  default:
    return t('notifications.types.fallback')
  }
}

function formatCreatedAt(dateString) {
  if (!dateString) {
    return ''
  }

  return new Date(dateString).toLocaleDateString(locale.value === 'en' ? 'en-GB' : 'pl-PL', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

function handleClickOutside(event) {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    isOpen.value = false
  }
}

function handleKeydown(event) {
  if (event.key === 'Escape' && isOpen.value) {
    isOpen.value = false
  }
}

function pollUnreadCount() {
  router.reload({
    only: ['notificationsUnreadCount'],
    preserveScroll: true,
    preserveState: true,
  })
}

function startPolling() {
  if (pollTimer) {
    return
  }

  pollTimer = window.setInterval(pollUnreadCount, POLL_INTERVAL_MS)
}

function stopPolling() {
  if (pollTimer) {
    window.clearInterval(pollTimer)
    pollTimer = null
  }
}

function handleVisibilityChange() {
  if (document.hidden) {
    stopPolling()
  } else {
    startPolling()
    pollUnreadCount()
  }
}

watch(unreadCount, (newCount, oldCount) => {
  if (newCount > oldCount) {
    const count = newCount - oldCount
    toastInfo(t('notifications.toast.new', { count }, count))
  }
})

onMounted(() => {
  document.addEventListener('mousedown', handleClickOutside)
  document.addEventListener('keydown', handleKeydown)
  document.addEventListener('visibilitychange', handleVisibilityChange)
  startPolling()
})

onUnmounted(() => {
  document.removeEventListener('mousedown', handleClickOutside)
  document.removeEventListener('keydown', handleKeydown)
  document.removeEventListener('visibilitychange', handleVisibilityChange)
  stopPolling()
})
</script>

<template>
  <div ref="dropdownRef" class="relative inline-block text-left">
    <button
      type="button"
      class="relative flex h-10 w-10 items-center justify-center rounded-full text-white/90 transition-colors hover:bg-white/10 hover:text-white focus:outline-none"
      :aria-label="bellAriaLabel"
      aria-haspopup="true"
      :aria-expanded="isOpen"
      @click="toggle"
    >
      <IconBell stroke="2" class="h-6 w-6" aria-hidden="true" />
      <span
        v-if="hasUnread"
        class="absolute top-1 right-1 flex h-[18px] min-w-[18px] items-center justify-center rounded-full bg-secondary px-1 text-[11px] font-semibold text-white"
      >
        {{ unreadBadge }}
      </span>
    </button>

    <transition
      enter-active-class="transition ease-out duration-100"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div
        v-if="isOpen"
        class="absolute right-0 z-50 mt-3 w-96 max-w-[90vw] origin-top-right overflow-hidden rounded-xl border border-border bg-white shadow-md focus:outline-none"
      >
        <div class="flex items-center justify-between gap-3 border-b border-border px-4 py-3">
          <span class="min-w-0 flex-1 truncate text-sm font-semibold text-text">{{ t('notifications.bell.ariaLabel') }}</span>
          <button
            v-if="hasUnread"
            type="button"
            class="inline-flex shrink-0 items-center justify-center whitespace-nowrap rounded-md border border-primary/30 px-2 py-1 text-xs font-medium text-primary hover:bg-primary/5"
            @click="markAllAsRead"
          >
            {{ t('notifications.markAllAsRead') }}
          </button>
        </div>

        <ul class="max-h-96 divide-y divide-border overflow-y-auto">
          <li v-if="notifications.length === 0" class="px-4 py-6 text-center text-sm text-additional">
            {{ t('notifications.empty') }}
          </li>

          <li v-for="item in notifications" :key="item.id">
            <Link
              :href="notificationRead(item.id)"
              method="patch"
              as="button"
              preserve-scroll
              class="relative block w-full py-3 pr-4 pl-8 text-left text-sm transition-colors"
              :class="item.read_at ? 'text-additional hover:bg-background' : 'bg-secondary/10 font-medium text-text hover:bg-secondary/15'"
              @click="isOpen = false"
            >
              <span
                v-if="!item.read_at"
                class="absolute top-4 left-3 h-2 w-2 rounded-full bg-secondary"
              />
              <p>{{ notificationLabel(item) }}</p>
              <p class="mt-1 text-xs text-additional">{{ formatCreatedAt(item.created_at) }}</p>
            </Link>
          </li>
        </ul>
      </div>
    </transition>
  </div>
</template>
