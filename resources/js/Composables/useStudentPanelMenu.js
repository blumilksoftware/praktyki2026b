import { computed, unref } from 'vue'
import { useI18n } from 'vue-i18n'
import { IconHome, IconUserCircle, IconBriefcase, IconHeart } from '@tabler/icons-vue'
import { ROUTES } from '@/Helpers/routes'

export function useStudentPanelMenu(activePage) {
  const { t } = useI18n()

  return computed(() => {
    const current = unref(activePage)

    return [
      {
        label: t('student.layout.nav.dashboard'),
        href: ROUTES.STUDENT_DASHBOARD,
        icon: IconHome,
        isActive: current === 'dashboard',
      },
      {
        label: t('student.layout.nav.profile'),
        href: ROUTES.STUDENT_PROFILE,
        icon: IconUserCircle,
        isActive: current === 'profile',
      },
      {
        label: t('student.layout.nav.offers'),
        href: ROUTES.STUDENT_OFFERS,
        icon: IconBriefcase,
        isActive: current === 'offers',
      },
      {
        label: t('student.layout.nav.favorites'),
        href: ROUTES.STUDENT_FAVORITES,
        icon: IconHeart,
        isActive: current === 'favorites',
      },
    ]
  })
}