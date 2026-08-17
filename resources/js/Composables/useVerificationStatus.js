import { useI18n } from 'vue-i18n'

export function useVerificationStatus() {
  const { t } = useI18n()

  function statusClass(status) {
    switch ((status || '').toLowerCase()) {
    case 'verified':
      return 'bg-green-100 text-green-700'
    case 'rejected':
      return 'bg-red-100 text-red-700'
    case 'pending':
      return 'bg-amber-100 text-amber-700'
    default:
      return 'bg-gray-100 text-gray-700'
    }
  }

  function statusTranslate(status) {
    switch ((status || '').toLowerCase()) {
    case 'verified':
      return t('admin.verification.verified')
    case 'rejected':
      return t('admin.verification.rejected')
    case 'pending':
      return t('admin.verification.pending')
    default:
      return status
    }
  }

  return {
    statusClass,
    statusTranslate,
  }
}
