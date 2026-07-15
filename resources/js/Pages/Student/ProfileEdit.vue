<script setup>
import { computed, nextTick, onMounted, ref } from 'vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { IconArrowLeft, IconHome, IconUser } from '@tabler/icons-vue'
import BaseLayout from '@/Components/Layouts/BaseLayout.vue'
import BaseInput from '@/Components/Base/BaseInput.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import ProfileTagInput from '@/Components/Profile/ProfileTagInput.vue'
import DynamicMultiSelect from '@/Components/Common/DynamicMultiSelect.vue'
import ProfilePhotoUpload from '@/Components/Student/ProfilePhotoUpload.vue'
import CvUploadSection from '@/Components/Student/CvUploadSection.vue'
import { ROUTES } from '@/Helpers/routes'

const props = defineProps({
  user: { type: Object, required: true },
  studyFields: { type: Array, default: () => [] },
})

const { t } = useI18n()
const photoUpload = ref(null)
const pendingPhoto = ref(null)

const universityOptions = [
  'Collegium Witelona',
  'Politechnika Wrocławska',
  'Uniwersytet Wrocławski',
  'Uniwersytet Ekonomiczny we Wrocławiu',
  'Akademia Nauk Stosowanych Angelusa Silesiusa',
]

const photoForm = useForm({ photo: null })

const profileForm = useForm({
  first_name: props.user.first_name ?? '',
  last_name: props.user.last_name ?? '',
  age: props.user.age ?? '',
  location: props.user.location ?? '',
  university: props.user.university ?? '',
  study_field: props.user.study_field ?? '',
  study_year: props.user.study_year ?? '',
  specialization: props.user.specialization ?? '',
  study_field_ids: [...(props.user.study_field_ids ?? [])],
  preferred_cities: [...(props.user.preferred_cities ?? [])],
})

const hasPhotoPending = computed(() => Boolean(pendingPhoto.value))
const fieldError = (field) => profileForm.errors[field]

const navItems = computed(() => [
  { key: 'dashboard', label: t('student.layout.nav.dashboard'), href: ROUTES.STUDENT_DASHBOARD, icon: IconHome },
  { key: 'profile', label: t('student.layout.nav.profile'), href: ROUTES.STUDENT_PROFILE, icon: IconUser },
])

function scrollToFocusSection() {
  const section = new URLSearchParams(window.location.search).get('section')
  if (!section) return
  nextTick(() => {
    document.getElementById(`profile-section-${section}`)?.scrollIntoView({ behavior: 'smooth', block: 'start' })
  })
}

onMounted(scrollToFocusSection)

function saveProfile() {
  profileForm.patch(ROUTES.STUDENT_PROFILE_UPDATE, {
    preserveScroll: true,
    onSuccess: () => router.visit(ROUTES.STUDENT_PROFILE),
  })
}

function saveAll() {
  if (hasPhotoPending.value) {
    photoForm.photo = pendingPhoto.value
    photoForm.post(ROUTES.STUDENT_PROFILE_PHOTO, {
      forceFormData: true,
      preserveScroll: true,
      onSuccess: () => {
        photoUpload.value?.clearPending()
        pendingPhoto.value = null
        if (profileForm.isDirty) {
          saveProfile()
        } else {
          router.visit(ROUTES.STUDENT_PROFILE)
        }
      },
    })
    return
  }
  if (profileForm.isDirty) {
    saveProfile()
    return
  }
  router.visit(ROUTES.STUDENT_PROFILE)
}
</script>

