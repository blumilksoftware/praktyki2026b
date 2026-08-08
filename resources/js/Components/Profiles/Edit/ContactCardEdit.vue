<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { IconWorld, IconMapPin, IconPhone, IconId } from '@tabler/icons-vue'
import BaseInput from '@/Components/Base/BaseInput.vue' 

const { t } = useI18n()

const props = defineProps({
  website: { type: String, default: '' },
  phone: { type: String, default: '' },
  street: { type: String, default: '' },
  postalCode: { type: String, default: '' },
  city: { type: String, default: '' },
  nip: { type: String, default: null },
  errors: { type: Object, default: () => ({}) },
})

const emit = defineEmits([
  'update:website', 
  'update:phone',
  'update:street',
  'update:postalCode',
  'update:city',
])

const requiredFields = ['city', 'postalCode', 'street', 'phone']

const getTranslatedError = (field) => {
  if (props.errors && props.errors[field]) {
    return props.errors[field] 
  }
  
  if (requiredFields.includes(field)) {
    const value = props[field]
    if (value === null || value === undefined || String(value).trim() === '') {
      return t('validation.requiredField')
    }
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
</script>

<template>
  <div class="flex flex-col gap-5">
    <h2 class="text-xl font-bold text-text">{{ t('profiles.contact') }}</h2>
    
    <div class="p-6 sm:p-8 bg-white flex flex-col gap-2">
      <div class="flex flex-col sm:flex-row items-start gap-2 sm:gap-6">
        <IconWorld stroke="1.5" class="hidden sm:block w-7 h-7 text-black shrink-0 mt-9" />
        <BaseInput
          id="website"
          v-model="websiteModel"
          :label="t('common.fields.website')"
          :error="getTranslatedError('website')"
        />
      </div>
      
      <div class="flex flex-col sm:flex-row items-start gap-2 sm:gap-6">
        <IconMapPin class="hidden sm:block w-7 h-7 text-black shrink-0 mt-9" />
        <div class="w-full flex flex-col sm:flex-row gap-3">
          <div class="w-full sm:w-2/3">
            <BaseInput
              id="city"
              v-model="cityModel"
              :label="t('common.fields.city')"
              :error="getTranslatedError('city')"
              required
            />
          </div>
          <div class="w-full sm:w-1/3">
            <BaseInput
              id="postalCode"
              v-model="postalCodeModel"
              :label="t('common.fields.postalCode')"
              :error="getTranslatedError('postalCode')"
              required
            />
          </div>
        </div>
      </div>

      <div class="flex flex-col sm:flex-row items-start gap-2 sm:gap-6">
        <div class="hidden sm:block w-7 h-7 shrink-0" />
        <BaseInput
          id="street"
          v-model="streetModel"
          :label="t('common.fields.street')"
          :error="getTranslatedError('street')"
          required
        />
      </div>

      <div class="flex flex-col sm:flex-row items-start gap-2 sm:gap-6">
        <IconPhone class="hidden sm:block w-7 h-7 text-black shrink-0 mt-9" />
        <BaseInput
          id="phone"
          v-model="phoneModel"
          :label="t('common.fields.phone')"
          :error="getTranslatedError('phone')"
          required
        />
      </div>

      <div v-if="nip" class="flex items-center gap-4 sm:gap-6">
        <IconId class="hidden sm:block w-7 h-7 text-black shrink-0" />
        <span class="font-bold text-text text-sm">{{ t('common.fields.nip') }}:</span>
        <span class="text-gray-700 font-medium wrap-break-word">{{ nip }}</span>
      </div>
    </div>
  </div>
</template>
