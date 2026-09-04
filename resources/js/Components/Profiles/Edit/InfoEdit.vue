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
    <h2 class="mb-4 font-bold text-text text-xl">
      {{ t('profiles.university.systemInformation') }}
    </h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="flex flex-col gap-1">
        <BaseInput
          id="domain"
          v-model="domainModel"
          :label="t('profiles.university.domain')"
          :disabled="isDomainLocked"
          :error="errors.domain"
        />
        <span v-if="isDomainLocked" class="text-xs text-additional font-medium px-1">
          {{ t('profiles.university.domainLockedHint') }}
        </span>
      </div>

      <div class="flex flex-col gap-1">
        <BaseInput
          id="external_form_url"
          v-model="externalFormUrlModel"
          :label="t('profiles.university.externalFormUrlPlaceholder')"
          :error="errors.external_form_url"
        />
      </div>
    </div>
  </div>
</template>
