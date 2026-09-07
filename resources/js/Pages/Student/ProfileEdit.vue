<script setup>
import { computed, nextTick, onMounted, ref, watch } from 'vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { useMapboxGeocoding } from '@/Composables/useMapboxGeocoding'
import { useUniversityFaculties } from '@/Composables/useUniversityFaculties'
import { IconArrowLeft } from '@tabler/icons-vue'
import BaseInput from '@/Components/Base/BaseInput.vue'
import BaseSelect from '@/Components/Base/BaseSelect.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import CityAutocomplete from '@/Components/Common/CityAutocomplete.vue'
import DynamicMultiSelect from '@/Components/Common/DynamicMultiSelect.vue'
import ProfilePhotoUpload from '@/Components/Student/ProfilePhotoUpload.vue'
import BaseMaskedInput from '@/Components/Base/BaseMaskedInput.vue'
import CvUploadSection from '@/Components/Student/CvUploadSection.vue'
import ProfilePageCard from '@/Components/Profile/ProfilePageCard.vue'
import UniversityAutocomplete from '@/Components/Profile/UniversityAutocomplete.vue'
import AppLayout from '@/Components/Layouts/AppLayout.vue'
import { ROUTES } from '@/Helpers/routes'

const props = defineProps({
  user: { type: Object, required: true },
  studyFields: { type: Array, default: () => [] },
  faculties: { type: Array, default: () => [] },
  universityOrganization: { type: Object, default: null },
  suggestedUniversity: { type: Object, default: null },
})

const { t } = useI18n()
const { cityOptions, fetchSuggestions: fetchCitySuggestions } = useMapboxGeocoding()
const photoUpload = ref(null)
const pendingPhoto = ref(null)

const photoForm = useForm({ photo: null })

const profileForm = useForm({
  first_name: props.user.first_name ?? '',
  last_name: props.user.last_name ?? '',
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
  skills: [...(props.user.skills ?? [])],
  work_modes: [...(props.user.work_modes ?? [])],
})

if (!profileForm.university && props.suggestedUniversity) {
  profileForm.university = props.suggestedUniversity.name
}

const isUniversitySuggested = computed(() =>
  !props.universityOrganization
  && props.suggestedUniversity !== null
  && profileForm.university === props.suggestedUniversity.name,
)

const { faculties, loadFaculties } = useUniversityFaculties(props.faculties)
const selectedFacultyId = ref(props.user.faculty_id ?? '')

const hasUniversity = computed(() => Boolean(profileForm.university_id))

const facultyOptions = computed(() => faculties.value.map((faculty) => ({
  value: faculty.id,
  label: faculty.name,
})))

const studyFieldOptions = computed(() => {
  const faculty = faculties.value.find((item) => item.id === selectedFacultyId.value)

  return (faculty?.study_fields ?? []).map((field) => ({
    value: field.id,
    label: field.name,
  }))
})

const selectedUniversityName = ref(profileForm.university)

function selectUniversity(university) {
  selectedUniversityName.value = university.name
  profileForm.university_id = university.id
  selectedFacultyId.value = ''
  profileForm.study_field_id = ''
  loadFaculties(university.id)
}

watch(() => profileForm.university, (name) => {
  if (!profileForm.university_id || name === selectedUniversityName.value) {
    return
  }

  profileForm.university_id = ''
  selectedFacultyId.value = ''
  profileForm.study_field_id = ''
  faculties.value = []
})

watch(selectedFacultyId, () => {
  if (!studyFieldOptions.value.some((option) => option.value === profileForm.study_field_id)) {
    profileForm.study_field_id = ''
  }
})

const facultyHint = computed(() => {
  if (!hasUniversity.value) {
    return t('student.profile.edit.selectUniversityFirst')
  }

  return facultyOptions.value.length === 0
    ? t('student.profile.edit.noFacultiesForUniversity')
    : undefined
})

const studyFieldHint = computed(() => {
  if (!selectedFacultyId.value) {
    return t('student.profile.edit.selectFacultyFirst')
  }

  return studyFieldOptions.value.length === 0
    ? t('student.profile.edit.noStudyFieldsForFaculty')
    : undefined
})