<template>
  <Head :title="t('student.profile.edit.title')" />
  <BaseLayout
    active-page="profile"
    :nav-items="navItems"
    :minimal-header="true"
    :show-background="false"
  >
    <div class="mx-auto w-full max-w-4xl">
      <Link
        :href="ROUTES.STUDENT_PROFILE"
        class="inline-flex items-center gap-2 text-additional text-sm transition hover:text-text"
      >
        <IconArrowLeft class="h-4 w-4" aria-hidden="true" />
        {{ t('student.profile.edit.backToProfile') }}
      </Link>

      <h1 class="mt-4 font-semibold text-text text-2xl">
        {{ t('student.profile.edit.title') }}
      </h1>

      <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <ProfilePhotoUpload
          ref="photoUpload"
          v-model:pending-file="pendingPhoto"
          :photo-url="user.photo_url"
          :first-name="profileForm.first_name"
          :last-name="profileForm.last_name"
          :server-error="photoForm.errors.photo"
        />

        <h2 class="mb-3 mt-8 font-medium text-text text-sm">
          {{ t('student.profile.edit.basicData') }}
        </h2>
        <form class="grid grid-cols-1 gap-4 sm:grid-cols-2" novalidate @submit.prevent>
          <BaseInput
            id="edit_first_name"
            v-model="profileForm.first_name"
            :label="t('student.profile.details.firstName')"
            :error="fieldError('first_name')"
          />
          <BaseInput
            id="edit_last_name"
            v-model="profileForm.last_name"
            :label="t('student.profile.details.lastName')"
            :error="fieldError('last_name')"
          />
          <div class="sm:col-span-2">
            <p class="text-additional text-sm">
              {{ t('student.profile.email.label') }}
            </p>
            <p class="mt-1 rounded-lg border border-border bg-background px-4 py-3 text-additional text-sm">
              {{ user.email }}
            </p>
            <p class="mt-1 text-additional text-xs">
              {{ t('student.profile.edit.emailReadonlyHint') }}
            </p>
          </div>
          <BaseInput
            id="edit_age"
            v-model="profileForm.age"
            class="max-w-36"
            stacked
            :label="t('student.profile.edit.ageLabel')"
            :error="fieldError('age')"
          />
          <BaseInput
            id="edit_location"
            v-model="profileForm.location"
            class="sm:col-span-2"
            stacked
            :label="t('student.profile.edit.locationLabel')"
            :error="fieldError('location')"
          />
        </form>

        <h2 class="mb-1 mt-8 font-medium text-text text-sm">
          {{ t('student.profile.edit.searchPreferences') }}
        </h2>
        <p class="mb-3 text-additional text-xs">
          {{ t('student.profile.edit.searchPreferencesHint') }}
        </p>
        <div class="grid grid-cols-1 gap-4">
          <DynamicMultiSelect
            id="edit_fields"
            v-model="profileForm.study_field_ids"
            :label="t('student.profile.details.fields')"
            :options="studyFields"
            :max="10"
            :placeholder="t('student.profile.details.fieldsPlaceholder')"
            :error="fieldError('study_field_ids')"
          />
          <ProfileTagInput
            id="edit_cities"
            v-model="profileForm.preferred_cities"
            :label="t('student.profile.details.cities')"
            :placeholder="t('student.profile.details.citiesPlaceholder')"
            :error="fieldError('preferred_cities')"
          />
        </div>

        <h2 id="profile-section-university" class="mb-3 mt-8 font-medium text-text text-sm">
          {{ t('student.profile.edit.education') }}
        </h2>
        <form class="grid grid-cols-1 gap-4 sm:grid-cols-2" novalidate @submit.prevent>
          <div class="sm:col-span-2">
            <label for="edit_university" class="mb-1 block text-additional text-sm">
              {{ t('student.profile.edit.university') }}
            </label>
            <select
              id="edit_university"
              v-model="profileForm.university"
              class="w-full rounded-lg border border-border bg-white px-4 py-3 text-base text-text transition-all focus:border-text focus:outline-none focus:ring-2 focus:ring-primary/30"
              :aria-invalid="fieldError('university') ? true : undefined"
              :aria-describedby="fieldError('university') ? 'edit_university-error' : undefined"
            >
              <option
                v-for="university in universityOptions"
                :key="university"
                :value="university"
              >
                {{ university }}
              </option>
            </select>
            <p
              v-if="fieldError('university')"
              id="edit_university-error"
              class="mt-1 text-error text-sm"
              role="alert"
            >
              {{ fieldError('university') }}
            </p>
          </div>
          <BaseInput
            id="edit_study_field"
            v-model="profileForm.study_field"
            :label="t('student.profile.edit.studyField')"
            :error="fieldError('study_field')"
          />
          <BaseInput
            id="edit_study_year"
            v-model="profileForm.study_year"
            :label="t('student.profile.edit.studyYear')"
            :error="fieldError('study_year')"
          />
          <BaseInput
            id="edit_specialization"
            v-model="profileForm.specialization"
            class="sm:col-span-2"
            :label="t('student.profile.edit.specialization')"
            :error="fieldError('specialization')"
          />
        </form>

        <div id="profile-section-cv" class="mt-8">
          <CvUploadSection :cv-path="user.cv_path" />
        </div>

        <div class="mt-8 flex flex-col-reverse gap-3 border-t border-border pt-6 sm:flex-row sm:justify-end">
          <BaseButton
            type="button"
            variant="secondary"
            class="justify-center sm:w-auto"
            @click="router.visit(ROUTES.STUDENT_PROFILE)"
          >
            {{ t('student.profile.actions.cancel') }}
          </BaseButton>
          <div class="flex flex-col gap-3 sm:flex-row">
            <BaseButton
              v-if="hasPhotoPending"
              type="button"
              variant="secondary"
              class="justify-center"
              @click="photoUpload?.clearPending(); pendingPhoto = null"
            >
              {{ t('student.profile.photo.cancelPreview') }}
            </BaseButton>
            <BaseButton
              type="button"
              class="justify-center"
              :disabled="profileForm.processing || photoForm.processing"
              @click="saveAll"
            >
              {{ t('student.profile.edit.saveChanges') }}
            </BaseButton>
          </div>
        </div>
      </div>
    </div>
  </BaseLayout>
</template>
