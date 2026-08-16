import { computed, unref } from 'vue'
import { useI18n } from 'vue-i18n'
import {IconHome, IconUserCircle, IconUsersGroup, IconHeartHandshake, IconBuildings} from '@tabler/icons-vue'
import { ROUTES } from '@/Helpers/routes'

export function useUniversityPanelMenu(activePage) {
  const {t} = useI18n()

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
        key: 'team',
        label: t('university.layout.nav.team'),
        href: ROUTES.TEAM,
        icon: IconUsersGroup,
        isActive: current === 'team',
      },
      {
        key: 'departament',
        label: t('university.layout.nav.department'),
        href: ROUTES.UNIVERSITY_DEPARTMENT,
        icon: IconBuildings,
        isActive: current === 'departament',
      },
      {
        key: 'partnership',
        label: t('university.layout.nav.partnership'),
        href: ROUTES.UNIVERSITY_PARTNERSHIP,
        icon: IconHeartHandshake,
        isActive: current === 'partnership',
      }
    ]
  })
}
