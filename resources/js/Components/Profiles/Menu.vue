<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  items: { type: Array, default: () => [] },
})
</script>

<template>
  <div class="hidden lg:block w-full overflow-x-auto no-scrollbar pb-1">
    <ul class="flex flex-row gap-2">
      <li v-for="item in items" :key="item.href" class="shrink-0">
        <Link 
          :href="item.href" 
          class="flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold transition-all"
          :class="item.isActive 
            ? 'border border-primary/30 bg-white text-primary shadow-sm' 
            : 'border border-transparent text-slate-500 hover:bg-white hover:text-secondary'"
          :aria-current="item.isActive ? 'page' : undefined"
        >
          <component :is="item.icon" stroke="2" class="w-4 h-4 shrink-0" />
          {{ item.label }}
        </Link>
      </li>
    </ul>
  </div>
</template>
