<script setup>
import { computed } from 'vue'
import { usePage, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import BaseLogo from '@/Components/Navigation/BaseLogo.vue'
import LanguageSwitcher from '@/Components/Navigation/LanguageSwitcher.vue'
import NotificationBell from '@/Components/Navigation/NotificationBell.vue'
import ProfileIcon from '@/Components/Navigation/ProfileIcon.vue'
import ProfileAvatar from '@/Components/Student/ProfileAvatar.vue'
import BaseNavigationButtons from '@/Components/Navigation/BaseNavigationButtons.vue'
import { IconMenu2, IconX, IconUserCircle, IconSettings, IconLogout, IconSearch} from '@tabler/icons-vue'
import { useMobileMenu } from '@/Composables/useMobileMenu'
import { useAuthUser } from '@/Composables/useAuthUser'
import { ROUTES } from '@/Helpers/routes'

const { t } = useI18n()
const page = usePage()

defineOptions({ inheritAttrs: false })

const props = defineProps({
  showHamburger: {
    type: Boolean,
    default: false,
  },
  menuItems: {
    type: Array,
    default: () => [],
  },
  showNavigationButtons: {
    type: Boolean,
    default: false,
  },
  navigationButtons: {
    type: Array,
    default: () => [],
  },
  navigationVariant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'outline', 'ghost'].includes(value),
  },
})

const emit = defineEmits(['navigationClick'])

const { user, fullName, displayName, emailName, avatarUrl } = useAuthUser()
const isAuthenticated = computed(() => !!user.value)
const isAuthPage = computed(() => {
  const currentComponent = page.component
  return currentComponent === 'Auth/Login' || currentComponent === 'Auth/Register'
})

const isAdminAuthPage = computed(() => {
  const currentComponent = page.component
  return currentComponent === 'Auth/AdminLogin'
})

const showProfileIcon = computed(() => isAuthenticated.value && !isAuthPage.value)

const isStudent = computed(() => user.value?.role === 'student')
const settingsLabel = computed(() => (
  isStudent.value ? t('student.profile.account.title') : t('buttons.settings')
))

const { isMobileMenuOpen, toggle, close } = useMobileMenu()

const handleNavigationClick = (item) => {
  emit('navigationClick', item)
}

const mobileMenuItems = computed(() => (
  props.navigationButtons.length > 0 ? props.navigationButtons : props.menuItems
))

const hasSettingsInMenu = computed(() => (
  mobileMenuItems.value.some(item => item.href === ROUTES.SETTINGS)
))

const roleProfileRoutes = [ROUTES.COMPANY_PROFILE, ROUTES.STUDENT_PROFILE, ROUTES.UNIVERSITY_PROFILE]
const hasProfileInMenu = computed(() => (
  mobileMenuItems.value.some(item => roleProfileRoutes.includes(item.href))
))
</script>

