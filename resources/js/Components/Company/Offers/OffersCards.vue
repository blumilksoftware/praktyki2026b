<script setup>
import { useI18n } from 'vue-i18n'
import { ROUTES } from '@/Helpers/routes'
import OfferActionsMenu from './OfferActionsMenu.vue'

const { t } = useI18n()

defineProps({
  offers: {
    type: Array,
    required: true,
  },
  openMenuId: {
    type: [String, Number, null],
    default: null,
  },
})

const emit = defineEmits(['toggle-menu', 'edit', 'toggle-status', 'delete', 'go-to-applications', 'go-to-offer'])

const statusClasses = {
  published: 'bg-green-100 text-green-700',
  draft: 'bg-gray-100 text-gray-700',
  closed: 'bg-red-100 text-red-700',
  expired: 'bg-orange-100 text-orange-700',
}

const applicationsHref = (offerId) => `${ROUTES.COMPANY_APPLICATIONS}?offer=${offerId}`
const offerHref = (offerId) => ROUTES.OFFER_SHOW.replace('{offer}', offerId)
</script>

<template>
  <div class="md:hidden divide-y divide-border">
    <div
      v-for="offer in offers"
      :key="offer.id"
      class="p-4 space-y-3"
    >
      <div class="flex items-start justify-between gap-2">
        <div class="min-w-0">
          <p class="text-text font-medium truncate">
            <a
              :href="offerHref(offer.id)"
              class="text-primary hover:underline"
              @click="emit('go-to-offer', $event, offer.id)"
            >
              {{ offer.title }}
            </a>
          </p>
          <span
            class="mt-1 inline-block px-2 py-0.5 rounded-full text-xs font-medium"
            :class="statusClasses[offer.status] ?? 'bg-gray-100 text-gray-700'"
          >
            {{ t(`company.dashboard.offers.status.${offer.status}`) }}
          </span>
        </div>

        <OfferActionsMenu
          class="shrink-0"
          :offer="offer"
          :is-open="openMenuId === offer.id"
          @toggle="emit('toggle-menu', $event)"
          @edit="emit('edit', $event)"
          @toggle-status="emit('toggle-status', $event)"
          @delete="emit('delete', $event)"
        />
      </div>

      <div class="flex items-center gap-4 text-sm">
        <div class="flex flex-col">
          <span class="text-xs text-additional">
            {{ t('company.dashboard.offers.table.spots') }}
          </span>
          <span class="text-text">{{ offer.spots }}</span>
        </div>

        <div class="flex flex-col">
          <span class="text-xs text-additional">
            {{ t('company.dashboard.offers.table.applications') }}
          </span>
          <a
            :href="applicationsHref(offer.id)"
            class="text-primary hover:underline"
            @click="emit('go-to-applications', $event, offer.id)"
          >
            {{ offer.applications_count }}
          </a>
        </div>
      </div>
    </div>
  </div>
</template>
