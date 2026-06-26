<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  meta: {
    type: Object,
    default: () => ({}),
  },
})

const decodeLabel = (label) => {
  return label
    .replaceAll('&laquo;', '«')
    .replaceAll('&raquo;', '»')
}

const links = computed(() => {
  if (!props.meta.links) return []
  return props.meta.links
})

function navigate(url) {
  if (!url) return
  router.visit(url, {
    preserveState: true,
    preserveScroll: true,
  })
}
</script>

<template>
  <div v-if="meta && meta.last_page > 1" class="flex flex-wrap justify-center items-center gap-2 mt-6">
    <button
      v-for="(link, index) in links"
      :key="index"
      :disabled="!link.url"
      :class="[
        'h-10 min-w-10 px-2 flex items-center justify-center rounded-lg text-sm font-medium transition whitespace-nowrap',
        link.active
          ? 'bg-primary text-white'
          : 'bg-white/40 text-slate-700 hover:bg-white/60',
        !link.url ? 'opacity-50 cursor-not-allowed' : ''
      ]"
      @click="navigate(link.url)"
    >
      {{ decodeLabel(link.label) }}
    </button>
  </div>
</template>
