import { computed, unref } from 'vue'
import { useI18n } from 'vue-i18n'
import { IconClipboard, IconHome, IconUsers } from '@tabler/icons-vue'
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
    ]
  })
}
