<script setup>
import { useI18n } from 'vue-i18n'
import { router } from '@inertiajs/vue3'

const { t } = useI18n()

const props = defineProps({
  offers: {
    type: Object,
    required: true,
  },
})

function paginationLabel(label) {
  const textarea = document.createElement('textarea')
  textarea.innerHTML = label
  return textarea.value
}

function goToPage(url) {
  if (!url) return
  router.get(url, {}, { preserveState: true, preserveScroll: true, replace: true })
}
</script>

<template>
  <div
    v-if="offers.data.length > 0 && offers.last_page > 1"
    class="px-4 py-3 border-t border-border flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2"
  >
    <span class="text-xs text-additional">
      {{ t('company.dashboard.offers.pagination.summary', {
        from: offers.from,
        to: offers.to,
        total: offers.total
      }) }}
    </span>

    <div class="flex items-center gap-1 flex-wrap">
      <button
        v-for="(link, index) in offers.links"
        :key="index"
        type="button"
        class="px-2.5 py-1 text-xs rounded-md min-w-[2rem]"
        :class="link.active
          ? 'bg-primary text-white'
          : link.url
            ? 'text-text hover:bg-gray-100 cursor-pointer'
            : 'text-additional cursor-not-allowed'"
        :disabled="!link.url"
        @click="goToPage(link.url)"
      >
        {{ paginationLabel(link.label) }}
      </button>
    </div>
  </div>
</template>
