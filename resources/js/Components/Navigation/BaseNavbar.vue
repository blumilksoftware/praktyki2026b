<script setup lang="ts">
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import BaseLogo from '@/Components/Navigation/BaseLogo.vue'
import LanguageSwitcher from '@/Components/Navigation/LanguageSwitcher.vue'
import ProfileIcon from '@/Components/Navigation/ProfileIcon.vue'

interface InertiaSharedProps {
  auth?: {
    user?: Record<string, any>
  }
  [key: string]: any
}

const page = usePage()

const isAuthenticated = computed(() => {
  const props = page.props as InertiaSharedProps
  return !!props.auth?.user
})

const isAuthPage = computed(() => {
  const currentComponent = page.component
  return currentComponent === 'Auth/Login' || currentComponent === 'Auth/Register'
})

const showProfileIcon = computed(() => isAuthenticated.value && !isAuthPage.value)
</script>

<template>
  <nav class="w-full h-14 md:h-16 lg:h-20 bg-primary border-b border-border shrink-0">
    <div class="h-full flex items-center justify-between px-4 sm:px-6">
      <BaseLogo />
      <div class="flex items-center gap-4">
        <LanguageSwitcher />
        <ProfileIcon v-if="showProfileIcon"/>
        <ProfileIcon />
      </div>
    </div>
  </nav>
</template>