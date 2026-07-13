<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import BaseLogo from '@/Components/Navigation/BaseLogo.vue'
import LanguageSwitcher from '@/Components/Navigation/LanguageSwitcher.vue'
import ProfileIcon from '@/Components/Navigation/ProfileIcon.vue'
import { IconMenu2 } from '@tabler/icons-vue'
import { useMobileMenu } from '@/Composables/useMobileMenu'

const page = usePage()

const user = computed(() => page.props.auth?.user)

const isAuthenticated = computed(() => !!user.value)

const isAuthPage = computed(() => {
  const currentComponent = page.component
  return currentComponent === 'Auth/Login' || currentComponent === 'Auth/Register'
})

const showProfileIcon = computed(() => isAuthenticated.value && !isAuthPage.value)

const { toggle } = useMobileMenu()
</script>

<template>
  <nav class="w-full h-14 md:h-16 lg:h-20 bg-primary border-b border-border shrink-0">
    <div class="h-full flex items-center justify-between px-4 sm:px-6">
      <div class="flex items-center gap-3 sm:gap-4">
        <BaseLogo />
        <button
          class="lg:hidden flex items-center justify-center text-white hover:text-white/80 transition-colors focus:outline-none"
          aria-label="Otwórz menu nawigacji"
          @click="toggle"
        >
          <IconMenu2 stroke="2" class="w-6 h-6 sm:w-7 sm:h-7" />
        </button>
      </div>

      <div class="flex items-center gap-4">
        <LanguageSwitcher />
        <ProfileIcon v-if="showProfileIcon" />
      </div>
    </div>
  </nav>
</template>
