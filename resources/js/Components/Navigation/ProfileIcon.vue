<script setup>
import { useI18n } from 'vue-i18n'
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { ROUTES } from '@/Helpers/routes'
import ProfileAvatar from '@/Components/Student/ProfileAvatar.vue'
import { useAuthUser } from '@/Composables/useAuthUser'

const { t } = useI18n()
const page = usePage()

const { user, avatarUrl } = useAuthUser()

const isStudent = computed(() => user.value?.role === 'student')

const settingsLabel = computed(() => (
  isStudent.value
    ? t('student.profile.account.title')
    : t('buttons.settings')
))

const isOnMyProfile = computed(() => (
  page.component === 'Student/Profile' || page.component === 'Student/ProfileEdit'
))

const isOnSettings = computed(() => page.component === 'Student/Settings')

const isOpen = ref(false)
const dropdownRef = ref(null)
const triggerRef = ref(null)

const closeAndRestoreFocus = () => {
  isOpen.value = false
  triggerRef.value?.focus()
}

const handleClickOutside = (event) => {
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
  <div ref="dropdownRef" class="relative text-left" @keydown.escape="closeAndRestoreFocus">
    <button
      ref="triggerRef"
      type="button"
      class="flex h-10 w-10 items-center justify-center rounded-full transition-all hover:ring-2 hover:ring-link hover:ring-offset-2 hover:ring-offset-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-primary"
      :aria-expanded="isOpen"
      aria-haspopup="true"
      :aria-label="t('profiles.accountMenu')"
      @click="isOpen = !isOpen"
    >
      <ProfileAvatar
        :photo-url="avatarUrl"
        :first-name="user?.first_name"
        :last-name="user?.last_name"
        size-class="h-full w-full text-sm"
        color-class="bg-secondary text-accent"
      />
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
        class="absolute right-0 z-50 mt-3 w-56 origin-top-right overflow-hidden rounded-xl border border-border bg-white shadow-md focus:outline-none"
      >
        <div class="py-1">
          <Link
            :href="ROUTES.PROFILE"
            class="block px-4 py-2.5 text-sm font-medium transition-colors"
            :class="isOnMyProfile
              ? 'bg-primary/5 text-primary'
              : 'text-text hover:bg-background'"
            :aria-current="isOnMyProfile ? 'page' : undefined"
            @click="isOpen = false"
          >
            {{ t('buttons.myProfile') }}
          </Link>

          <Link
            :href="ROUTES.SETTINGS"
            class="block px-4 py-2.5 text-sm font-medium transition-colors"
            :class="isOnSettings
              ? 'bg-primary/5 text-primary'
              : 'text-text hover:bg-background'"
            :aria-current="isOnSettings ? 'page' : undefined"
            @click="isOpen = false"
          >
            {{ settingsLabel }}
          </Link>

          <hr class="my-1 border-border">

          <Link
            :href="ROUTES.LOGOUT"
            method="post"
            as="button"
            class="block w-full px-4 py-2.5 text-left text-sm font-medium text-error transition-colors hover:bg-red-50 hover:text-error-dark"
            @click="isOpen = false"
          >
            {{ t('buttons.logout') }}
          </Link>
        </div>
      </div>
    </transition>
  </div>
</template>
