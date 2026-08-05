import { useI18n } from 'vue-i18n'

const STATUS_CLASS: Record<string, string> = {
  none: 'bg-slate-50 text-slate-900 ring-1 ring-slate-200',
  pending: 'bg-amber-50 text-amber-900 ring-1 ring-amber-200',
  active: 'bg-green-50 text-green-900 ring-1 ring-green-200',
  suspended: 'bg-red-50 text-red-900 ring-1 ring-red-200',
}

export function usePartnershipStatus() {
  const { t } = useI18n()

  function statusClass(status: string): string {
    return STATUS_CLASS[status] ?? STATUS_CLASS.none
  }

  function statusLabel(status: string): string {
    return t(`university.companies.status.${status}`)
  }

  return { statusClass, statusLabel }
}
