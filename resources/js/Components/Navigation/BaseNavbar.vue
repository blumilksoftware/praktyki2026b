<script setup>
import { computed } from 'vue'
import { usePage, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import BaseLogo from '@/Components/Navigation/BaseLogo.vue'
import LanguageSwitcher from '@/Components/Navigation/LanguageSwitcher.vue'
import ProfileIcon from '@/Components/Navigation/ProfileIcon.vue'
import BaseNavigationButtons from '@/Components/Navigation/BaseNavigationButtons.vue'
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

const user = computed(() => page.props.auth?.user)
const isAuthenticated = computed(() => !!user.value)
const isAuthPage = computed(() => {
  const currentComponent = page.component
  return currentComponent === 'Auth/Login' || currentComponent === 'Auth/Register'
})

const showProfileIcon = computed(() => isAuthenticated.value && !isAuthPage.value)

const { isMobileMenuOpen, toggle, close } = useMobileMenu()

const handleNavigationClick = (item) => {
  emit('navigationClick', item)
}
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

      <div class="flex items-center gap-4">
        <LanguageSwitcher />
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
        <div v-if="showNavigationButtons && navigationButtons.length > 0" class="mb-6">
          <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3 px-3">
            {{ t('navigation') || 'Nawigacja' }}
          </h3>
          <ul class="flex flex-col gap-1">
            <li v-for="item in navigationButtons" :key="item.id || item.key">
              <Link
                :href="item.href || '#'"
                class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-medium transition-colors"
                :class="item.isActive
                  ? 'bg-primary/10 text-primary'
                  : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'"
                :aria-current="item.isActive ? 'page' : undefined"
                @click="close"
              >
                <component
                  :is="item.icon"
                  v-if="item.icon"
                  stroke="2"
                  class="w-5 h-5 shrink-0"
                  :class="item.isActive ? 'text-primary' : 'text-gray-400'"
                />
                {{ item.label }}
              </Link>
            </li>
          </ul>
        </div>

        <div v-if="menuItems.length > 0" class="mb-6">
          <h3 v-if="showNavigationButtons && navigationButtons.length > 0" class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3 px-3">
            {{ t('menu') || 'Menu' }}
          </h3>
          <ul class="flex flex-col gap-1">
            <li v-for="item in menuItems" :key="item.href">
              <Link
                :href="item.href"
                class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-medium transition-colors"
                :class="item.isActive
                  ? 'bg-primary/10 text-primary'
                  : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'"
                :aria-current="item.isActive ? 'page' : undefined"
                @click="close"
              >
                <component :is="item.icon" stroke="2" class="w-5 h-5 shrink-0" :class="item.isActive ? 'text-primary' : 'text-gray-400'" />
                {{ item.label }}
              </Link>
            </li>
          </ul>
        </div>
      </div>
    </aside>
  </transition>
</template>
