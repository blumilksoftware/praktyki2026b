<script setup>
import { useI18n } from 'vue-i18n'
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { ROUTES } from '@/Helpers/routes'
import { IconUser } from '@tabler/icons-vue'

const { t } = useI18n()
const page = usePage()

const user = computed(() => page.props.auth?.user)
const logoPath = computed(() => {
  return user.value?.company?.logo_path || user.value?.university_organization?.logo_path
})

const isOpen = ref(false)
const dropdownRef = ref(null)

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
  <div ref="dropdownRef" class="relative inline-block text-left">
    <div
      class="flex items-center justify-center w-10 h-10 p-0 rounded-full bg-secondary text-accent overflow-hidden hover:cursor-pointer hover:ring-2 hover:ring-link hover:ring-offset-2 hover:ring-offset-background transition-all focus:outline-none"
      :aria-expanded="isOpen"
      aria-haspopup="true"
      @click="isOpen = !isOpen"
    >
      <img 
        v-if="logoPath" 
        :src="logoPath.startsWith('/') ? logoPath : '/' + logoPath" 
        alt="Profil" 
        class="w-full h-full object-cover"
      >
      
      <IconUser 
        v-else 
        stroke="2" 
        class="w-6 h-6 text-accent" 
      />
    </div>

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
        class="absolute right-0 mt-3 w-48 origin-top-right rounded-xl bg-white shadow-md border border-border focus:outline-none z-50 overflow-hidden"
      >
        <div class="py-1">
          <Link 
            :href="ROUTES.PROFILE" 
            class="block px-4 py-2.5 text-sm font-medium text-text hover:bg-background transition-colors"
          >
            {{ t('buttons.myProfile') }}
          </Link>
          
          <Link 
            :href="ROUTES.SETTINGS" 
            class="block px-4 py-2.5 text-sm font-medium text-text hover:bg-background transition-colors"
          >
            {{ t('buttons.settings') }}
          </Link>
          
          <hr class="my-1 border-border">
          
          <Link 
            :href="ROUTES.LOGOUT" 
            method="post" 
            as="button"
            class="block w-full text-left px-4 py-2.5 text-sm font-medium text-error hover:bg-red-50 hover:text-error-dark transition-colors"
          >
            {{ t('buttons.logout') }}
          </Link>
        </div>
      </div>
    </transition>
  </div>
</template>
