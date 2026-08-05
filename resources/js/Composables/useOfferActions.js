import { ref, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { ROUTES } from '@/Helpers/routes'

export function useOfferActions({ t }) {
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
    router.patch(`${ROUTES.COMPANY_OFFERS_STORE}/${offer.id}/publish`, {}, {
      preserveScroll: true,
    })
    closeMenu()
  }

  function deactivateOffer(offer) {
    router.patch(`${ROUTES.COMPANY_OFFERS_STORE}/${offer.id}/deactivate`, {}, {
      preserveScroll: true,
    })
    closeMenu()
  }

  function toggleStatusOffer(offer) {
    if (offer.status === 'closed') return
    offer.status === 'published' ? deactivateOffer(offer) : publishOffer(offer)
  }

  function deleteOffer(offer) {
    closeMenu()
    if (!confirm(t('company.dashboard.offers.confirmDelete'))) {
      return
    }
    router.delete(`${ROUTES.COMPANY_OFFERS_STORE}/${offer.id}`, {
      preserveScroll: true,
    })
  }

  return {
    openMenuId,
    toggleMenu,
    closeMenu,
    editOffer,
    toggleStatusOffer,
    deleteOffer,
  }
}
