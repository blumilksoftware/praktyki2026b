import { computed, unref } from 'vue'
import { useI18n } from 'vue-i18n'
import { IconHome, IconSchool, IconSearch, IconUsersGroup, IconHeartHandshake, IconBuildings } from '@tabler/icons-vue'
import { ROUTES } from '@/Helpers/routes'

export function useUniversityPanelMenu(activePage) {
  const { t } = useI18n()

  return computed(() => {
    const current = unref(activePage)

    return [
      {
        key: 'dashboard',
        label: t('university.layout.nav.dashboard'),
        href: ROUTES.UNIVERSITY_DASHBOARD,
        icon: IconHome,
        isActive: current === 'dashboard',
      },
      {
        key: 'faculties',
        label: t('university.layout.nav.faculties'),
        href: ROUTES.UNIVERSITY_FACULTIES,
        icon: IconSchool,
        isActive: current === 'faculties',
      },
      {
        key: 'team',
        label: t('university.layout.nav.team'),
        href: ROUTES.TEAM,
        icon: IconUsersGroup,
        isActive: current === 'team',
      },
      {
        key: 'partnership',
        label: t('university.layout.nav.partnership'),
        href: ROUTES.UNIVERSITY_COMPANIES,
        icon: IconHeartHandshake,
        isActive: current === 'partnership',
      },
    ]
  })
}
