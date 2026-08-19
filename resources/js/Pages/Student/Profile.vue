<script setup>
import { computed, ref, watch } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AppLayout from '@/Components/Layouts/AppLayout.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import BaseModal from '@/Components/Base/BaseModal.vue'
import OnboardingBanner from '@/Components/Onboarding/OnboardingBanner.vue'
import ProfileTagInput from '@/Components/Profile/ProfileTagInput.vue'
import StudentProfileSidebar from '@/Components/Student/StudentProfileSidebar.vue'
import StudentProfileSkillsSection from '@/Components/Student/StudentProfileSkillsSection.vue'
import StudentProfileWorkModeSection from '@/Components/Student/StudentProfileWorkModeSection.vue'
import StudentProfileApplicationsSection from '@/Components/Student/StudentProfileApplicationsSection.vue'
import { ROUTES } from '@/Helpers/routes'

const props = defineProps({
  user: { type: Object, required: true },
  studyFields: { type: Array, default: () => [] },
  workModeOptions: { type: Array, required: true },
})

const { t } = useI18n()
const isSkillsModalOpen = ref(false)
const isWorkModeModalOpen = ref(false)
const skills = ref([])
const workModes = ref([])
const skillsDraft = ref([])
const workModesDraft = ref([])
const skillsError = ref(undefined)
const workModesError = ref(undefined)
const isSkillsSaving = ref(false)
const isWorkModesSaving = ref(false)

const profileUser = computed(() => ({
  ...props.user,
  skills: skills.value,
  work_modes: workModes.value,
}))

const displayWorkModes = computed(() => profileUser.value.work_modes)

watch(() => props.user, () => {
  skills.value = [...(props.user.skills ?? [])]
  workModes.value = [...(props.user.work_modes ?? [])]
}, { immediate: true })

function buildProfilePayload(overrides) {
  return {
    first_name: props.user.first_name ?? '',
    last_name: props.user.last_name ?? '',
    age: props.user.age ?? '',
    street: props.user.street ?? '',
    postal_code: props.user.postal_code ?? '',
    city: props.user.city ?? '',
    university: props.user.university ?? '',
    university_id: props.user.university_id ?? '',
    study_field_id: props.user.study_field_id ?? '',
    study_year: props.user.study_year ?? '',
    specialization: props.user.specialization ?? '',
    study_field_ids: [...(props.user.study_field_ids ?? [])],
    preferred_cities: [...(props.user.preferred_cities ?? [])],
    skills: [...skills.value],
    work_modes: [...workModes.value],
    ...overrides,
  }
}

function openSkillsModal() {
  skillsDraft.value = [...skills.value]
  skillsError.value = undefined
  isSkillsModalOpen.value = true
}

function saveSkills() {
  skillsError.value = undefined
  isSkillsSaving.value = true
  router.patch(ROUTES.STUDENT_PROFILE_UPDATE, buildProfilePayload({ skills: [...skillsDraft.value] }), {
    preserveScroll: true,
    onSuccess: () => {
      skills.value = [...skillsDraft.value]
      isSkillsModalOpen.value = false
    },
    onError: (errors) => {
      skillsError.value = errors.skills
    },
    onFinish: () => {
      isSkillsSaving.value = false
    },
  })
}

function openWorkModeModal() {
  workModesDraft.value = [...workModes.value]
  workModesError.value = undefined
  isWorkModeModalOpen.value = true
}

function toggleWorkMode(mode) {
  if (workModesDraft.value.includes(mode)) {
    workModesDraft.value = workModesDraft.value.filter((item) => item !== mode)
    return
  }
  workModesDraft.value = [...workModesDraft.value, mode]
}

function saveWorkModes() {
  workModesError.value = undefined
  isWorkModesSaving.value = true
  router.patch(ROUTES.STUDENT_PROFILE_UPDATE, buildProfilePayload({ work_modes: [...workModesDraft.value] }), {
    preserveScroll: true,
    onSuccess: () => {
      workModes.value = [...workModesDraft.value]
      isWorkModeModalOpen.value = false
    },
    onError: (errors) => {
      workModesError.value = errors.work_modes
    },
    onFinish: () => {
      isWorkModesSaving.value = false
    },
  })
}
</script>

<template>
  <Head :title="t('student.profile.title')" />
  <AppLayout active-page="profile">
    <div class="space-y-6">
      <OnboardingBanner />

      <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <StudentProfileSidebar :user="profileUser" />

        <div class="flex flex-col gap-6 lg:col-span-2">
          <StudentProfileSkillsSection
            :skills="profileUser.skills ?? []"
            @manage="openSkillsModal"
          />
          <StudentProfileWorkModeSection
            :work-modes="displayWorkModes"
            @manage="openWorkModeModal"
          />
          <StudentProfileApplicationsSection :applications="profileUser.applications ?? []" />
        </div>
      </div>
    </div>

    <BaseModal
      :open="isSkillsModalOpen"
      :title="t('student.profile.skills.modalTitle')"
      max-width-class="max-w-lg"
      @close="isSkillsModalOpen = false"
    >
      <ProfileTagInput
        id="profile_skills"
        v-model="skillsDraft"
        :label="t('student.profile.skills.title')"
        :placeholder="t('student.profile.skills.placeholder')"
        :max="15"
        :error="skillsError"
      />
      <div class="mt-6 flex justify-end gap-3">
        <BaseButton type="button" variant="secondary" @click="isSkillsModalOpen = false">
          {{ t('student.profile.actions.cancel') }}
        </BaseButton>
        <BaseButton type="button" :disabled="isSkillsSaving" @click="saveSkills">
          {{ t('student.profile.actions.save') }}
        </BaseButton>
      </div>
    </BaseModal>

    <BaseModal
      :open="isWorkModeModalOpen"
      :title="t('student.profile.workMode.modalTitle')"
      max-width-class="max-w-lg"
      @close="isWorkModeModalOpen = false"
    >
      <div class="flex flex-wrap gap-2">
        <button
          v-for="mode in workModeOptions"
          :key="mode"
          type="button"
          class="rounded-full border px-4 py-1.5 text-sm font-medium transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
          :class="workModesDraft.includes(mode)
            ? 'border-primary bg-primary text-white'
            : 'border-slate-400 bg-slate-100 text-text hover:bg-white'"
          :aria-pressed="workModesDraft.includes(mode)"
          @click="toggleWorkMode(mode)"
        >
          {{ t(`student.workModes.${mode}`) }}
        </button>
      </div>
      <p v-if="workModesError" class="mt-2 text-error text-sm" role="alert">{{ workModesError }}</p>
      <div class="mt-6 flex justify-end gap-3">
        <BaseButton type="button" variant="secondary" @click="isWorkModeModalOpen = false">
          {{ t('student.profile.actions.cancel') }}
        </BaseButton>
        <BaseButton type="button" :disabled="isWorkModesSaving" @click="saveWorkModes">
          {{ t('student.profile.actions.save') }}
        </BaseButton>
      </div>
    </BaseModal>
  </AppLayout>
</template>
