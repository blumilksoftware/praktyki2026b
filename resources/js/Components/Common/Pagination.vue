<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { IconChevronLeft, IconChevronRight } from '@tabler/icons-vue'

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

function range(start, end) {
  if (end < start) return []
  const length = end - start + 1
  return Array.from({ length }, (_, index) => start + index)
}

function pageRange(siblingCount) {
  const current = props.meta.current_page
  const last = props.meta.last_page
  const boundaryCount = 1

  const startPages = range(1, Math.min(boundaryCount, last))
  const endPages = range(Math.max(last - boundaryCount + 1, boundaryCount + 1), last)

  const siblingsStart = Math.max(
    Math.min(current - siblingCount, last - boundaryCount - siblingCount * 2 - 1),
    boundaryCount + 2,
  )
  const siblingsEnd = Math.min(
    Math.max(current + siblingCount, boundaryCount + siblingCount * 2 + 2),
    endPages.length > 0 ? endPages[0] - 2 : last - 1,
  )

  const pages = [...startPages]

  if (siblingsStart > boundaryCount + 2) {
    pages.push('ellipsis')
  } else if (boundaryCount + 1 < last - boundaryCount) {
    pages.push(boundaryCount + 1)
  }

  pages.push(...range(siblingsStart, siblingsEnd))

  if (siblingsEnd < last - boundaryCount - 1) {
    pages.push('ellipsis')
  } else if (last - boundaryCount > boundaryCount) {
    pages.push(last - boundaryCount)
  }

  pages.push(...endPages)

  return pages
}

function buildItems(siblingCount) {
  return pageRange(siblingCount).map((page, index) => {
    if (page === 'ellipsis') return { type: 'ellipsis', key: `ellipsis-${index}` }
    const link = numericLinks.value.find(item => Number(item.label) === page)
    return { type: 'page', key: `page-${page}`, ...link }
  })
}

const mobilePages = computed(() => buildItems(0))
const desktopPages = computed(() => buildItems(2))

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
    <div class="sm:hidden flex flex-wrap justify-center items-center gap-1 mt-6">
      <button
        :disabled="!previousLink.url"
        :aria-label="decodeLabel(previousLink.label)"
        :class="pageButtonClass(previousLink)"
        @click="navigate(previousLink.url)"
      >
        <IconChevronLeft class="h-4 w-4" aria-hidden="true" />
      </button>

      <template v-for="item in mobilePages" :key="item.key">
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

      <button
        :disabled="!nextLink.url"
        :aria-label="decodeLabel(nextLink.label)"
        :class="pageButtonClass(nextLink)"
        @click="navigate(nextLink.url)"
      >
        <IconChevronRight class="h-4 w-4" aria-hidden="true" />
      </button>
    </div>

    <div class="hidden sm:flex flex-wrap justify-center items-center gap-2 mt-6">
      <button
        :disabled="!previousLink.url"
        :class="pageButtonClass(previousLink)"
        @click="navigate(previousLink.url)"
      >
        {{ decodeLabel(previousLink.label) }}
      </button>

      <template v-for="item in desktopPages" :key="item.key">
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

      <button
        :disabled="!nextLink.url"
        :class="pageButtonClass(nextLink)"
        @click="navigate(nextLink.url)"
      >
        {{ decodeLabel(nextLink.label) }}
      </button>
    </div>
  </div>
</template>
