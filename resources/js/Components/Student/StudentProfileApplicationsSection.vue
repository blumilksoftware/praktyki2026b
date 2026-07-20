<script setup>
import { useI18n } from 'vue-i18n'
import ProfileSectionCard from '@/Components/Profile/ProfileSectionCard.vue'
import ProfileTag from '@/Components/Profile/ProfileTag.vue'

defineProps({
  applications: { type: Array, default: () => [] },
})
const { t } = useI18n()
</script>

<template>
  <ProfileSectionCard :title="t('student.profile.applications.title')">
    <p v-if="!applications.length" class="text-additional text-sm">
      {{ t('student.profile.placeholder.comingSoon') }}
    </p>
    <div v-else class="flex flex-col gap-4">
      <article
        v-for="application in applications"
        :key="application.id"
        class="flex flex-col gap-4 rounded-xl border border-slate-200 bg-white p-4 sm:flex-row sm:items-center sm:justify-between"
      >
        <div class="flex min-w-0 gap-4">
          <div class="h-12 w-12 shrink-0 rounded-md border border-slate-300 bg-slate-100" aria-hidden="true" />
          <div class="min-w-0">
            <div class="flex items-center gap-1.5 text-additional text-xs">
              <span>{{ application.company }}</span>
              <span
                v-if="application.company_verified"
                class="text-success"
                aria-hidden="true"
              >
                ✓
              </span>
            </div>
            <h3 class="mt-0.5 font-semibold text-text text-sm">
              {{ application.title }}
            </h3>
            <div class="mt-2 flex flex-wrap gap-2">
              <ProfileTag
                v-for="tag in application.tags"
                :key="`${application.id}-${tag}`"
                :label="tag"
              />
            </div>
          </div>
        </div>

        <button
          type="button"
          class="w-full rounded-md px-4 py-2 text-sm font-semibold transition sm:w-auto"
          :class="application.action_disabled
            ? 'cursor-not-allowed border border-slate-300 bg-slate-100 text-text'
            : 'bg-link text-white hover:bg-link/90'"
          :disabled="application.action_disabled"
        >
          {{ application.action_label }}
        </button>
      </article>
    </div>
  </ProfileSectionCard>
</template>
