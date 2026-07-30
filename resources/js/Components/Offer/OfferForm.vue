<script setup>
import { ref, computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import BaseButton from '@/Components/Base/BaseButton.vue'
import BaseInput from '@/Components/Base/BaseInput.vue'
import BaseSelect from '@/Components/Base/BaseSelect.vue'
import BaseTextarea from '@/Components/Base/BaseTextarea.vue'
import BaseToggle from '@/Components/Base/BaseToggle.vue'
import DynamicMultiSelect from '@/Components/Common/DynamicMultiSelect.vue'
import DateRangeField from '@/Components/Offer/DateRangeField.vue'
import CityAutocomplete from '@/Components/Offer/CityAutocomplete.vue'
import { ROUTES } from '@/Helpers/routes'

const props = defineProps({
  studyFields: { type: Array, required: true },
  universities: { type: Array, required: true },
  offer: { type: Object, default: null },
  isCompanyVerified: { type: Boolean, default: false },
})

const uniqueStudyFields = computed(() => {
  const seen = new Map()
  for (const field of props.studyFields) {
    if (!seen.has(field.name)) seen.set(field.name, field)
  }
  return [...seen.values()]
})

const uniqueUniversities = computed(() => {
  const seen = new Map()
  for (const uni of props.universities) {
    if (!seen.has(uni.name)) seen.set(uni.name, uni)
  }
  return [...seen.values()]
})

const studyFieldNames = computed(() => uniqueStudyFields.value.map(f => f.name))
const universityNames = computed(() => uniqueUniversities.value.map(u => u.name))

const nameToStudyFieldId = (name) => uniqueStudyFields.value.find(f => f.name === name)?.id
const nameToUniversityId = (name) => uniqueUniversities.value.find(u => u.name === name)?.id

const selectedStudyFieldNames = ref(
  uniqueStudyFields.value
    .filter(f => (props.offer?.study_field_ids ?? []).includes(f.id))
    .map(f => f.name),
)
const selectedUniversityNames = ref(
  uniqueUniversities.value
    .filter(u => (props.offer?.university_ids ?? []).includes(u.id))
    .map(u => u.name),
)



const { t } = useI18n()

const isEditing = computed(() => props.offer !== null)

const form = useForm({
  title: props.offer?.title ?? '',
  description: props.offer?.description ?? '',
  spots: props.offer ? String(props.offer.spots) : '',
  city: props.offer?.city ?? '',
  start_date: props.offer?.start_date ?? '',
  end_date: props.offer?.end_date ?? '',
  work_mode: props.offer?.work_mode ?? '',
  status: props.offer?.status ?? (props.isCompanyVerified ? 'published' : 'draft'),
  is_paid: props.offer?.is_paid ?? false,
  salary_min: props.offer?.salary_min != null ? String(props.offer.salary_min) : '',
  salary_max: props.offer?.salary_max != null ? String(props.offer.salary_max) : '',
  study_field_ids: props.offer?.study_field_ids ?? [],
  university_ids: props.offer?.university_ids ?? [],
})

watch(() => form.is_paid, (isPaid) => {
  if (!isPaid) {
    form.salary_min = ''
    form.salary_max = ''
    delete form.errors.salary_min
    delete form.errors.salary_max
  }
})

watch(() => form.salary_min, (value) => {
  if (value !== '' && value !== null) {
    form.salary_min = String(value)
  }
})

watch(() => form.salary_max, (value) => {
  if (value !== '' && value !== null) {
    form.salary_max = String(value)
  }
})

watch(() => form.spots, (value) => {
  if (value !== '' && value !== null) {
    form.spots = String(value)
  }
})

const isPublished = computed({
  get: () => form.status === 'published',
  set: (value) => { form.status = value ? 'published' : 'draft' },
})

const workModeOptions = computed(() => [
  { value: 'onSite', label: t('company.offers.form.workModeOptions.onSite') },
  { value: 'hybrid', label: t('company.offers.form.workModeOptions.hybrid') },
  { value: 'remote', label: t('company.offers.form.workModeOptions.remote') },
])

const fieldError = (field) => {
  if (form.errors[field]) {
    return form.errors[field]
  }

  const nestedKey = Object.keys(form.errors).find((key) => key.startsWith(`${field}.`))

  return nestedKey ? form.errors[nestedKey] : undefined
}

const submit = () => {
  form.study_field_ids = selectedStudyFieldNames.value
    .map(nameToStudyFieldId)
    .filter(id => id !== undefined)

  form.university_ids = selectedUniversityNames.value
    .map(nameToUniversityId)
    .filter(id => id !== undefined)

  if (isEditing.value) {
    form.patch(ROUTES.COMPANY_OFFERS_UPDATE(props.offer.id), { preserveScroll: true })
  } else {
    form.post(ROUTES.COMPANY_OFFERS_STORE, { preserveScroll: true })
  }
}
</script>

<template>
  <form class="flex flex-col gap-6" novalidate @submit.prevent="submit">
    <section class="bg-secondary/5 shadow-sm p-6 border border-border rounded-3xl">
      <div class="flex flex-col gap-3">
        <div>
          <h2 class="text-xl font-bold text-text">
            {{ t('company.offers.form.section.details') }}
          </h2>
          <p class="mt-1 text-additional text-sm">
            {{ t('company.offers.form.section.detailsHint') }}
          </p>
        </div>

        <BaseInput id="title" v-model="form.title" :label="t('company.offers.form.title')" required :maxlength="255"
                   :error="fieldError('title')"
        />

        <DateRangeField v-model:start="form.start_date" v-model:end="form.end_date" start-id="start_date"
                        end-id="end_date" :start-label="t('company.offers.form.startDate')"
                        :end-label="t('company.offers.form.endDate')" :start-error="fieldError('start_date')"
                        :end-error="fieldError('end_date')" required stacked
        />

        <div class="gap-4 grid grid-cols-1 sm:grid-cols-2">
          <BaseSelect id="work_mode" v-model="form.work_mode" :label="t('company.offers.form.workMode')"
                      :options="workModeOptions" :placeholder="t('company.offers.form.workModePlaceholder')" required
                      :error="fieldError('work_mode')" stacked
          />

          <div>
            <BaseInput id="spots" v-model="form.spots" type="number" :label="t('company.offers.form.spots')" required
                       :error="fieldError('spots')" max="1000"
            />
            <p class="mt-1 text-additional text-sm">
              {{ t('company.offers.form.spotsMaxHint') }}
            </p>
          </div>
        </div>

        <CityAutocomplete id="city" v-model="form.city" :label="t('company.offers.form.city')" required
                          :error="fieldError('city')" stacked
        />
      </div>
    </section>
    <section class="bg-white shadow-sm p-6 border border-border rounded-3xl">
      <div class="flex flex-col gap-3">
        <div>
          <h2 class="text-xl font-bold text-text">
            {{ t('company.offers.form.section.requirements') }}
          </h2>
          <p class="mt-1 text-additional text-sm">
            {{ t('company.offers.form.section.requirementsHint') }}
          </p>
        </div>

        <div class="gap-5 grid">
          <div>
            <DynamicMultiSelect
              id="study_field_ids"
              v-model="selectedStudyFieldNames"
              :label="t('company.offers.form.preferredFields')"
              :options="studyFieldNames"
            />
            <p v-if="fieldError('study_field_ids')" class="text-error text-sm" role="alert">
              {{ fieldError('study_field_ids') }}
            </p>
          </div>

          <div>
            <DynamicMultiSelect
              id="university_ids"
              v-model="selectedUniversityNames"
              :label="t('company.offers.form.preferredUniversities')"
              :options="universityNames"
            />
            <p v-if="fieldError('university_ids')" class="text-error text-sm" role="alert">
              {{ fieldError('university_ids') }}
            </p>
          </div>
        </div>
      </div>
    </section>

    <section class="bg-white shadow-sm p-6 border border-border rounded-3xl">
      <div class="flex flex-col gap-3">
        <div>
          <h2 class="text-xl font-bold text-text">
            {{ t('company.offers.form.section.description') }}
          </h2>
          <p class="mt-1 text-additional text-sm">
            {{ t('company.offers.form.section.descriptionHint') }}
          </p>
        </div>

        <BaseTextarea id="description" v-model="form.description" :label="t('company.offers.form.description')" required
                      :maxlength="10000" :rows="6" :error="fieldError('description')"
        />
      </div>
    </section>

    <section class="bg-white shadow-sm p-6 border border-border rounded-3xl">
      <div class="flex flex-col gap-3">
        <div class="flex sm:flex-row flex-col sm:justify-between sm:items-center gap-2">
          <div>
            <h2 class="text-xl font-bold text-text">
              {{ t('company.offers.form.section.compensation') }}
            </h2>
            <p class="mt-1 text-additional text-sm">
              {{ t('company.offers.form.section.compensationHint') }}
            </p>
          </div>

          <BaseToggle id="is_paid" v-model="form.is_paid" :label="t('company.offers.form.isPaid')" />
        </div>

        <div v-if="form.is_paid" class="gap-4 grid grid-cols-1 sm:grid-cols-2">
          <BaseInput id="salary_min" v-model="form.salary_min" type="number" :label="t('company.offers.form.salaryMin')"
                     required :error="fieldError('salary_min')"
          />
          <BaseInput id="salary_max" v-model="form.salary_max" type="number" :label="t('company.offers.form.salaryMax')"
                     required :error="fieldError('salary_max')"
          />
        </div>
      </div>
    </section>

    <section class="bg-white shadow-sm p-6 border border-border rounded-3xl">
      <div class="flex flex-col gap-3">
        <div>
          <h2 class="text-xl font-bold text-text">
            {{ t('company.offers.form.section.status') }}
          </h2>
          <p class="mt-1 text-additional text-sm">
            {{ t('company.offers.form.draftHint') }}
          </p>
        </div>

        <BaseToggle id="status" v-model="isPublished"
                    :label="isPublished ? t('company.offers.form.published') : t('company.offers.form.draft')"
        />
        <p v-if="fieldError('status')" class="text-error text-sm" role="alert">
          {{ fieldError('status') }}
        </p>
      </div>
    </section>

    <div class="flex justify-end">
      <BaseButton type="submit" class="px-8 w-full sm:w-auto" :disabled="form.processing">
        {{ isEditing ? t('company.offers.form.submitEdit') : t('company.offers.form.submitCreate') }}
      </BaseButton>
    </div>
  </form>
</template>