<template>
  <nav v-bind="$attrs" class="w-full h-14 md:h-16 lg:h-20 bg-primary border-b border-border shrink-0 relative z-30">
    <div class="h-full flex items-center justify-between px-4 sm:px-6">
      <BaseLogo />

      <div class="flex items-center gap-3 sm:gap-4 lg:gap-6">
        <div class="flex items-center gap-3 sm:gap-4">
          <Link
            v-if="!isAuthenticated && !isAdminAuthPage"
            :href="ROUTES.OFFERS"
            class="hidden lg:inline-block rounded-full px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/40"
          >
            {{ t('offers.browseCta') }}
          </Link>
          <template v-if="!isAuthenticated && !isAuthPage && !isAdminAuthPage">
            <Link
              :href="ROUTES.LOGIN"
              class="hidden lg:inline-block rounded-full px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/40"
            >
              {{ t('auth.login.submit') }}
            </Link>
            <Link
              :href="ROUTES.REGISTER_STUDENT"
              class="hidden lg:inline-block rounded-full bg-white px-4 py-2 text-sm font-semibold text-primary transition hover:bg-white/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/40"
            >
              {{ t('auth.register.submit') }}
            </Link>
          </template>
          <button
            v-if="showHamburger"
            type="button"
            class="lg:hidden flex items-center justify-center rounded-full p-1 text-white hover:text-white/80 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-primary"
            :aria-label="t('profiles.navMenu')"
            :aria-expanded="isMobileMenuOpen"
            @click="toggle"
          >
            <IconMenu2 stroke="2" class="w-6 h-6 sm:w-7 sm:h-7" />
          </button>
        </div>

        <div class="hidden lg:flex lg:items-center lg:gap-2">
          <BaseNavigationButtons
            v-if="showNavigationButtons && navigationButtons.length > 0"
            :show-buttons="true"
            :variant="navigationVariant"
            :buttons="navigationButtons"
            @button-click="handleNavigationClick"
          />
        </div>

        <LanguageSwitcher />
        <ProfileIcon v-if="showProfileIcon" />
        <NotificationBell v-if="showProfileIcon" />
      </div>
    </div>
  </nav>

  <transition
    enter-active-class="transition-opacity ease-linear duration-300"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="transition-opacity ease-linear duration-300"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div
      v-if="isMobileMenuOpen && showHamburger"
      class="fixed inset-0 bg-black/60 z-40 lg:hidden"
      @click="close"
    />
  </transition>

  <transition
    enter-active-class="transition ease-in-out duration-300 transform"
    enter-from-class="-translate-x-full"
    enter-to-class="translate-x-0"
    leave-active-class="transition ease-in-out duration-300 transform"
    leave-from-class="translate-x-0"
    leave-to-class="-translate-x-full"
  >
    <aside
      v-if="isMobileMenuOpen && showHamburger"
      class="fixed inset-y-0 left-0 z-50 w-72 bg-white shadow-xl lg:hidden flex flex-col"
    >
      <div class="h-14 md:h-16 flex items-center justify-end px-6 border-b border-white/10 bg-primary shrink-0">
        <button
          class="flex items-center justify-center rounded-full p-1 text-white hover:text-white/80 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-primary"
          :aria-label="t('buttons.close')"
          @click="close"
        >
          <IconX stroke="2.5" class="w-6 h-6" />
        </button>
      </div>

      <div class="p-5 overflow-y-auto h-full bg-white">
        <div v-if="showProfileIcon" class="mb-4 flex items-center gap-3 border-b border-border pb-4">
          <ProfileAvatar
            :photo-url="avatarUrl"
            :first-name="displayName"
            :last-name="user.last_name"
            size-class="h-10 w-10 text-sm"
          />
          <div class="min-w-0">
            <p v-if="fullName" class="truncate text-sm font-semibold text-text">
              {{ fullName }}
            </p>
            <p class="truncate text-xs text-additional">
              {{ emailName }}
            </p>
          </div>
        </div>

        <ul class="flex flex-col gap-2">
          <li v-if="!isAuthenticated && !isAdminAuthPage">
            <Link
              :href="ROUTES.OFFERS"
              class="flex items-center gap-3 rounded-lg p-3 text-base font-semibold text-additional transition-colors hover:bg-gray-50 hover:text-secondary"
              @click="close"
            >
              <IconSearch stroke="2" class="w-6 h-6 shrink-0" />
              {{ t('offers.browseCta') }}
            </Link>
          </li>
          <template v-if="!isAuthenticated && !isAuthPage && !isAdminAuthPage">
            <li>
              <Link
                :href="ROUTES.LOGIN"
                class="flex items-center gap-3 rounded-lg p-3 text-base font-semibold text-additional transition-colors hover:bg-gray-50 hover:text-secondary"
                @click="close"
              >
                <IconUserCircle stroke="2" class="w-6 h-6 shrink-0" />
                {{ t('auth.login.submit') }}
              </Link>
            </li>
            <li>
              <Link
                :href="ROUTES.REGISTER_STUDENT"
                class="flex items-center gap-3 rounded-lg p-3 text-base font-semibold text-primary transition-colors hover:bg-primary/5"
                @click="close"
              >
                <IconUserCircle stroke="2" class="w-6 h-6 shrink-0" />
                {{ t('auth.register.submit') }}
              </Link>
            </li>
          </template>
          <li v-for="item in mobileMenuItems" :key="item.id || item.key || item.href">
            <Link
              :href="item.href || '#'"
              class="flex items-center gap-3 rounded-lg p-3 text-base font-semibold transition-colors"
              :class="item.isActive
                ? 'border border-primary/30 bg-primary/5 text-primary'
                : 'text-additional hover:bg-gray-50 hover:text-secondary'"
              :aria-current="item.isActive ? 'page' : undefined"
              @click="close"
            >
              <component :is="item.icon" v-if="item.icon" stroke="2" class="w-6 h-6 shrink-0" />
              {{ item.label }}
            </Link>
          </li>
        </ul>

        <template v-if="showProfileIcon">
          <hr class="my-4 border-border">
          <ul class="flex flex-col gap-2">
            <li v-if="!hasProfileInMenu">
              <Link
                :href="ROUTES.PROFILE"
                class="flex items-center gap-3 rounded-lg p-3 text-base font-semibold text-additional transition-colors hover:bg-gray-50 hover:text-secondary"
                @click="close"
              >
                <IconUserCircle stroke="2" class="w-6 h-6 shrink-0" />
                {{ t('buttons.myProfile') }}
              </Link>
            </li>
            <li v-if="!hasSettingsInMenu">
              <Link
                :href="ROUTES.SETTINGS"
                class="flex items-center gap-3 rounded-lg p-3 text-base font-semibold text-additional transition-colors hover:bg-gray-50 hover:text-secondary"
                @click="close"
              >
                <IconSettings stroke="2" class="w-6 h-6 shrink-0" />
                {{ settingsLabel }}
              </Link>
            </li>
            <li>
              <Link
                :href="ROUTES.LOGOUT"
                method="post"
                as="button"
                class="flex w-full items-center gap-3 rounded-lg p-3 text-left text-base font-semibold text-error transition-colors hover:bg-red-50"
                @click="close"
              >
                <IconLogout stroke="2" class="w-6 h-6 shrink-0" />
                {{ t('buttons.logout') }}
              </Link>
            </li>
          </ul>
        </template>
      </div>
    </aside>
  </transition>
</template>
