<script setup>
import { computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { IconArrowLeft, IconDownload, IconMapPin, IconSchool } from '@tabler/icons-vue'
import BaseLayout from '@/Components/Layouts/BaseLayout.vue'
import ProfileAvatar from '@/Components/Student/ProfileAvatar.vue'
import ProfilePageCard from '@/Components/Profile/ProfilePageCard.vue'
import ProfileSectionCard from '@/Components/Profile/ProfileSectionCard.vue'
import ProfileTag from '@/Components/Profile/ProfileTag.vue'
import { useCompanyPanelMenu } from '@/Composables/useCompanyPanelMenu'

const props = defineProps({
  student: { type: Object, required: true },
  cvUrl: { type: String, default: null },
})

const { t } = useI18n()
const companyMenu = useCompanyPanelMenu('applications')

const ageLabel = computed(() => (props.student.age ? t('student.profile.sidebar.age', { count: props.student.age }) : null))
const hasPreferences = computed(() => props.student.preferred_study_fields.length > 0 || props.student.preferred_cities.length > 0)

const goBack = () => {
  window.history.back()
}
</script>

<template>
  <Head :title="student.full_name" />

  <BaseLayout
    active-page="applications"
    :nav-items="companyMenu"
    :navigation-buttons="companyMenu"
    navigation-variant="default"
  >
    <div class="mb-6">
      <button
        type="button"
        class="inline-flex items-center gap-2 rounded text-slate-500 text-sm transition hover:text-slate-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
        @click="goBack"
      >
        <IconArrowLeft stroke="2.5" class="w-4 h-4" />
        {{ t('buttons.back') }}
      </button>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
      <div class="flex flex-col gap-6 self-start">
        <ProfilePageCard centered>
          <ProfileAvatar
            :first-name="student.first_name"
            :last-name="student.last_name"
            size-class="w-28 h-28 text-3xl"
          />
          <h1 class="mt-4 font-semibold text-text text-xl">
            {{ student.full_name }}
          </h1>
          <p v-if="ageLabel" class="mt-1 text-additional text-sm">
            {{ ageLabel }}
          </p>
          <a :href="`mailto:${student.email}`" class="mt-1 break-all text-primary text-sm hover:underline">
            {{ student.email }}
          </a>

          <p v-if="student.university" class="mt-3 font-medium text-text text-sm">
            {{ student.university }}
          </p>

          <div v-if="student.study_field || student.study_year" class="mt-3 flex flex-wrap justify-center gap-2">
            <ProfileTag v-if="student.study_field" :label="student.study_field" variant="profile" />
            <ProfileTag v-if="student.study_year" :label="t('student.profile.sidebar.yearTag', { year: student.study_year })" variant="profile" />
          </div>
        </ProfilePageCard>

        <ProfilePageCard compact>
          <ul v-if="student.city || student.specialization" class="space-y-3 text-base">
            <li v-if="student.city" class="flex items-start gap-2 text-text">
              <IconMapPin class="mt-0.5 h-5 w-5 shrink-0 text-additional" aria-hidden="true" />
              <span>{{ student.city }}</span>
            </li>
            <li v-if="student.specialization" class="flex items-start gap-2 text-text">
              <IconSchool class="mt-0.5 h-5 w-5 shrink-0 text-additional" aria-hidden="true" />
              <span>{{ student.specialization }}</span>
            </li>
          </ul>

          <a
            v-if="cvUrl"
            :href="cvUrl"
            target="_blank"
            class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-primary px-4 py-3 text-sm font-semibold text-white transition hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
          >
            <IconDownload class="h-4 w-4" stroke="2.5" aria-hidden="true" />
            {{ t('profiles.company.applications.actions.download_cv') }}
          </a>
          <p v-else class="mt-4 text-additional text-sm">
            {{ t('profiles.company.applications.no_cv') }}
          </p>
        </ProfilePageCard>
      </div>

      <div class="flex flex-col gap-6 lg:col-span-2">
        <ProfileSectionCard :title="t('student.profile.skills.title')">
          <div v-if="student.skills.length" class="flex flex-wrap gap-2">
            <ProfileTag v-for="skill in student.skills" :key="skill" :label="skill" />
          </div>
          <p v-else class="text-additional text-sm">
            {{ t('student.publicProfile.noSkills') }}
          </p>
        </ProfileSectionCard>

        <ProfileSectionCard :title="t('student.profile.workMode.title')">
          <div v-if="student.work_modes.length" class="flex flex-wrap gap-2">
            <ProfileTag v-for="mode in student.work_modes" :key="mode" :label="t(`student.workModes.${mode}`)" />
          </div>
          <p v-else class="text-additional text-sm">
            {{ t('student.publicProfile.noWorkModes') }}
          </p>
        </ProfileSectionCard>

        <ProfileSectionCard :title="t('student.profile.edit.searchPreferences')">
          <div v-if="hasPreferences" class="flex flex-col gap-4">
            <div v-if="student.preferred_study_fields.length">
              <h3 class="mb-2 font-medium text-additional text-sm">
                {{ t('student.profile.details.fields') }}
              </h3>
              <div class="flex flex-wrap gap-2">
                <ProfileTag v-for="field in student.preferred_study_fields" :key="field" :label="field" />
              </div>
            </div>
            <div v-if="student.preferred_cities.length">
              <h3 class="mb-2 font-medium text-additional text-sm">
                {{ t('student.profile.details.cities') }}
              </h3>
              <div class="flex flex-wrap gap-2">
                <ProfileTag v-for="city in student.preferred_cities" :key="city" :label="city" />
              </div>
            </div>
          </div>
          <p v-else class="text-additional text-sm">
            {{ t('student.publicProfile.noPreferences') }}
          </p>
        </ProfileSectionCard>
      </div>
    </div>
  </BaseLayout>
</template>
