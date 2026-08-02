<script setup>
import { useI18n } from 'vue-i18n'
import { IconDownload } from '@tabler/icons-vue'

const props = defineProps({
  application: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['update-status'])

const { t } = useI18n()

const getInitials = (name) => {
  if (!name) return ''
  return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2)
}

const formatDate = (isoString) => {
  if (!isoString) return ''
  
  const date = new Date(isoString)
  const today = new Date()
  
  const isToday = date.getDate() === today.getDate() && 
                  date.getMonth() === today.getMonth() && 
                  date.getFullYear() === today.getFullYear()

  if (isToday) {
    const time = date.toLocaleTimeString('pl-PL', { hour: '2-digit', minute: '2-digit' })
    return t('profiles.company.applications.date.today', { time })
  }
  
  const dateString = date.toLocaleDateString('pl-PL', { day: '2-digit', month: '2-digit', year: 'numeric' })
  return t('profiles.company.applications.date.exact', { date: dateString })
}

const onStatusChange = (event) => {
  emit('update-status', props.application.id, event.target.value)
}
</script>

<template>
  <div class="flex flex-col lg:flex-row lg:items-center justify-between p-4 lg:p-5 bg-white rounded-xl border border-[#1e3a8a]/40 shadow-sm gap-6">
    <div class="flex items-center gap-4 lg:w-1/3">
      <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-700 font-bold text-lg shrink-0">
        {{ getInitials(application.student_name) }}
      </div>
      <div class="flex flex-col">
        <span class="font-bold text-[#0f172a] text-lg leading-tight">{{ application.student_name }}</span>
        <span class="text-sm text-slate-500 font-medium">
          {{ application.university?.name || application.university || t('profiles.company.applications.no_university') }}
        </span>
        <span class="text-xs text-slate-400 mt-0.5">{{ formatDate(application.application_date) }}</span>
      </div>
    </div>

    <div class="flex flex-col lg:w-1/3">
      <span class="text-xs text-slate-400 font-medium uppercase tracking-wide">
        {{ t('profiles.company.applications.applied_for') }}
      </span>
      <span class="font-bold text-[#0f172a]">{{ application.offer_title }}</span>
    </div>

    <div class="flex items-center gap-4 lg:w-1/3 lg:justify-end">
      <select
        :aria-label="t('profiles.company.applications.filters.status')"
        :value="application.status"
        :disabled="['accepted', 'rejected'].includes(application.status)"
        class="rounded-full py-1.5 pl-4 pr-8 text-sm font-semibold shadow-sm focus:ring-0 appearance-none disabled:cursor-not-allowed disabled:opacity-75 transition-all"
        :class="{
          'bg-blue-50 text-blue-700 border-blue-200 cursor-pointer hover:bg-blue-100': application.status === 'pending',
          'bg-yellow-50 text-yellow-700 border-yellow-300 cursor-pointer hover:bg-yellow-100': application.status === 'reviewed',
          'bg-green-50 text-green-700 border-green-300': application.status === 'accepted',
          'bg-red-50 text-red-700 border-red-200': application.status === 'rejected'
        }"
        @change="onStatusChange"
      >
        <option value="pending" :disabled="application.status !== 'pending'">
          {{ t('profiles.company.applications.statuses.pending') }}
        </option>
        <option value="reviewed">
          {{ t('profiles.company.applications.statuses.reviewed') }}
        </option>
        <option value="accepted">
          {{ t('profiles.company.applications.statuses.accepted') }}
        </option>
        <option value="rejected">
          {{ t('profiles.company.applications.statuses.rejected') }}
        </option>
      </select>

      <a
        v-if="application.cv_url"
        :href="application.cv_url"
        target="_blank"
        class="inline-flex items-center gap-2 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-lg px-4 py-2 font-semibold text-[#0f172a] text-sm transition-colors"
      >
        <IconDownload class="h-4 w-4 text-[#1e3a8a]" stroke="2.5" />
        {{ t('profiles.company.applications.actions.download_cv') }}
      </a>
      <span v-else class="italic text-slate-400 text-sm px-4 py-2">
        {{ t('profiles.company.applications.no_cv') }}
      </span>
    </div>
  </div>
</template>
