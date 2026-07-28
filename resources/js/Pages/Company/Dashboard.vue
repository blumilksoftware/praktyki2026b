<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import {
  IconHome,
  IconUser,
  IconDotsVertical,
  IconPencil,
  IconPlayerPause,
  IconPlayerPlay,
  IconTrash,
} from '@tabler/icons-vue'

import BaseLayout from '@/Components/Layouts/BaseLayout.vue'
import { ROUTES } from '@/Helpers/routes'

const { t } = useI18n()

const props = defineProps({
  offers: {
    type: Array,
    required: true,
  },
})

const navItems = [
  {
    key: 'dashboard',
    label: 'Dashboard',
    href: '/company/dashboard',
    icon: IconHome,
  },
  {
    key: 'profile',
    label: 'Profile',
    href: '/company/profile',
    icon: IconUser,
  },
]

const statusClasses = {
  published: 'bg-green-100 text-green-700',
  draft: 'bg-gray-100 text-gray-700',
  closed: 'bg-red-100 text-red-700',
  expired: 'bg-orange-100 text-orange-700',
}

const applicationsHref = (offerId) => `${ROUTES.COMPANY_APPLICATIONS}?offer=${offerId}`

function goToApplications(event, offerId) {
  event.preventDefault()
  router.visit(applicationsHref(offerId))
}

const openMenuId = ref(null)

function toggleMenu(offerId) {
  openMenuId.value =
    openMenuId.value === offerId ? null : offerId
}

function closeMenu() {
  openMenuId.value = null
}

function handleClickOutside(event) {
  if (!event.target.closest('[data-offer-menu]')) {
    closeMenu()
  }
}


onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})


onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})


function editOffer(offer) {
  router.visit(`${ROUTES.COMPANY_OFFERS_STORE}/${offer.id}/edit`)
  closeMenu()
}

function publishOffer(offer) {
  router.patch(`${ROUTES.COMPANY_OFFERS_STORE}/${offer.id}/publish`)
  closeMenu()
}

function deactivateOffer(offer) {
  router.patch(`${ROUTES.COMPANY_OFFERS_STORE}/${offer.id}/deactivate`)
  closeMenu()
}

function deleteOffer(offer) {
  closeMenu()
  if (!confirm(t('company.dashboard.offers.confirmDelete'))) {
    return
  }
  router.delete(`${ROUTES.COMPANY_OFFERS_STORE}/${offer.id}`)
}
</script>

