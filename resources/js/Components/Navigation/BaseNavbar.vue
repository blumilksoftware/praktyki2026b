<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import BaseLogo from '@/Components/Navigation/BaseLogo.vue'
import LanguageSwitcher from '@/Components/Navigation/LanguageSwitcher.vue'
import ProfileIcon from '@/Components/Navigation/ProfileIcon.vue'

const page = usePage()

const user = computed(() => page.props.auth?.user)

const isAuthenticated = computed(() => !!user.value)

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
        <ProfileIcon v-if="showProfileIcon" />
      </div>
    </div>
  </nav>
</template>
