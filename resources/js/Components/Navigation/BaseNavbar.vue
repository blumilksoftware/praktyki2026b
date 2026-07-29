<script setup>
import { computed } from 'vue'
import { usePage, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import BaseLogo from '@/Components/Navigation/BaseLogo.vue'
import LanguageSwitcher from '@/Components/Navigation/LanguageSwitcher.vue'
import NotificationBell from '@/Components/Navigation/NotificationBell.vue'
import ProfileIcon from '@/Components/Navigation/ProfileIcon.vue'
import { IconMenu2, IconX } from '@tabler/icons-vue'
import { useMobileMenu } from '@/Composables/useMobileMenu'

const { t } = useI18n()
const page = usePage()

const props = defineProps({
  showHamburger: {
    type: Boolean,
    default: false,
  },
  menuItems: {
    type: Array,
    default: () => [],
  },
})

const user = computed(() => page.props.auth?.user)
const isAuthenticated = computed(() => !!user.value)
const isAuthPage = computed(() => {
  const currentComponent = page.component
  return currentComponent === 'Auth/Login' || currentComponent === 'Auth/Register'
})

const showProfileIcon = computed(() => isAuthenticated.value && !isAuthPage.value)

const { isMobileMenuOpen, toggle, close } = useMobileMenu()
</script>

<template>
  <nav class="w-full h-14 md:h-16 lg:h-20 bg-primary border-b border-border shrink-0 relative z-30">
    <div class="h-full flex items-center justify-between px-4 sm:px-6">
      <BaseLogo />

      <div class="flex items-center gap-3 sm:gap-4">
        <button
          v-if="showHamburger"
          type="button"
          class="lg:hidden flex items-center justify-center text-white hover:text-white/80 transition-colors focus:outline-none"
          :aria-label="t('profiles.navMenu')"
          :aria-expanded="isMobileMenuOpen"
          @click="toggle"
        >
          <IconMenu2 stroke="2" class="w-6 h-6 sm:w-7 sm:h-7" />
        </button>
        <LanguageSwitcher />
        <NotificationBell v-if="showProfileIcon" />
        <ProfileIcon v-if="showProfileIcon" />
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
      <div class="h-14 md:h-16 flex items-center justify-between px-6 border-b border-white/10 bg-primary shrink-0">
        <span class="font-bold text-white text-sm uppercase tracking-wider">
          {{ t('profiles.navMenu') }}
        </span>
        <button 
          class="text-white hover:text-white/80 transition-colors focus:outline-none flex items-center justify-center p-1"
          @click="close"
        >
          <IconX stroke="2.5" class="w-6 h-6" />
        </button>
      </div>

      <div class="p-5 overflow-y-auto h-full bg-white">
        <ul class="flex flex-col gap-2">
          <li v-for="item in menuItems" :key="item.href">
            <Link 
              :href="item.href" 
              class="flex items-center gap-3 rounded-lg p-3 text-base font-semibold transition-colors"
              :class="item.isActive 
                ? 'border border-primary/30 bg-primary/5 text-primary' 
                : 'text-additional hover:bg-gray-50 hover:text-secondary'"
              :aria-current="item.isActive ? 'page' : undefined"
              @click="close"
            >
              <component :is="item.icon" stroke="2" class="w-6 h-6 shrink-0" />
              {{ item.label }}
            </Link>
          </li>
        </ul>
      </div>
    </aside>
  </transition>
</template>