<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import VerifiedBadge from '@/Components/Common/VerifiedBadge.vue'
import { offerShow } from '@/Helpers/routes'

const props = defineProps({
  offer: { type: Object, required: true },
})

const { t } = useI18n()

const companyInitial = computed(() => props.offer.company?.name?.charAt(0) ?? 'O')
const workModeLabel = computed(() => t(`student.workModes.${props.offer.work_mode}`))
const detailHref = computed(() => offerShow(props.offer.id))
</script>

<template>
  <Link
    :href="detailHref"
    class="block rounded-2xl border border-border bg-white p-4 transition hover:border-primary/40 hover:bg-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30"
    :aria-label="t('offers.detail.similarOfferAria', { title: offer.title, company: offer.company.name })"
  >
    <div class="flex items-start gap-3">
      <div
        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-sm font-semibold text-primary"
        aria-hidden="true"
      >
        {{ companyInitial }}
      </div>
      <div class="min-w-0 flex-1">
        <div class="flex flex-wrap items-center gap-1.5">
          <p class="truncate text-xs font-semibold text-additional">
            {{ offer.company.name }}
          </p>
          <VerifiedBadge :verified="Boolean(offer.company.is_verified)" />
        </div>
        <p class="mt-1 line-clamp-2 text-sm font-semibold text-text">
          {{ offer.title }}
        </p>
        <div class="mt-2 flex flex-wrap gap-1.5">
          <span class="inline-flex items-center rounded-full border border-primary/20 bg-primary/10 px-2 py-0.5 text-xs font-medium text-primary">
            {{ offer.city }}
          </span>
          <span class="inline-flex items-center rounded-full border border-primary/20 bg-primary/10 px-2 py-0.5 text-xs font-medium text-primary">
            {{ workModeLabel }}
          </span>
        </div>
      </div>
    </div>
  </Link>
</template>