const hasPhotoPending = computed(() => Boolean(pendingPhoto.value))
const fieldError = (field) => profileForm.errors[field]

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
  <AppLayout active-page="profile">
    <div class="mx-auto w-full max-w-3xl">
      <h1 class="mt-4 font-semibold text-text text-2xl">
        {{ t('student.profile.edit.title') }}
      </h1>

      <div class="mt-6 flex w-full flex-col gap-6">
        <ProfilePageCard>
          <ProfilePhotoUpload
            ref="photoUpload"
            v-model:pending-file="pendingPhoto"
            :photo-url="user.photo_url"
            :first-name="profileForm.first_name"
            :last-name="profileForm.last_name"
            :server-error="photoForm.errors.photo"
          />
        </ProfilePageCard>

        <ProfilePageCard id="profile-section-cv">
          <CvUploadSection :cv-path="user.cv_path" />
        </ProfilePageCard>

        <ProfilePageCard>
          <h2 class="mb-3 font-semibold text-text text-base">
            {{ t('student.profile.edit.basicData') }}
          </h2>
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
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
            <div class="sm:col-span-2 grid grid-cols-1 gap-4 md:grid-cols-[1fr_2fr]">
              <BaseMaskedInput
                id="edit_postal_code"
                v-model="profileForm.postal_code"
                mask="##-###"
                inputmode="numeric"
                autocomplete="postal-code"
                :label="t('student.profile.edit.postalCode')"
                :error="fieldError('postal_code')"
              />
              <CityAutocomplete
                id="edit_city"
                v-model="profileForm.city"
                :label="t('student.profile.edit.city')"
                :error="fieldError('city')"
              />
            </div>
            <div class="sm:col-span-2">
              <BaseInput
                id="edit_street"
                v-model="profileForm.street"
                :label="t('student.profile.edit.street')"
                autocomplete="street-address"
                :error="fieldError('street')"
              />
            </div>
          </div>
        </ProfilePageCard>

        <ProfilePageCard>
          <h2 class="mb-1 font-semibold text-text text-base">
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
            <DynamicMultiSelect
              id="edit_cities"
              v-model="profileForm.preferred_cities"
              :label="t('student.profile.details.cities')"
              :placeholder="t('student.profile.details.citiesPlaceholder')"
              :options="cityOptions"
              :max="10"
              :error="fieldError('preferred_cities')"
              allow-custom
              remote
              @search="fetchCitySuggestions"
            />
          </div>
        </ProfilePageCard>

        <ProfilePageCard id="profile-section-university">
          <h2 class="mb-3 font-semibold text-text text-base">
            {{ t('student.profile.edit.education') }}
          </h2>
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
              <UniversityAutocomplete
                id="edit_university"
                v-model="profileForm.university"
                :label="t('student.profile.edit.university')"
                @select="selectUniversity"
              />

              <p
                v-if="isUniversitySuggested"
                class="mt-1 text-primary text-xs"
              >
                *{{ t('student.profile.edit.universitySuggestedNote') }}
              </p>

              <p
                v-if="fieldError('university')"
                id="edit_university-error"
                class="mt-1 text-error text-sm"
                role="alert"
              >
                {{ fieldError('university') }}
              </p>
            </div>
            <div class="sm:col-span-2 grid grid-cols-1 gap-4 sm:grid-cols-2">
              <BaseSelect
                id="edit_faculty"
                v-model="selectedFacultyId"
                :label="t('student.profile.edit.faculty')"
                :options="facultyOptions"
                :placeholder="t('student.profile.edit.facultyPlaceholder')"
                :disabled="facultyOptions.length === 0"
                :hint="facultyHint"
              />
              <BaseSelect
                id="edit_study_field"
                v-model="profileForm.study_field_id"
                :label="t('student.profile.edit.studyField')"
                :options="studyFieldOptions"
                :placeholder="t('student.profile.edit.studyFieldPlaceholder')"
                :disabled="studyFieldOptions.length === 0"
                :hint="studyFieldHint"
                :error="fieldError('study_field_id')"
              />
            </div>
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
          </div>
        </ProfilePageCard>

        <div class="flex flex-row flex-wrap justify-end gap-3 pb-4 pt-2">
          <BaseButton
            type="button"
            variant="secondary"
            class="flex-1 justify-center sm:flex-none"
            @click="router.visit(ROUTES.STUDENT_PROFILE)"
          >
            {{ t('student.profile.actions.cancel') }}
          </BaseButton>
          <BaseButton
            v-if="hasPhotoPending"
            type="button"
            variant="secondary"
            class="w-full justify-center sm:w-auto"
            @click="photoUpload?.clearPending(); pendingPhoto = null"
          >
            {{ t('student.profile.photo.cancelPreview') }}
          </BaseButton>
          <BaseButton
            type="button"
            class="flex-1 justify-center sm:flex-none"
            :disabled="profileForm.processing || photoForm.processing"
            @click="saveAll"
          >
            {{ t('student.profile.edit.saveChanges') }}
          </BaseButton>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
