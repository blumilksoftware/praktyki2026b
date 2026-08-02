<script setup>
import { computed } from 'vue'

const props = defineProps({
  currentPage: { type: Number, required: true },
  totalPages: { type: Number, required: true },
})

const emit = defineEmits(['update:currentPage'])

const pages = computed(() => Array.from({ length: props.totalPages }, (_, index) => index + 1))

function goTo(page) {
  if (page < 1 || page > props.totalPages || page === props.currentPage) return
  emit('update:currentPage', page)
}
</script>

<template>
  <div v-if="totalPages > 1" class="flex flex-wrap justify-center items-center gap-2 mt-6">
    <button
      :disabled="currentPage === 1"
      :class="[
        'h-10 min-w-10 px-2 flex items-center justify-center rounded-lg text-sm font-medium transition whitespace-nowrap',
        currentPage === 1 ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer bg-white/40 text-slate-700 hover:bg-white/60',
      ]"
      @click="goTo(currentPage - 1)"
    >
      «
    </button>
    <button
      v-for="page in pages"
      :key="page"
      :class="[
        'h-10 min-w-10 px-2 flex items-center justify-center rounded-lg text-sm font-medium transition whitespace-nowrap cursor-pointer',
        page === currentPage ? 'bg-primary text-white' : 'bg-white/40 text-slate-700 hover:bg-white/60',
      ]"
      @click="goTo(page)"
    >
      {{ page }}
    </button>
    <button
      :disabled="currentPage === totalPages"
      :class="[
        'h-10 min-w-10 px-2 flex items-center justify-center rounded-lg text-sm font-medium transition whitespace-nowrap',
        currentPage === totalPages ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer bg-white/40 text-slate-700 hover:bg-white/60',
      ]"
      @click="goTo(currentPage + 1)"
    >
      »
    </button>
  </div>
</template>
