<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { IconHome, IconUser, IconDotsVertical, IconPencil, IconPlayerPause, IconPlayerPlay, IconTrash } from '@tabler/icons-vue'
import BaseLayout from '@/Components/Layouts/BaseLayout.vue'
import { ROUTES } from '@/Helpers/routes'
import BaseButton from "@/Components/Base/BaseButton.vue";

const { t } = useI18n()

const props = defineProps({
  offers: {
    type: Array,
    required: true,
  },
})

const navItems = [
  { key: 'dashboard', label: 'Dashboard', href: '/company/dashboard', icon: IconHome },
  { key: 'profile', label: 'Profile', href: '/company/profile', icon: IconUser },
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
  openMenuId.value = openMenuId.value === offerId ? null : offerId
}

function closeMenu() {
  openMenuId.value = null
}

function handleClickOutside(event) {
  if (!event.target.closest('[data-offer-menu]')) {
    closeMenu()
  }
}

onMounted(() => document.addEventListener('click', handleClickOutside))
onUnmounted(() => document.removeEventListener('click', handleClickOutside))

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
  if (!confirm(t('company.dashboard.offers.confirmDelete'))) return
  router.delete(`${ROUTES.COMPANY_OFFERS_STORE}/${offer.id}`)
}
</script>

<template>
  <Head :title="t('company.dashboard.offers.title')" />
  <BaseLayout active-page="offers" :nav-items="navItems">
    <div class="p-6 space-y-6">
      <div class="rounded-xl border border-gray-200 overflow-visible">
        <div class="px-4 py-3 border-b border-gray-100">
          <h2 class="font-medium text-gray-900">{{ t('company.dashboard.offers.title') }}</h2>
        </div>

        <table class="w-full text-sm">
          <thead class="bg-gray-50 text-gray-500 text-left">
          <tr>
            <th class="px-4 py-2 font-medium">{{ t('company.dashboard.offers.table.offer') }}</th>
            <th class="px-4 py-2 font-medium">{{ t('company.dashboard.offers.table.status') }}</th>
            <th class="px-4 py-2 font-medium">{{ t('company.dashboard.offers.table.spots') }}</th>
            <th class="px-4 py-2 font-medium">{{ t('company.dashboard.offers.table.applications') }}</th>
            <th class="px-4 py-2 font-medium text-right">{{ t('company.dashboard.offers.table.actions') }}</th>
          </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
          <tr v-if="offers.length === 0">
            <td colspan="5" class="px-4 py-6 text-center text-gray-400">
              {{ t('company.dashboard.offers.noOffers') }}
            </td>
          </tr>

          <tr v-for="offer in offers" :key="offer.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 text-gray-900">{{ offer.title }}</td>

            <td class="px-4 py-3">
                <span
                  class="px-2 py-1 rounded-full text-xs font-medium"
                  :class="statusClasses[offer.status] ?? 'bg-gray-100 text-gray-700'"
                >
                  {{ offer.status }}
                </span>
            </td>

            <td class="px-4 py-3 text-gray-700">{{ offer.spots }}</td>

            <td class="px-4 py-3">
              <a
              :href="applicationsHref(offer.id)"
              class="text-blue-600 hover:underline hover:text-blue-800"
              @click="goToApplications($event, offer.id)"
              >
              {{ offer.applications_count }}
              </a>
            </td>

            <td class="px-4 py-3 text-right">
              <div class="relative inline-block text-left" data-offer-menu>
                <BaseButton
                  type="button"
                  variant="outline"
                  class="p-1.5 rounded-md text-gray-500 hover:bg-gray-100"
                  :aria-label="t('company.dashboard.offers.actions.menu')"
                  @click="toggleMenu(offer.id)"
                >
                  <IconDotsVertical class="w-4 h-4" />
                </BaseButton>

                <div
                  v-if="openMenuId === offer.id"
                  class="absolute right-0 z-10 mt-1 w-40 rounded-lg border border-gray-200 bg-white shadow-lg py-1"
                >
                  <BaseButton
                    type="button"
                    variant="outline"
                    class="flex items-center gap-2 w-full px-3 py-2 text-left text-gray-700 hover:bg-gray-50"
                    @click="editOffer(offer)"
                  >
                    <IconPencil class="w-4 h-4" />
                    {{ t('company.dashboard.offers.actions.edit') }}
                  </BaseButton>

                  <BaseButton
                    type="button"
                    variant="outline"
                    class="flex items-center gap-2 w-full px-3 py-2 text-left text-gray-700 hover:bg-gray-50"
                    @click="offer.status === 'published' ? deactivateOffer(offer) : publishOffer(offer)"
                  >
                    <IconPlayerPause v-if="offer.status === 'published'" class="w-4 h-4" />
                    <IconPlayerPlay v-else class="w-4 h-4" />
                    {{
                      offer.status === 'published'
                        ? t('company.dashboard.offers.actions.deactivate')
                        : t('company.dashboard.offers.actions.activate')
                    }}
                  </BaseButton>

                  <BaseButton
                    type="button"
                    variant="outline"
                    class="flex items-center gap-2 w-full px-3 py-2 text-left text-red-600 hover:bg-red-50"
                    @click="deleteOffer(offer)"
                  >
                    <IconTrash class="w-4 h-4" />
                    {{ t('company.dashboard.offers.actions.delete') }}
                  </BaseButton>
                </div>
              </div>
            </td>
          </tr>
          </tbody>
        </table>
      </div>
    </div>
  </BaseLayout>
</template>
