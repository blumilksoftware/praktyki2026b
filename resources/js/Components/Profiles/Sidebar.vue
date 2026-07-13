<script setup>
import { useI18n } from 'vue-i18n'
import { Link } from '@inertiajs/vue3'
import { IconX } from '@tabler/icons-vue'
import { useMobileMenu } from '@/Composables/useMobileMenu'

const { t } = useI18n()

const { isMobileMenuOpen, close } = useMobileMenu()

defineProps({
  items: { type: Array, default: () => [] },
})
</script>

<template>
  <aside class="hidden lg:block bg-white border border-secondary rounded-2xl lg:rounded-3xl w-full overflow-hidden">
    <div class="px-5 pb-5 lg:p-6">
      <ul class="flex flex-col gap-2">
        <span class="font-bold text-secondary text-sm uppercase tracking-wider block mb-2">
          {{ t('profiles.navMenu') }}
        </span>

        <li v-for="item in items" :key="item.href">
          <Link 
            :href="item.href" 
            class="flex items-center gap-3 text-sm font-semibold transition-colors p-3 lg:p-2.5 rounded-lg"
            :class="item.isActive 
              ? 'bg-background border border-border text-secondary' 
              : 'text-additional hover:bg-gray-50 hover:text-secondary'"
          >
            <component :is="item.icon" stroke="2" class="w-5 h-5 shrink-0" />
            {{ item.label }}
          </Link>
        </li>
      </ul>
    </div>
  </aside>

  <transition
    enter-active-class="transition-opacity ease-linear duration-300"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="transition-opacity ease-linear duration-300"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div
      v-if="isMobileMenuOpen"
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
      v-if="isMobileMenuOpen"
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
          <li v-for="item in items" :key="item.href">
            <Link 
              :href="item.href" 
              class="flex items-center gap-3 text-base font-semibold transition-colors p-3 rounded-lg"
              :class="item.isActive 
                ? 'bg-background border border-border text-secondary' 
                : 'text-additional hover:bg-gray-50 hover:text-secondary'"
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
