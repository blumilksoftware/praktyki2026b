// @/Composables/useCompanyPanelMenu.js
import { computed, unref } from 'vue'
import { useI18n } from 'vue-i18n'
import { IconHome, IconUser } from '@tabler/icons-vue'
import { ROUTES } from '@/Helpers/routes'

export function useCompanyPanelMenu(activePage) {
  const { t } = useI18n()

  return computed(() => {
    const current = unref(activePage)
    return [
      {
        label: t('company.layout.nav.dashboard'),
        href: ROUTES.COMPANY_DASHBOARD,
        icon: IconHome,
        isActive: current === 'dashboard',
      },
      {
        label: t('company.layout.nav.profile'),
        href: ROUTES.COMPANY_PROFILE,
        icon: IconUser,
        isActive: current === 'profile',
      },
    ]
  })
}
