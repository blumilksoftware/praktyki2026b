<script setup>
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import BaseButton from '@/Components/Base/BaseButton.vue'
import BaseInput from '@/Components/Base/BaseInput.vue'
import BaseSelect from '@/Components/Base/BaseSelect.vue'
import BaseTextarea from '@/Components/Base/BaseTextarea.vue'
import BaseToggle from '@/Components/Base/BaseToggle.vue'
import MultiSelect from '@/Components/Common/MultiSelect.vue'
import DateRangeField from '@/Components/Offer/DateRangeField.vue'
import { ROUTES } from '@/Helpers/routes'

const props = defineProps({
  studyFields: { type: Array, required: true },
  universities: { type: Array, required: true },
  offer: { type: Object, default: null },
})

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

const isPublished = computed({
  get: () => form.status === 'published',
  set: (value) => { form.status = value ? 'published' : 'draft' },
})

const workModeOptions = computed(() => [
  { value: 'onSite', label: t('company.offers.form.workModeOptions.onSite') },
  { value: 'hybrid', label: t('company.offers.form.workModeOptions.hybrid') },
  { value: 'remote', label: t('company.offers.form.workModeOptions.remote') },
])

const fieldError = (field) => form.errors[field]

const submit = () => {
  if (isEditing.value) {
    form.patch(`/company/offers/${props.offer.id}`, { preserveScroll: true })
  } else {
    form.post(ROUTES.COMPANY_OFFERS_STORE, { preserveScroll: true })
  }
}
</script>

<template>
  <form class="flex flex-col gap-5" novalidate @submit.prevent="submit">
    <BaseInput
      id="title"
      v-model="form.title"
      :label="t('company.offers.form.title')"
      required
      :maxlength="255"
      :error="fieldError('title')"
    />

    <DateRangeField
      v-model:start="form.start_date"
      v-model:end="form.end_date"
      start-id="start_date"
      end-id="end_date"
      :start-label="t('company.offers.form.startDate')"
      :end-label="t('company.offers.form.endDate')"
      :start-error="fieldError('start_date')"
      :end-error="fieldError('end_date')"
      required
    />

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
      <BaseSelect
        id="work_mode"
        v-model="form.work_mode"
        :label="t('company.offers.form.workMode')"
        :options="workModeOptions"
        :placeholder="t('company.offers.form.workModePlaceholder')"
        required
        :error="fieldError('work_mode')"
      />

      <BaseInput
        id="spots"
        v-model="form.spots"
        type="number"
        :label="t('company.offers.form.spots')"
        required
        :error="fieldError('spots')"
      />
    </div>

    <BaseInput
      id="city"
      v-model="form.city"
      :label="t('company.offers.form.city')"
      autocomplete="address-level2"
      required
      :error="fieldError('city')"
    />

    <MultiSelect
      id="study_field_ids"
      v-model="form.study_field_ids"
      :options="studyFields"
      :label="t('company.offers.form.preferredFields')"
      :placeholder="t('company.offers.form.preferredFieldsPlaceholder')"
      :empty-state-label="t('company.offers.form.noMoreOptions')"
    />

    <MultiSelect
      id="university_ids"
      v-model="form.university_ids"
      :options="universities"
      :label="t('company.offers.form.preferredUniversities')"
      :placeholder="t('company.offers.form.preferredUniversitiesPlaceholder')"
      :empty-state-label="t('company.offers.form.noMoreOptions')"
    />

    <BaseTextarea
      id="description"
      v-model="form.description"
      :label="t('company.offers.form.description')"
      required
      :maxlength="10000"
      :rows="6"
      :error="fieldError('description')"
    />

    <div class="flex flex-col gap-4 rounded-lg border border-border p-4">
      <BaseToggle id="is_paid" v-model="form.is_paid" :label="t('company.offers.form.isPaid')" />

      <div v-if="form.is_paid" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <BaseInput
          id="salary_min"
          v-model="form.salary_min"
          type="number"
          :label="t('company.offers.form.salaryMin')"
          required
          :error="fieldError('salary_min')"
        />
        <BaseInput
          id="salary_max"
          v-model="form.salary_max"
          type="number"
          :label="t('company.offers.form.salaryMax')"
          required
          :error="fieldError('salary_max')"
        />
      </div>
    </div>

    <div class="flex flex-col gap-2 rounded-lg border border-border p-4">
      <BaseToggle id="status" v-model="isPublished" :label="isPublished ? t('company.offers.form.published') : t('company.offers.form.draft')" />
      <p class="text-sm text-additional">
        {{ t('company.offers.form.draftHint') }}
      </p>
      <p v-if="fieldError('status')" class="text-sm text-error" role="alert">
        {{ fieldError('status') }}
      </p>
    </div>

    <BaseButton type="submit" class="w-fit px-8" :disabled="form.processing">
      {{ isEditing ? t('company.offers.form.submitEdit') : t('company.offers.form.submitCreate') }}
    </BaseButton>
  </form>
</template>
