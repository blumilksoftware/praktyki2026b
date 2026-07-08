<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { Link } from '@inertiajs/vue3'
import { IconMenu2, IconX } from '@tabler/icons-vue'

const { t } = useI18n()
const isMobileMenuOpen = ref(false)

defineProps({
  items: { type: Array, default: () => [] },
})
</script>

<template>
  <aside class="bg-white border border-secondary rounded-2xl lg:rounded-3xl w-full overflow-hidden">
    <div 
      class="flex items-center justify-between p-5 lg:hidden cursor-pointer"
      @click="isMobileMenuOpen = !isMobileMenuOpen"
    >
      <span class="font-bold text-secondary text-sm uppercase tracking-wider">
        {{ t('profiles.navMenu') }}
      </span>
      <button class="text-secondary hover:text-primary transition-colors focus:outline-none">
        <IconMenu2 v-if="!isMobileMenuOpen" stroke="2" class="w-6 h-6" />
        <IconX v-else stroke="2" class="w-6 h-6" />
      </button>
    </div>

    <div :class="[
      isMobileMenuOpen ? 'block' : 'hidden', 
      'lg:block px-5 pb-5 lg:p-6 border-t border-border lg:border-none'
    ]"
    >
      <ul class="flex flex-col gap-2">
        <span class="font-bold text-secondary text-sm uppercase tracking-wider hidden lg:block mb-2">
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
</template>
