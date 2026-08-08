import { useI18n } from 'vue-i18n'

export function useVerificationStatus() {
  const { t } = useI18n()

  function statusClass(status) {
    switch ((status || '').toLowerCase()) {
    case 'verified':
      return 'bg-green-50 text-green-900'
    case 'rejected':
      return 'bg-red-50 text-red-900'
    case 'pending':
      return 'bg-amber-50 text-amber-900'
    default:
      return 'bg-slate-50 text-slate-900'
    }
  }

  function statusTranslate(status) {
    switch ((status || '').toLowerCase()) {
    case 'verified':
      return t('admin.verification.verified')
    case 'rejected':
      return t('common.statuses.application.rejected')
    case 'pending':
      return t('common.statuses.application.pending')
    default:
      return status
    }
  }

  return {
    statusClass,
    statusTranslate,
  }
}
