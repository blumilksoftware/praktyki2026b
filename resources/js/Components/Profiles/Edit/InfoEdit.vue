<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import BaseInput from '@/Components/Base/BaseInput.vue'

const { t } = useI18n()

const props = defineProps({
  domain: { type: String, default: '' },
  externalFormUrl: { type: String, default: '' },
  isDomainLocked: { type: Boolean, default: false },
  errors: { type: Object, default: () => ({}) },
})

const emit = defineEmits([
  'update:domain',
  'update:externalFormUrl',
])

const domainModel = computed({
  get: () => props.domain,
  set: (val) => emit('update:domain', val),
})

const externalFormUrlModel = computed({
  get: () => props.externalFormUrl,
  set: (val) => emit('update:externalFormUrl', val),
})
</script>

<template>
  <div class="bg-white rounded-xl border border-secondary/20 shadow-sm p-6 sm:p-8">
    <h3 class="font-semibold text-gray-800 mb-4 text-lg">
      {{ t('university.profile.systemInformation') }}
    </h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="flex flex-col gap-1">
        <BaseInput
          id="domain"
          v-model="domainModel"
          :label="t('common.fields.domain')"
          :disabled="isDomainLocked"
          :error="errors.domain"
        />
        <span v-if="isDomainLocked" class="text-xs text-gray-500 font-medium px-1">
          {{ t('university.profile.domainLockedHint') }}
        </span>
      </div>

      <div class="flex flex-col gap-1">
        <BaseInput
          id="external_form_url"
          v-model="externalFormUrlModel"
          :label="t('university.profile.externalFormUrlPlaceholder')"
          :error="errors.external_form_url"
        />
      </div>
    </div>
  </div>
</template>
