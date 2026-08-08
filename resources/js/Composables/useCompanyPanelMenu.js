import { computed, unref } from 'vue'
import { useI18n } from 'vue-i18n'
import { IconBriefcase, IconClipboardText, IconHome, IconSettings, IconUserCircle } from '@tabler/icons-vue'
import { ROUTES } from '@/Helpers/routes'

export function useCompanyPanelMenu(activePage) {
  const { t } = useI18n()

  return computed(() => {
    const current = unref(activePage)

    return [
      {
        key: 'dashboard',
        label: t('common.nav.dashboard'),
        href: ROUTES.COMPANY_DASHBOARD,
        icon: IconHome,
        isActive: current === 'dashboard',
      },
      {
        key: 'offers',
        label: t('common.titles.myOffers'),
        href: ROUTES.COMPANY_OFFERS_INDEX,
        icon: IconBriefcase,
        isActive: current === 'offers',
      },
      {
        key: 'applications',
        label: t('common.nav.applications'),
        href: ROUTES.COMPANY_APPLICATIONS,
        icon: IconClipboardText,
        isActive: current === 'applications',
      },
      {
        key: 'profile',
        label: t('common.words.profile'),
        href: ROUTES.COMPANY_PROFILE,
        icon: IconUserCircle,
        isActive: current === 'profile',
      },
      {
        key: 'settings',
        label: t('common.nav.settings'),
        href: ROUTES.SETTINGS,
        icon: IconSettings,
        isActive: current === 'settings',
      },
    ]
  })
}
