import { useI18n } from 'vue-i18n'

const STATUS_CLASS: Record<string, string> = {
  pending: 'bg-amber-50 text-amber-900 ring-1 ring-amber-200',
  reviewed: 'bg-sky-50 text-sky-900 ring-1 ring-sky-200',
  accepted: 'bg-green-50 text-green-900 ring-1 ring-green-200',
  rejected: 'bg-red-50 text-red-900 ring-1 ring-red-200',
}

export function useApplicationStatus() {
  const { t } = useI18n()

  function statusClass(status: string): string {
    return STATUS_CLASS[status] ?? 'bg-slate-50 text-slate-900 ring-1 ring-slate-200'
  }

  function statusLabel(status: string): string {
    return t(`common.statuses.application.${status}`)
  }

  return { statusClass, statusLabel }
}
