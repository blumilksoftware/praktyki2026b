import { computed, unref } from 'vue'
import { useI18n } from 'vue-i18n'
import { IconBriefcase, IconClipboardText, IconHome, IconSettings, IconUserCircle, IconUsersGroup} from '@tabler/icons-vue'
import { ROUTES } from '@/Helpers/routes'

export function useCompanyPanelMenu(activePage) {
  const { t } = useI18n()

  return computed(() => {
    const current = unref(activePage)

    return [
      {
        key: 'dashboard',
        label: t('company.layout.nav.dashboard'),
        href: ROUTES.COMPANY_DASHBOARD,
        icon: IconHome,
        isActive: current === 'dashboard',
      },
      {
        key: 'offers',
        label: t('company.layout.nav.offers'),
        href: ROUTES.COMPANY_OFFERS_INDEX,
        icon: IconBriefcase,
        isActive: current === 'offers',
      },
      {
        key: 'team',
        label: t('company.layout.nav.team'),
        href: ROUTES.TEAM,
        icon: IconUsersGroup,
        isActive: current === 'team',
      },
      {
        key: 'applications',
        label: t('company.layout.nav.applications'),
        href: ROUTES.COMPANY_APPLICATIONS,
        icon: IconClipboardText,
        isActive: current === 'applications',
      },
      {
        key: 'profile',
        label: t('company.layout.nav.profile'),
        href: ROUTES.COMPANY_PROFILE,
        icon: IconUserCircle,
        isActive: current === 'profile',
      },
      {
        key: 'settings',
        label: t('company.layout.nav.settings'),
        href: ROUTES.SETTINGS,
        icon: IconSettings,
        isActive: current === 'settings',
      },
    ]
  })
}
