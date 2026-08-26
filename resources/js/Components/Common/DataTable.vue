<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { IconSelector, IconChevronUp, IconChevronDown } from '@tabler/icons-vue'

const props = defineProps({
  items: { type: Array, default: () => [] },
  columns: { type: Array, default: () => [] },
  rowKey: { type: String, default: '' },
  caption: { type: String, default: '' },
  sortKey: { type: String, default: '' },
  sortDir: { type: String, default: 'asc' },
  rowHref: { type: Function, default: null },
  cardTitleKey: { type: String, default: 'name' },
  cardBadgeKey: { type: String, default: 'status' },
})

const emit = defineEmits(['sort'])

const { t } = useI18n()

const cardColumns = computed(() =>
  props.columns.filter(col => col.key !== props.cardTitleKey && col.key !== props.cardBadgeKey),
)

function handleSort(col) {
  if (!col.sortable) return
  const newDir = props.sortKey === col.key && props.sortDir === 'asc' ? 'desc' : 'asc'
  emit('sort', { key: col.key, dir: newDir })
}

function handleRowClick(event, item) {
  if (!props.rowHref) return
  if (event.target.closest('button, a, input, select')) return

  const href = props.rowHref(item)

  if (href) window.open(href, '_blank')
}

function sortIcon(col) {
  if (props.sortKey !== col.key) return IconSelector
  return props.sortDir === 'asc' ? IconChevronUp : IconChevronDown
}
</script>

<template>
  <div class="space-y-3">
    <div v-if="props.items.length === 0" class="py-8 text-additional text-center">
      {{ t('table.noData') }}
    </div>
    <div v-else class="xl:hidden gap-4 grid grid-cols-1 md:grid-cols-2">
      <article
        v-for="item in props.items"
        :key="`mobile-${item[props.rowKey]}`"
        class="bg-white p-4 rounded-xl border border-border"
        :class="{ 'cursor-pointer': rowHref }"
        @click="handleRowClick($event, item)"
      >
        <div class="flex justify-between items-center gap-3">
          <p class="font-semibold text-text text-sm">
            <slot :name="`cell-${props.cardTitleKey}`" :item="item">{{ item[props.cardTitleKey] }}</slot>
          </p>
          <template v-if="item[props.cardBadgeKey]">
            <slot :name="`cell-${props.cardBadgeKey}`" :item="item">{{ item[props.cardBadgeKey] }}</slot>
          </template>
        </div>
        <dl class="space-y-2 mt-3 text-sm">
          <div
            v-for="col in cardColumns"
            :key="col.key"
            class="flex justify-between items-center gap-2"
          >
            <dt class="text-additional shrink-0">{{ col.label }}</dt>
            <dd
              class="min-w-0 text-text text-right break-words"
              :class="col.key === 'actions' ? '' : 'overflow-hidden'"
            >
              <slot :name="`cell-${col.key}`" :item="item">{{ item[col.key] }}</slot>
            </dd>
          </div>
        </dl>
      </article>
    </div>

    <div v-if="props.items.length > 0" class="hidden xl:block overflow-x-auto">
      <table class="min-w-full text-sm">
        <caption class="sr-only">{{ props.caption || 'Data table' }}</caption>
        <thead class="bg-gray-50 font-semibold text-additional text-xs uppercase tracking-wide">
          <tr>
            <th
              v-for="col in props.columns"
              :key="col.key"
              :class="[
                'px-4 py-3',
                col.align === 'right' ? 'text-right' : 'text-left',
                col.sortable ? 'cursor-pointer select-none hover:bg-gray-100 transition-colors' : '',
              ]"
              :aria-sort="col.sortable && sortKey === col.key
                ? (sortDir === 'asc' ? 'ascending' : 'descending')
                : (col.sortable ? 'none' : undefined)"
              @click="handleSort(col)"
            >
              <span v-if="col.srLabel" class="sr-only">{{ col.srLabel }}</span>
              <span class="inline-flex items-center gap-1">
                {{ col.label }}
                <component
                  :is="sortIcon(col)"
                  v-if="col.sortable"
                  class="w-3.5 h-3.5"
                  :class="sortKey === col.key ? 'text-primary' : 'text-additional'"
                  aria-hidden="true"
                />
              </span>
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-border">
          <tr
            v-for="item in props.items"
            :key="item[props.rowKey]"
            class="hover:bg-gray-50"
            :class="{ 'cursor-pointer': rowHref }"
            @click="handleRowClick($event, item)"
          >
            <td
              v-for="col in props.columns"
              :key="col.key"
              :class="['px-4 py-3', col.align === 'right' ? 'text-right' : 'text-left']"
            >
              <slot :name="`cell-${col.key}`" :item="item">{{ item[col.key] }}</slot>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
