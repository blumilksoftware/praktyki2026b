export function useOfferStatus() {
  function statusClass(status) {
    switch (status) {
    case 'published':
      return 'bg-green-100 text-green-800'
    case 'closed':
      return 'bg-red-100 text-red-700'
    case 'draft':
      return 'bg-gray-100 text-gray-700'
    case 'expired':
      return 'bg-amber-100 text-amber-700'
    default:
      return 'bg-gray-100 text-gray-700'
    }
  }

  return {
    statusClass,
  }
}
