<script setup>
import { useI18n } from 'vue-i18n'
import ProfileSectionCard from '@/Components/Profile/ProfileSectionCard.vue'
import ProfileTag from '@/Components/Profile/ProfileTag.vue'

defineProps({
  workModes: { type: Array, default: () => [] },
})
const emit = defineEmits(['manage'])
const { t } = useI18n()
</script>

<template>
  <ProfileSectionCard :title="t('student.profile.workMode.title')">
    <template #actions>
      <button
        type="button"
        class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-semibold text-text transition hover:bg-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
        @click="emit('manage')"
      >
        {{ t('student.profile.actions.edit') }}
      </button>
    </template>
    <div v-if="workModes.length" class="flex flex-wrap gap-2">
      <ProfileTag v-for="mode in workModes" :key="mode" :label="mode" />
    </div>
    <p v-else class="text-additional text-sm">
      {{ t('student.profile.placeholder.comingSoon') }}
    </p>
  </ProfileSectionCard>
</template>
