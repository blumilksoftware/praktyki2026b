import { computed, unref } from 'vue'
import { useI18n } from 'vue-i18n'
import { IconBriefcase, IconBuildingBank, IconClipboardText, IconHome, IconSettings, IconUserCircle } from '@tabler/icons-vue'
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
        key: 'applications',
        label: t('company.layout.nav.applications'),
        href: ROUTES.COMPANY_APPLICATIONS,
        icon: IconClipboardText,
        isActive: current === 'applications',
      },
      {
        key: 'universities',
        label: t('company.layout.nav.universities'),
        href: ROUTES.COMPANY_UNIVERSITIES,
        icon: IconBuildingBank,
        isActive: current === 'universities',
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
