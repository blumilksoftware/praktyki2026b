<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { IconBriefcase2Filled } from '@tabler/icons-vue'

const { t } = useI18n()

const props = defineProps({
  offers: { type: Array, default: () => [] },
})

const visibleOffers = computed(() => {
  return props.offers.slice(0, 4)
})
</script>

<template>
  <div class="flex flex-col gap-6">
    <h2 class="text-xl font-bold text-text">{{ t('profiles.currentOffers') }}</h2>

    <div v-if="offers && offers.length > 0" class="flex flex-col gap-4">
      <div 
        v-for="offer in visibleOffers" 
        :key="offer.id"
        class="border border-border rounded-2xl p-5 bg-white shadow-sm flex items-start gap-4 sm:gap-5 w-full"
      >
        <div class="flex items-center justify-center bg-gray-50 border border-gray-100 rounded-xl p-3 shrink-0 text-gray-700">
          <IconBriefcase2Filled class="w-6 h-6" />
        </div>

        <div class="flex flex-col pt-0.5">
          <h3 class="font-semibold text-lg text-text">
            {{ offer.title }}
          </h3>
          <p class="text-gray-500 text-sm mt-1.5 line-clamp-2">
            {{ offer.description }}
          </p>
        </div>
      </div>
    </div>

    <div v-else class="text-gray-400 italic text-sm py-2">
      {{ t('profiles.noOffers') }}
    </div>
  </div>
</template>
