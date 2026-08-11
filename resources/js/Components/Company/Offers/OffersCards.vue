<script setup>
import { Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { IconUsers, IconClipboardText } from '@tabler/icons-vue'
import { ROUTES } from '@/Helpers/routes'
import OfferActionsMenu from './OfferActionsMenu.vue'

const { t } = useI18n()

const props = defineProps({
  offers: {
    type: Array,
    required: true,
  },
  openMenuId: {
    type: [String, Number, null],
    default: null,
  },
  hiddenOnMd: {
    type: Boolean,
    default: true,
  },
  isCompanyVerified: {
    type: Boolean,
    default: true,
  },
  showVerificationHint: {
    type: Boolean,
    default: false,
  },
  verificationHintKey: {
    type: String,
    default: '',
  },
  labels: {
    type: Object,
    default: () => ({
      menu: 'company.dashboard.offers.actions.menu',
      edit: 'company.dashboard.offers.actions.edit',
      activate: 'company.dashboard.offers.actions.activate',
      deactivate: 'company.dashboard.offers.actions.deactivate',
      delete: 'company.dashboard.offers.actions.delete',
    }),
  },
  statusKeyPrefix: {
    type: String,
    default: 'company.dashboard.offers.status',
  },
})

const emit = defineEmits(['toggle-menu', 'edit', 'toggle-status', 'delete', 'go-to-applications'])

const statusClasses = {
  draft: 'bg-slate-100 text-slate-600',
  published: 'bg-green-100 text-green-700',
  closed: 'bg-slate-200 text-slate-500',
  expired: 'bg-slate-200 text-slate-500',
}

const titleHref = (offerId) => props.isCompanyVerified
  ? `${ROUTES.COMPANY_APPLICATIONS}?offer=${offerId}`
  : ROUTES.COMPANY_OFFERS_EDIT(offerId)
</script>

<template>
  <ul :class="[props.hiddenOnMd ? 'md:hidden' : '', 'flex flex-col gap-3']">
    <li
      v-for="offer in props.offers"
      :key="offer.id"
      class="relative rounded-2xl border border-border bg-white p-4 pr-12 shadow-sm sm:p-5 sm:pr-14"
    >
      <div class="flex items-start gap-3">
        <div class="min-w-0">
          <div class="flex flex-wrap items-center gap-2">
            <span
              class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold tracking-wide"
              :class="statusClasses[offer.status] ?? 'bg-gray-100 text-gray-700'"
            >
              {{ t(`${props.statusKeyPrefix}.${offer.status}`) }}
            </span>
            <h2 class="min-w-0 truncate font-semibold text-text text-base">
              <Link
                :href="titleHref(offer.id)"
                class="block truncate transition-colors hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 rounded"
                @click="emit('go-to-applications', $event, offer.id)"
              >
                {{ offer.title }}
              </Link>
            </h2>
          </div>
        </div>

        <div class="absolute right-4 inset-y-0 flex items-center">
          <OfferActionsMenu
            class="shrink-0"
            :offer="offer"
            :is-open="props.openMenuId === offer.id"
            :show-status-action="offer.status === 'published' || (offer.status === 'draft' && props.isCompanyVerified)"
            :labels="props.labels"
            @toggle="emit('toggle-menu', $event)"
            @edit="emit('edit', $event)"
            @toggle-status="emit('toggle-status', $event)"
            @delete="emit('delete', $event)"
          />
        </div>
      </div>
      <p
        v-if="props.showVerificationHint && offer.status === 'draft' && !props.isCompanyVerified && props.verificationHintKey"
        class="mt-1 text-amber-600 text-xs"
      >
        {{ t(props.verificationHintKey) }}
      </p>

      <div class="mt-2 flex flex-wrap items-center gap-3 text-additional text-sm">
        <span class="inline-flex items-center gap-1">
          <IconUsers class="h-4 w-4" aria-hidden="true" />
          {{ t('company.offers.index.spotsLabel', { count: offer.remaining_spots ?? offer.spots }) }}
        </span>

        <span class="inline-flex items-center gap-1">
          <IconClipboardText class="h-4 w-4" aria-hidden="true" />
          <span class="text-additional font-normal">
            {{ t('company.offers.index.applicationsCount', { count: offer.applications_count }) }}
          </span>
        </span>
      </div>
    </li>
  </ul>
</template>
