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
  status: props.offer?.status ?? 'draft',
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
    <section class="rounded-3xl border border-border bg-secondary/5 p-6 shadow-sm">
      <div class="flex flex-col gap-3">
        <div>
          <p class="text-sm font-semibold uppercase tracking-[0.25em] text-additional">
            {{ t('company.offers.form.section.details') }}
          </p>
          <p class="mt-1 text-sm text-additional">
            {{ t('company.offers.form.section.detailsHint') }}
          </p>
        </div>

        <BaseInput id="title" v-model="form.title" :label="t('company.offers.form.title')" required :maxlength="255"
                   stacked :error="fieldError('title')"
        />

        <DateRangeField v-model:start="form.start_date" v-model:end="form.end_date" start-id="start_date"
                        end-id="end_date" :start-label="t('company.offers.form.startDate')"
                        :end-label="t('company.offers.form.endDate')" :start-error="fieldError('start_date')"
                        :end-error="fieldError('end_date')" required
        />

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <BaseSelect id="work_mode" v-model="form.work_mode" :label="t('company.offers.form.workMode')"
                      :options="workModeOptions" :placeholder="t('company.offers.form.workModePlaceholder')" required
                      :error="fieldError('work_mode')"
          />

          <BaseInput id="spots" v-model="form.spots" type="number" :label="t('company.offers.form.spots')" required
                     stacked :error="fieldError('spots')"
          />
        </div>

        <CityAutocomplete id="city" v-model="form.city" :label="t('company.offers.form.city')" required
                          stacked :error="fieldError('city')"
        />
      </div>
    </section>

    <section class="rounded-3xl border border-border bg-white p-6 shadow-sm">
      <div class="grid gap-5">
        <div>
          <label for="study_field_ids" class="mb-1 block text-sm font-medium text-text">
            {{ t('company.offers.form.preferredFields') }}
          </label>
          <DynamicMultiSelect id="study_field_ids" v-model="selectedStudyFieldNames" :options="studyFieldNames" />
          <p v-if="fieldError('study_field_ids')" class="text-sm text-error" role="alert">
            {{ fieldError('study_field_ids') }}
          </p>
        </div>

        <div>
          <label for="university_ids" class="mb-1 block text-sm font-medium text-text">
            {{ t('company.offers.form.preferredUniversities') }}
          </label>
          <DynamicMultiSelect id="university_ids" v-model="selectedUniversityNames" :options="universityNames" />
          <p v-if="fieldError('university_ids')" class="text-sm text-error" role="alert">
            {{ fieldError('university_ids') }}
          </p>
        </div>
      </div>
    </section>

    <section class="rounded-3xl border border-border bg-white p-6 shadow-sm">
      <div class="flex flex-col gap-3">
        <div>
          <p class="text-sm font-semibold uppercase tracking-[0.25em] text-additional">
            {{ t('company.offers.form.section.description') }}
          </p>
          <p class="mt-1 text-sm text-additional">
            {{ t('company.offers.form.section.descriptionHint') }}
          </p>
        </div>

        <BaseTextarea id="description" v-model="form.description" :label="t('company.offers.form.description')" required
                      :maxlength="10000" :rows="6" :error="fieldError('description')"
        />
      </div>
    </section>

    <section class="rounded-3xl border border-border bg-white p-6 shadow-sm">
      <div class="flex flex-col gap-4">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <p class="text-sm font-semibold text-text">
              {{ t('company.offers.form.section.compensation') }}
            </p>
            <p class="text-sm text-additional">
              {{ t('company.offers.form.section.compensationHint') }}
            </p>
          </div>

          <BaseToggle id="is_paid" v-model="form.is_paid" :label="t('company.offers.form.isPaid')" />
        </div>

        <div v-if="form.is_paid" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <BaseInput id="salary_min" v-model="form.salary_min" type="number" :label="t('company.offers.form.salaryMin')"
                     stacked required :error="fieldError('salary_min')"
          />
          <BaseInput id="salary_max" v-model="form.salary_max" type="number" :label="t('company.offers.form.salaryMax')"
                     stacked required :error="fieldError('salary_max')"
          />
        </div>
      </div>
    </section>

    <section class="rounded-3xl border border-border bg-white p-6 shadow-sm">
      <div class="flex flex-col gap-4">
        <BaseToggle id="status" v-model="isPublished"
                    :label="isPublished ? t('company.offers.form.published') : t('company.offers.form.draft')"
        />
        <p class="text-sm text-additional">
          {{ t('company.offers.form.draftHint') }}
        </p>
        <p v-if="fieldError('status')" class="text-sm text-error" role="alert">
          {{ fieldError('status') }}
        </p>
      </div>
    </section>

    <div class="flex justify-end">
      <BaseButton type="submit" class="w-full sm:w-auto px-8" :disabled="form.processing">
        {{ isEditing ? t('company.offers.form.submitEdit') : t('company.offers.form.submitCreate') }}
      </BaseButton>
    </div>
  </form>
</template>
