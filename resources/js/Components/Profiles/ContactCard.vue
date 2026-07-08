<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { IconWorld, IconMapPin, IconPhone } from '@tabler/icons-vue'

const { t } = useI18n()

const props = defineProps({
  email: { type: String, default: null },
  phone: { type: String, default: null },
  website: { type: String, default: null },
  street: { type: String, default: null },
  buildingNumber: { type: String, default: null },
  postalCode: { type: String, default: null },
  city: { type: String, default: null },
  nip: { type: String, default: null },
})

const fullAddress = computed(() => {
  const parts = []
  
  if (props.street) {
    let streetPart = props.street
    if (props.buildingNumber) streetPart += ` ${props.buildingNumber}`
    parts.push(streetPart)
  }
  
  let cityPart = ''
  if (props.postalCode) cityPart += `${props.postalCode} `
  if (props.city) cityPart += props.city
  
  if (cityPart.trim()) parts.push(cityPart.trim())
  
  return parts.join(', ')
})

const googleMapsUrl = computed(() => {
  if (!fullAddress.value) return '#'
  return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(fullAddress.value)}`
})
</script>

<template>
  <div class="flex flex-col gap-5">
    <h2 class="text-xl font-bold text-text">{{ t('profiles.contact') }}</h2>
    
    <div class="border border-gray-200 rounded-4xl p-6 sm:p-8 bg-white flex flex-col gap-6">
      <div v-if="website" class="flex items-center gap-4 sm:gap-6">
        <IconWorld stroke="2.5" class="w-7 h-7 text-black shrink-0" />
        <a 
          :href="website" 
          target="_blank" 
          rel="noopener noreferrer" 
          class="border border-gray-200 rounded-2xl px-5 py-2.5 text-sm font-medium text-gray-800 hover:border-primary hover:text-primary transition-colors wrap-break-word w-full max-w-65"
        >
          {{ website.replace(/^https?:\/\//, '') }}
        </a>
      </div>
      
      <div v-if="fullAddress" class="flex items-center gap-4 sm:gap-6">
        <IconMapPin stroke="2.5" class="w-7 h-7 text-black shrink-0" />
        <a 
          :href="googleMapsUrl"
          target="_blank"
          rel="noopener noreferrer"
          class="flex items-center border border-gray-200 rounded-2xl px-5 py-2.5 text-sm font-medium text-gray-600 hover:border-primary hover:text-primary transition-colors wrap-break-word w-full max-w-65"
        >
          <span>{{ fullAddress }}</span>
        </a>
      </div>

      <div v-if="phone" class="flex items-center gap-4 sm:gap-6">
        <IconPhone stroke="2.5" class="w-7 h-7 text-black shrink-0" />
        <a 
          :href="`tel:${phone}`" 
          class="border border-gray-200 rounded-2xl px-5 py-2.5 text-sm font-medium text-gray-600 hover:border-primary hover:text-primary transition-colors wrap-break-word w-full max-w-65"
        >
          {{ phone }}
        </a>
      </div>

      <div v-if="nip" class="flex items-center gap-4 sm:gap-6">
        <span class="font-bold text-text text-sm shrink-0 w-7 flex justify-center">
          {{ t('profiles.nip') }}:
        </span>
        <div class="px-5 py-2.5 text-sm font-medium text-gray-600 wrap-break-word w-full max-w-65">
          {{ nip }}
        </div>
      </div>

      <div v-if="!fullAddress && !phone && !website && !nip" class="text-gray-400 italic text-sm text-center py-4">
        {{ t('profiles.noContactInfo') }}
      </div>
    </div>
  </div>
</template>
