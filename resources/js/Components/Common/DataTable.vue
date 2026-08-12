<script setup>
import { useI18n } from 'vue-i18n'
import { IconSelector, IconChevronUp, IconChevronDown } from '@tabler/icons-vue'

const props = defineProps({
  items: { type: Array, default: () => [] },
  columns: { type: Array, default: () => [] },
  rowKey: { type: String, default: '' },
  caption: { type: String, default: '' },
  sortKey: { type: String, default: '' },
  sortDir: { type: String, default: 'asc' },
})

const emit = defineEmits(['sort'])

const { t } = useI18n()

function handleSort(col) {
  if (!col.sortable) return
  const newDir = props.sortKey === col.key && props.sortDir === 'asc' ? 'desc' : 'asc'
  emit('sort', { key: col.key, dir: newDir })
}

function sortIcon(col) {
  if (props.sortKey !== col.key) return IconSelector
  return props.sortDir === 'asc' ? IconChevronUp : IconChevronDown
}
</script>

<template>
  <div class="space-y-3">
    <div v-if="props.items.length === 0" class="py-8 text-slate-500 text-center">
      {{ t('table.noData') }}
    </div>
    <div v-else class="xl:hidden gap-4 grid grid-cols-1 md:grid-cols-2">
      <article
        v-for="item in props.items"
        :key="`mobile-${item[props.rowKey]}`"
        class="bg-white p-4 rounded-xl border border-border"
      >
        <div class="flex justify-between items-center gap-3">
          <p class="font-semibold text-text text-sm">{{ item.name || item[props.rowKey] }}</p>
          <span v-if="item.status" class="inline-flex px-2.5 py-1 rounded-full font-medium text-xs">
            <slot :name="`cell-status`" :item="item">{{ item.status }}</slot>
          </span>
        </div>
        <dl class="space-y-2 mt-3 text-sm">
          <div
            v-for="col in props.columns"
            :key="col.key"
            class="flex sm:flex-row flex-col sm:justify-between gap-1 sm:gap-2"
          >
            <dt class="text-slate-500 shrink-0">{{ col.label }}</dt>
            <dd class="overflow-hidden text-slate-800 sm:text-right break-words">
              <slot :name="`cell-${col.key}`" :item="item">{{ item[col.key] }}</slot>
            </dd>
          </div>
        </dl>
      </article>
    </div>

    <div v-if="props.items.length > 0" class="hidden xl:block overflow-x-auto">
      <table class="min-w-full text-sm">
        <caption class="sr-only">{{ props.caption || 'Data table' }}</caption>
        <thead class="bg-gray-50 font-semibold text-slate-600 text-xs uppercase tracking-wide">
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
                  :class="sortKey === col.key ? 'text-primary' : 'text-slate-400'"
                  aria-hidden="true"
                />
              </span>
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-border">
          <tr v-for="item in props.items" :key="item[props.rowKey]" class="hover:bg-gray-50">
            <td
              v-for="col in props.columns"
              :key="col.key"
              :class="['px-4 py-3', col.align === 'right' ? 'text-right' : 'text-left']"
            >
              <span v-if="col.key === 'status'" class="inline-flex px-2.5 py-1 rounded-full font-medium text-xs">
                <slot :name="`cell-${col.key}`" :item="item">{{ item[col.key] }}</slot>
              </span>
              <template v-else>
                <slot :name="`cell-${col.key}`" :item="item">{{ item[col.key] }}</slot>
              </template>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
