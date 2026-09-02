import { computed, unref } from 'vue'
import { useI18n } from 'vue-i18n'
import { IconClipboard, IconHome, IconUsers, IconUsersGroup, IconBriefcase, IconTags } from '@tabler/icons-vue'
import { ROUTES } from '@/Helpers/routes'

export function useAdminPanelMenu(activePage) {
  const { t } = useI18n()

  return computed(() => {
    const current = unref(activePage)

    return [
      {
        key: 'dashboard',
        label: t('admin.layout.nav.dashboard'),
        href: ROUTES.ADMIN_DASHBOARD,
        icon: IconHome,
        isActive: current === 'dashboard',
      },
      {
        key: 'applications',
        label: t('admin.layout.nav.applications'),
        href: ROUTES.ADMIN_APPLICATIONS,
        icon: IconClipboard,
        isActive: current === 'applications',
      },
      {
        key: 'users',
        label: t('admin.layout.nav.users'),
        href: ROUTES.ADMIN_USERS,
        icon: IconUsers,
        isActive: current === 'users',
      },
      {
        key: 'offers',
        label: t('admin.layout.nav.offers'),
        href: ROUTES.ADMIN_OFFERS,
        icon: IconBriefcase,
        isActive: current === 'offers',
      },
      {
        key: 'industryTags',
        label: t('admin.layout.nav.industryTags'),
        href: ROUTES.ADMIN_INDUSTRY_TAGS,
        icon: IconTags,
        isActive: current === 'industryTags',
      },
    ]
  })
}
