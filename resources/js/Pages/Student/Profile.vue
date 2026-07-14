<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { Head, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { IconHome, IconUser } from '@tabler/icons-vue'
import BaseLayout from '@/Components/Layouts/BaseLayout.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import BaseModal from '@/Components/Common/BaseModal.vue'
import OnboardingBanner from '@/Components/Onboarding/OnboardingBanner.vue'
import ProfileTagInput from '@/Components/Profile/ProfileTagInput.vue'
import StudentProfileSidebar from '@/Components/Student/StudentProfileSidebar.vue'
import StudentProfileSkillsSection from '@/Components/Student/StudentProfileSkillsSection.vue'
import StudentProfileWorkModeSection from '@/Components/Student/StudentProfileWorkModeSection.vue'
import StudentProfileApplicationsSection from '@/Components/Student/StudentProfileApplicationsSection.vue'
import StudentAccountSettingsSection from '@/Components/Student/StudentAccountSettingsSection.vue'
import StudentProfileEditModal from '@/Components/Student/StudentProfileEditModal.vue'
import { ROUTES } from '@/Helpers/routes'

const props = defineProps({
  user: { type: Object, required: true },
  studyFields: { type: Array, default: () => [] },
})

const { t } = useI18n()
const page = usePage()
const isEditOpen = ref(false)
const focusSection = ref(null)
const isSkillsModalOpen = ref(false)
const isWorkModeModalOpen = ref(false)
const skills = ref([])
const workModes = ref([])
const skillsDraft = ref([])
const workModesDraft = ref([])

const profileUser = computed(() => ({
  ...props.user,
  skills: skills.value,
  work_modes: workModes.value,
}))

const workModeOptions = computed(() => [
  t('student.profile.workMode.options.onsite'),
  t('student.profile.workMode.options.remote'),
  t('student.profile.workMode.options.hybrid'),
])

watch(() => props.user, () => {
  skills.value = [...(props.user.skills ?? [])]
  workModes.value = [...(props.user.work_modes ?? [])]
}, { immediate: true })

function openSkillsModal() {
  skillsDraft.value = [...skills.value]
  isSkillsModalOpen.value = true
}

function saveSkills() {
  skills.value = [...skillsDraft.value]
  isSkillsModalOpen.value = false
}

function openWorkModeModal() {
  workModesDraft.value = [...workModes.value]
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
  workModes.value = [...workModesDraft.value]
  isWorkModeModalOpen.value = false
}

function syncSectionFromUrl() {
  const section = new URLSearchParams(window.location.search).get('section')
  if (!section) return
  focusSection.value = section
  isEditOpen.value = true
}

onMounted(syncSectionFromUrl)
watch(() => page.url, syncSectionFromUrl)

function closeEditModal() {
  isEditOpen.value = false
  focusSection.value = null
}

const navItems = computed(() => [
  { key: 'dashboard', label: t('student.layout.nav.dashboard'), href: ROUTES.STUDENT_DASHBOARD, icon: IconHome },
  { key: 'profile', label: t('student.layout.nav.profile'), href: ROUTES.STUDENT_PROFILE, icon: IconUser },
])
</script>

<template>
  <Head :title="t('student.profile.title')" />
  <BaseLayout
    active-page="profile"
    :nav-items="navItems"
    :minimal-header="true"
    :show-background="false"
  >
    <OnboardingBanner />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
      <StudentProfileSidebar
        :user="profileUser"
        @edit="isEditOpen = true"
      />

      <div class="flex flex-col gap-6 lg:col-span-2">
        <StudentProfileSkillsSection
          :skills="profileUser.skills ?? []"
          @manage="openSkillsModal"
        />
        <StudentProfileWorkModeSection
          :work-modes="profileUser.work_modes ?? []"
          @manage="openWorkModeModal"
        />
        <StudentProfileApplicationsSection :applications="profileUser.applications ?? []" />
        <StudentAccountSettingsSection
          :email="user.email"
          :email-verified-at="user.email_verified_at"
          :pending-email="user.pending_email"
        />
      </div>
    </div>

    <StudentProfileEditModal
      :open="isEditOpen"
      :user="profileUser"
      :study-fields="studyFields"
      :focus-section="focusSection"
      @close="closeEditModal"
    />

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
      />
      <div class="mt-6 flex justify-end gap-3">
        <BaseButton type="button" variant="secondary" @click="isSkillsModalOpen = false">
          {{ t('student.profile.actions.cancel') }}
        </BaseButton>
        <BaseButton type="button" @click="saveSkills">
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
          {{ mode }}
        </button>
      </div>
      <div class="mt-6 flex justify-end gap-3">
        <BaseButton type="button" variant="secondary" @click="isWorkModeModalOpen = false">
          {{ t('student.profile.actions.cancel') }}
        </BaseButton>
        <BaseButton type="button" @click="saveWorkModes">
          {{ t('student.profile.actions.save') }}
        </BaseButton>
      </div>
    </BaseModal>
  </BaseLayout>
</template>
