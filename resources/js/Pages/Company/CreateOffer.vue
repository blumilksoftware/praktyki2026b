<script setup>
import { Head } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { 
  IconHome, 
  IconUser,
  IconBriefcase,
  IconFileText,
  IconSettings,
} from '@tabler/icons-vue'
import BaseLayout from '@/Components/Layouts/BaseLayout.vue'
import OfferForm from '@/Components/Offer/OfferForm.vue'

defineProps({
  studyFields: { type: Array, required: true },
  universities: { type: Array, required: true },
})

const { t } = useI18n()
const page = usePage()

const navigationButtons = [
  { 
    id: 'dashboard',
    key: 'dashboard', 
    label: 'Dashboard', 
    href: '/company/dashboard', 
    icon: IconHome, 
  },
  { 
    id: 'profile',
    key: 'profile', 
    label: 'Profil', 
    href: '/company/profile', 
    icon: IconUser, 
  },
  { 
    id: 'offers',
    key: 'offers', 
    label: 'Moje oferty', 
    href: '/company/offers', 
    icon: IconBriefcase, 
  },
  { 
    id: 'documents',
    key: 'documents', 
    label: 'Dokumenty', 
    href: '/company/documents', 
    icon: IconFileText, 
  },
  { 
    id: 'settings',
    key: 'settings', 
    label: 'Ustawienia', 
    href: '/company/settings', 
    icon: IconSettings,
    variant: 'outline',
  },
]

const currentPath = computed(() => page.url)

const processedNavButtons = computed(() => {
  return navigationButtons.map(button => ({
    ...button,
    isActive: currentPath.value === button.href || 
              (button.href && currentPath.value.startsWith(button.href + '/')),
  }))
})

const navItems = computed(() => {
  return processedNavButtons.value.map(button => ({
    key: button.key,
    label: button.label,
    href: button.href,
    icon: button.icon,
    isActive: button.isActive,
  }))
})
</script>

<template>
  <Head :title="t('company.offers.create.title')" />
  
  <BaseLayout 
    active-page="offers" 
    :nav-items="navItems"
    :navigation-buttons="processedNavButtons"
    navigation-variant="default"
    @navigation-click="handleNavigationClick"
  >
    <div class="mx-auto px-4 py-8 w-full max-w-6xl">
      <div class="bg-white shadow-sm mx-auto border border-border rounded-3xl w-full overflow-visible">
        <div class="px-6 sm:px-8 py-6 border-border border-b">
          <h1 class="font-semibold text-text text-2xl">
            {{ t('company.offers.create.heading') }}
          </h1>
        </div>

        <div class="px-4 sm:px-8 py-8">
          <OfferForm :study-fields="studyFields" :universities="universities" />
        </div>
      </div>
    </div>
  </BaseLayout>
</template>