<template>
  <Head :title="t('company.dashboard.offers.title')" />
  <BaseLayout
    active-page="offers"
    :nav-items="navItems"
  >
    <div class="p-4 sm:p-6 space-y-6">
      <header class="mb-6">
        <h1 class="font-semibold text-text text-xl sm:text-2xl">
          {{ t('company.dashboard.offers.title') }}
        </h1>
        <p class="mt-1 text-additional text-sm">
          {{ t('company.dashboard.offers.subtitle') }}
        </p>
      </header>

      <div class="rounded-xl border border-border bg-white shadow-sm overflow-visible">
        <div class="px-4 py-3 border-b border-border">
          <h2 class="font-medium text-text">
            {{ t('company.dashboard.offers.title') }}
          </h2>
        </div>

        <div v-if="offers.length === 0" class="px-4 py-6 text-center text-additional text-sm">
          {{ t('company.dashboard.offers.noOffers') }}
        </div>

        <!-- Desktop table -->
        <div v-else class="hidden md:block overflow-visible">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 text-additional">
              <tr>
                <th class="px-4 py-3 text-left font-medium">
                  {{ t('company.dashboard.offers.table.offer') }}
                </th>
                <th class="px-4 py-3 text-center font-medium">
                  {{ t('company.dashboard.offers.table.status') }}
                </th>
                <th class="px-4 py-3 text-center font-medium">
                  {{ t('company.dashboard.offers.table.spots') }}
                </th>
                <th class="px-4 py-3 text-center font-medium">
                  {{ t('company.dashboard.offers.table.applications') }}
                </th>
                <th class="px-4 py-3 text-right font-medium">
                  {{ t('company.dashboard.offers.table.actions') }}
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
                  {{ offer.title }}
                </td>
                <td class="px-4 py-3 text-center">
                  <span
                    class="px-2 py-1 rounded-full text-xs font-medium"
                    :class="statusClasses[offer.status]?? 'bg-gray-100 text-gray-700'"
                  >
                    {{ offer.status }}
                  </span>
                </td>
                <td class="px-4 py-3 text-center text-text">
                  {{ offer.spots }}
                </td>
                <td class="px-4 py-3 text-center">
                  <a
                    :href="applicationsHref(offer.id)"
                    class="text-primary hover:underline"
                    @click="goToApplications($event, offer.id)"
                  >
                    {{ offer.applications_count }}
                  </a>
                </td>
                <td class="px-4 py-3 text-right">
                  <div
                    class="relative inline-block text-left"
                    data-offer-menu
                  >
                    <button
                      type="button"
                      class="p-1.5 rounded-md text-additional hover:bg-gray-100 cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
                      :aria-label="t('company.dashboard.offers.actions.menu')"
                      @click="toggleMenu(offer.id)"
                    >
                      <IconDotsVertical class="w-4 h-4" />
                    </button>

                    <div v-if="openMenuId === offer.id" class="absolute right-0 z-50 mt-1 w-40 rounded-lg border border-border bg-white shadow-lg py-1">
                      <button
                        type="button"
                        class="flex items-center gap-2 w-full px-3 py-2 text-left text-text hover:bg-gray-50 cursor-pointer"
                        @click="editOffer(offer)"
                      >
                        <IconPencil class="w-4 h-4" />
                        {{ t('company.dashboard.offers.actions.edit') }}
                      </button>

                      <button
                        type="button"
                        class="flex items-center gap-2 w-full px-3 py-2 text-left text-text hover:bg-gray-50 cursor-pointer"
                        @click="offer.status === 'published' ? deactivateOffer(offer) : publishOffer(offer)"
                      >
                        <IconPlayerPause v-if="offer.status === 'published'" class="w-4 h-4" />
                        <IconPlayerPlay v-else class="w-4 h-4" />

                        {{
                          offer.status === 'published'
                            ? t('company.dashboard.offers.actions.deactivate')
                            : t('company.dashboard.offers.actions.activate')
                        }}
                      </button>

                      <button
                        type="button"
                        class="flex items-center gap-2 w-full px-3 py-2 text-left text-red-600 hover:bg-red-50 cursor-pointer"
                        @click="deleteOffer(offer)"
                      >
                        <IconTrash class="w-4 h-4" />
                        {{ t('company.dashboard.offers.actions.delete') }}
                      </button>
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Mobile card list -->
        <div v-if="offers.length > 0" class="md:hidden divide-y divide-border">
          <div
            v-for="offer in offers"
            :key="offer.id"
            class="p-4 space-y-3"
          >
            <div class="flex items-start justify-between gap-2">
              <div class="font-medium text-text break-words pr-2">
                {{ offer.title }}
              </div>

              <div
                class="relative shrink-0"
                data-offer-menu
              >
                <button
                  type="button"
                  class="p-1.5 rounded-md text-additional hover:bg-gray-100 cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
                  :aria-label="
                    t('company.dashboard.offers.actions.menu')
                  "
                  @click="toggleMenu(offer.id)"
                >
                  <IconDotsVertical class="w-4 h-4" />
                </button>

                <div v-if="openMenuId === offer.id" class="absolute right-0 z-50 mt-1 w-44 rounded-lg border border-border bg-white shadow-lg py-1">
                  <button
                    type="button"
                    class="flex items-center gap-2 w-full px-3 py-2 text-left text-text hover:bg-gray-50 cursor-pointer"
                    @click="editOffer(offer)"
                  >
                    <IconPencil class="w-4 h-4" />
                    {{ t('company.dashboard.offers.actions.edit') }}
                  </button>

                  <button
                    type="button"
                    class="flex items-center gap-2 w-full px-3 py-2 text-left text-text hover:bg-gray-50 cursor-pointer"
                    @click="offer.status === 'published' ? deactivateOffer(offer) : publishOffer(offer)"
                  >
                    <IconPlayerPause v-if="offer.status === 'published'" class="w-4 h-4" />
                    <IconPlayerPlay v-else class="w-4 h-4" />

                    {{
                      offer.status === 'published'
                        ? t('company.dashboard.offers.actions.deactivate')
                        : t('company.dashboard.offers.actions.activate')
                    }}
                  </button>

                  <button
                    type="button"
                    class="flex items-center gap-2 w-full px-3 py-2 text-left text-red-600 hover:bg-red-50 cursor-pointer"
                    @click="deleteOffer(offer)"
                  >
                    <IconTrash class="w-4 h-4" />
                    {{ t('company.dashboard.offers.actions.delete') }}
                  </button>
                </div>
              </div>
            </div>

            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm">
              <span
                class="px-2 py-1 rounded-full text-xs font-medium"
                :class="statusClasses[offer.status] ?? 'bg-gray-100 text-gray-700'"
              >
                {{ offer.status }}
              </span>

              <span class="text-additional">
                {{ t('company.dashboard.offers.table.spots') }}:
                <span class="text-text font-medium">{{ offer.spots }}</span>
              </span>

              <a
                :href="applicationsHref(offer.id)"
                class="text-primary hover:underline"
                @click="goToApplications($event, offer.id)"
              >
                {{ t('company.dashboard.offers.table.applications') }}: {{ offer.applications_count }}
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </BaseLayout>
</template>
