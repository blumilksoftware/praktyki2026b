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
  sortIcon: {
    type: Function,
    required: true,
  },
  openMenuId: {
    type: [String, Number, null],
    default: null,
  },
})

const emit = defineEmits(['sort', 'toggle-menu', 'edit', 'toggle-status', 'delete', 'go-to-applications', 'go-to-offer'])

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
  <div class="hidden md:block overflow-visible">
    <table class="w-full text-sm">
      <thead class="bg-gray-50 text-additional">
        <tr>
          <th class="px-4 py-3 font-medium text-left select-none">
            <button
              type="button"
              class="inline-flex items-center gap-1 cursor-pointer hover:text-text"
              @click="emit('sort', 'title')"
            >
              {{ t('common.fields.offer') }}
              <component :is="sortIcon('title')" class="w-3.5 h-3.5" />
            </button>
          </th>

          <th class="px-4 py-3 text-center font-medium">
            {{ t('common.fields.status') }}
          </th>

          <th class="px-4 py-3 text-center font-medium select-none">
            <button
              type="button"
              class="inline-flex items-center gap-1 justify-center cursor-pointer hover:text-text"
              @click="emit('sort', 'spots')"
            >
              {{ t('common.fields.spots') }}
              <component :is="sortIcon('spots')" class="w-3.5 h-3.5" />
            </button>
          </th>

          <th class="px-4 py-3 text-center font-medium select-none">
            <button
              type="button"
              class="inline-flex items-center gap-1 justify-center cursor-pointer hover:text-text"
              @click="emit('sort', 'applications_count')"
            >
              {{ t('common.words.applications') }}
              <component :is="sortIcon('applications_count')" class="w-3.5 h-3.5" />
            </button>
          </th>

          <th class="px-4 py-3 text-right font-medium">
            {{ t('common.fields.actions') }}
          </th>
        </tr>
      </thead>
      <tbody class="divide-y divide-border">
        <tr
          v-for="offer in offers"
          :key="offer.id"
          class="hover:bg-gray-50"
        >
          <td class="px-4 py-3 text-text">
            <a
              :href="offerHref(offer.id)"
              class="text-primary hover:underline"
              @click="emit('go-to-offer', $event, offer.id)"
            >
              {{ offer.title }}
            </a>
          </td>
          <td class="px-4 py-3 text-center">
            <span
              class="px-2 py-1 rounded-full text-xs font-medium"
              :class="statusClasses[offer.status] ?? 'bg-gray-100 text-gray-700'"
            >
              {{ t(`company.dashboard.offers.status.${offer.status}`) }}
            </span>
          </td>
          <td class="px-4 py-3 text-center text-text">
            {{ offer.spots }}
          </td>
          <td class="px-4 py-3 text-center">
            <a
              :href="applicationsHref(offer.id)"
              class="text-primary hover:underline"
              @click="emit('go-to-applications', $event, offer.id)"
            >
              {{ offer.applications_count }}
            </a>
          </td>
          <td class="px-4 py-3 text-right">
            <OfferActionsMenu
              :offer="offer"
              :is-open="openMenuId === offer.id"
              @toggle="emit('toggle-menu', $event)"
              @edit="emit('edit', $event)"
              @toggle-status="emit('toggle-status', $event)"
              @delete="emit('delete', $event)"
            />
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
