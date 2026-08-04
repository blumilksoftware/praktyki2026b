<script setup>
import { useI18n } from 'vue-i18n'
import { IconSearch, IconX } from '@tabler/icons-vue'

const { t } = useI18n()

const statusOptions = ['published', 'draft', 'closed', 'expired']

const searchQuery = defineModel('search', { type: String, required: true })
const statusFilter = defineModel('status', { type: String, required: true })

const emit = defineEmits(['status-change'])
</script>

<template>
  <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
    <div class="relative">
      <IconSearch class="w-4 h-4 text-additional absolute left-2.5 top-1/2 -translate-y-1/2" />
      <input
        v-model="searchQuery"
        type="text"
        :placeholder="t('company.dashboard.offers.search.placeholder')"
        class="w-full sm:w-56 pl-8 pr-8 py-1.5 text-sm rounded-lg border border-border focus:outline-none focus:ring-2 focus:ring-primary/40"
      >
      <button
        v-if="searchQuery"
        type="button"
        class="absolute right-2 top-1/2 -translate-y-1/2 text-additional hover:text-text cursor-pointer"
        :aria-label="t('company.dashboard.offers.search.clear')"
        @click="searchQuery = ''"
      >
        <IconX class="w-3.5 h-3.5" />
      </button>
    </div>

    <select
      v-model="statusFilter"
      class="w-full sm:w-48 text-sm rounded-lg border border-border py-1.5 px-2 focus:outline-none focus:ring-2 focus:ring-primary/40 cursor-pointer"
      @change="emit('status-change')"
    >
      <option value="">
        {{ t('company.dashboard.offers.filters.allStatuses') }}
      </option>
      <option
        v-for="option in statusOptions"
        :key="option"
        :value="option"
      >
        {{ t(`company.dashboard.offers.filters.${option}`) }}
      </option>
    </select>
  </div>
</template>
