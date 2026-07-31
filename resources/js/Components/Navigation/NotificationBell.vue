<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { usePage, router, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { IconBell } from '@tabler/icons-vue'
import { ROUTES, notificationRead } from '@/Helpers/routes'

const { t, locale } = useI18n()
const page = usePage()
const isOpen = ref(false)
const dropdownRef = ref(null)
const unreadCount = computed(() => page.props.notificationsUnreadCount ?? 0)
const notifications = computed(() => page.props.notifications ?? [])
const hasUnread = computed(() => unreadCount.value > 0)
const unreadBadge = computed(() => (unreadCount.value > 9 ? '9+' : String(unreadCount.value)))
const bellAriaLabel = computed(() => (hasUnread.value
  ? t('notifications.bell.unreadAriaLabel', { count: unreadCount.value })
  : t('notifications.bell.ariaLabel')))

function loadNotifications() {
  router.reload({
    only: ['notifications'],
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

onMounted(() => {
  document.addEventListener('mousedown', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('mousedown', handleClickOutside)
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
      <IconBell stroke="2" class="h-6 w-6" />
      <span
        v-if="hasUnread"
        class="absolute top-1 right-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-error px-1 text-[10px] font-semibold text-white"
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
            class="shrink-0 whitespace-nowrap rounded-md border border-primary/30 px-2 py-1 text-xs font-medium text-primary hover:bg-primary/5"
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
              class="block w-full px-4 py-3 text-left text-sm transition-colors hover:bg-background"
              :class="item.read_at ? 'text-additional' : 'bg-primary/5 font-medium text-text'"
              @click="isOpen = false"
            >
              <p>{{ notificationLabel(item) }}</p>
              <p class="mt-1 text-xs text-additional">{{ formatCreatedAt(item.created_at) }}</p>
            </Link>
          </li>
        </ul>
      </div>
    </transition>
  </div>
</template>
