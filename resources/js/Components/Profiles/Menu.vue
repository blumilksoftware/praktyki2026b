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
          class="flex items-center gap-2 text-sm font-semibold transition-all px-4 py-2 rounded-xl"
          :class="item.isActive 
            ? 'bg-white border border-secondary/20 text-secondary shadow-sm' 
            : 'text-slate-500 hover:bg-white hover:text-secondary border border-transparent'"
        >
          <component :is="item.icon" stroke="2" class="w-4 h-4 shrink-0" />
          {{ item.label }}
        </Link>
      </li>
    </ul>
  </div>
</template>
