<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { IconWorld, IconMapPin, IconPhone } from '@tabler/icons-vue'
import BaseInput from '@/Components/Base/BaseInput.vue' 

const { t } = useI18n()

const props = defineProps({
  website: { type: String, default: '' },
  phone: { type: String, default: '' },
  street: { type: String, default: '' },
  buildingNumber: { type: String, default: '' },
  postalCode: { type: String, default: '' },
  city: { type: String, default: '' },
  nip: { type: String, default: '' },
  errors: { type: Object, default: () => ({}) }, 
})

const emit = defineEmits([
  'update:website', 
  'update:phone', 
  'update:street', 
  'update:buildingNumber', 
  'update:postalCode', 
  'update:city', 
  'update:nip',
])

const requiredFields = ['city', 'postalCode', 'street', 'buildingNumber', 'phone']

const getTranslatedError = (field) => {
  if (props.errors[field]) {
    return t(props.errors[field])
  }
  if (requiredFields.includes(field) && props[field].trim() === '') {
    return t('validation.requiredField')
  }
  return undefined
}

const websiteModel = computed({
  get: () => props.website,
  set: (val) => emit('update:website', val),
})
const phoneModel = computed({
  get: () => props.phone,
  set: (val) => emit('update:phone', val),
})
const cityModel = computed({
  get: () => props.city,
  set: (val) => emit('update:city', val),
})
const postalCodeModel = computed({
  get: () => props.postalCode,
  set: (val) => emit('update:postalCode', val),
})
const streetModel = computed({
  get: () => props.street,
  set: (val) => emit('update:street', val),
})
const buildingNumberModel = computed({
  get: () => props.buildingNumber,
  set: (val) => emit('update:buildingNumber', val),
})
</script>

<template>
  <div class="flex flex-col gap-5">
    <h2 class="text-xl font-bold text-text">{{ t('profiles.contact') }}</h2>
    
    <div class="border border-gray-200 rounded-4xl p-6 sm:p-8 bg-white flex flex-col gap-2">
      <div class="flex flex-col sm:flex-row items-start gap-2 sm:gap-6">
        <IconWorld stroke="2.5" class="hidden sm:block w-7 h-7 text-black shrink-0 mt-9" />
        <BaseInput
          id="website"
          v-model="websiteModel"
          :label="t('auth.register.company.website')"
          :error="getTranslatedError('website')"
        />
      </div>
      
      <div class="flex flex-col sm:flex-row items-start gap-2 sm:gap-6">
        <IconMapPin stroke="2.5" class="hidden sm:block w-7 h-7 text-black shrink-0 mt-9" />
        <div class="w-full flex flex-col sm:flex-row gap-3">
          <div class="w-full sm:w-2/3">
            <BaseInput
              id="city"
              v-model="cityModel"
              :label="t('auth.register.company.city')"
              :error="getTranslatedError('city')"
              required
            />
          </div>
          <div class="w-full sm:w-1/3">
            <BaseInput
              id="postalCode"
              v-model="postalCodeModel"
              :label="t('auth.register.company.postalCode')"
              :error="getTranslatedError('postalCode')"
              required
            />
          </div>
        </div>
      </div>

      <div class="flex flex-col sm:flex-row items-start gap-2 sm:gap-6">
        <div class="hidden sm:block w-7 h-7 shrink-0" /> 
        <div class="w-full flex flex-col sm:flex-row gap-3">
          <div class="w-full sm:w-3/4">
            <BaseInput
              id="street"
              v-model="streetModel"
              :label="t('auth.register.company.street')"
              :error="getTranslatedError('street')"
              required
            />
          </div>
          <div class="w-full sm:w-1/4">
            <BaseInput
              id="buildingNumber"
              v-model="buildingNumberModel"
              :label="t('auth.register.company.buildingNumber')"
              :error="getTranslatedError('buildingNumber')"
              required
            />
          </div>
        </div>
      </div>

      <div class="flex flex-col sm:flex-row items-start gap-2 sm:gap-6">
        <IconPhone stroke="2.5" class="hidden sm:block w-7 h-7 text-black shrink-0 mt-9" />
        <BaseInput
          id="phone"
          v-model="phoneModel"
          :label="t('auth.register.company.phone')"
          :error="getTranslatedError('phone')"
          required
        />
      </div>

      <div class="flex flex-col sm:flex-row items-center gap-2 sm:gap-6 mt-4">
        <span class="font-bold text-text text-sm shrink-0 sm:w-7 flex sm:justify-center sm:flex">
          {{ t('auth.register.company.nip') }}:
        </span>
        <span class="text-gray-800">{{ nip }}</span>
      </div>
    </div>
  </div>
</template>
