import { computed, unref } from 'vue'
import { useI18n } from 'vue-i18n'
import { IconHome, IconUserCircle} from '@tabler/icons-vue'
import { ROUTES } from '@/Helpers/routes'

export function useUniversityPanelMenu(activePage) {
  const { t } = useI18n()

  return computed(() => {
    const current = unref(activePage)

    return [
      {
        label: t('university.layout.nav.dashboard'),
        href: ROUTES.UNIVERSITY_DASHBOARD,
        icon: IconHome,
        isActive: current === 'dashboard',
      },
      {
        label: t('university.layout.nav.profile'),
        href: ROUTES.UNIVERSITY_PROFILE,
        icon: IconUserCircle,
        isActive: current === 'profile',
      },
    ]
  })
}
