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

const previousLink = computed(() => links.value[0])
const nextLink = computed(() => links.value[links.value.length - 1])

const numericLinks = computed(() => {
  return links.value.slice(1, -1).filter(link => /^\d+$/.test(link.label))
})

function buildItems(delta) {
  const current = props.meta.current_page
  const last = props.meta.last_page

  const pages = new Set([1, last])
  for (let page = current - delta; page <= current + delta; page++) {
    if (page >= 1 && page <= last) pages.add(page)
  }

  const sortedPages = [...pages].sort((a, b) => a - b)
  const pageItems = []
  let previousPage = null

  for (const page of sortedPages) {
    if (previousPage !== null && page - previousPage > 1) {
      pageItems.push({ type: 'ellipsis', key: `ellipsis-${page}` })
    }
    const link = numericLinks.value.find(item => Number(item.label) === page)
    if (link) pageItems.push({ type: 'page', key: `page-${page}`, ...link })
    previousPage = page
  }

  return [
    { type: 'page', key: 'previous', ...previousLink.value },
    ...pageItems,
    { type: 'page', key: 'next', ...nextLink.value },
  ]
}

const mobileItems = computed(() => buildItems(1))
const desktopItems = computed(() => buildItems(2))

function navigate(url) {
  if (!url) return
  router.visit(url, {
    preserveState: true,
    preserveScroll: true,
  })
}

function pageButtonClass(link) {
  return [
    'h-10 min-w-10 px-2 flex items-center justify-center rounded-lg text-sm font-medium transition whitespace-nowrap',
    link.active
      ? 'bg-primary text-white'
      : 'bg-white/40 text-text hover:bg-white/60',
    !link.url ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer',
  ]
}
</script>

<template>
  <div v-if="meta && meta.last_page > 1">
    <div class="sm:hidden flex flex-wrap justify-center items-center gap-2 mt-6">
      <template v-for="item in mobileItems" :key="item.key">
        <span
          v-if="item.type === 'ellipsis'"
          class="h-10 min-w-10 px-2 flex items-center justify-center text-sm text-additional"
        >
          …
        </span>
        <button
          v-else
          :disabled="!item.url"
          :class="pageButtonClass(item)"
          @click="navigate(item.url)"
        >
          {{ decodeLabel(item.label) }}
        </button>
      </template>
    </div>

    <div class="hidden sm:flex flex-wrap justify-center items-center gap-2 mt-6">
      <template v-for="item in desktopItems" :key="item.key">
        <span
          v-if="item.type === 'ellipsis'"
          class="h-10 min-w-10 px-2 flex items-center justify-center text-sm text-additional"
        >
          …
        </span>
        <button
          v-else
          :disabled="!item.url"
          :class="pageButtonClass(item)"
          @click="navigate(item.url)"
        >
          {{ decodeLabel(item.label) }}
        </button>
      </template>
    </div>
  </div>
</template>
