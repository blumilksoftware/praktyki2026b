import { computed } from 'vue'
import { useCompanyPanelMenu } from './companyMenu.js'
import { useUniversityPanelMenu } from './universityMenu.js'
import { useStudentPanelMenu } from './studentMenu.js'
import { useAdminPanelMenu } from './adminMenu.js'
import { useI18n } from 'vue-i18n'
import { IconBriefcase } from '@tabler/icons-vue'
import { ROUTES } from '@/Helpers/routes'

const resolvers = {
  student: useStudentPanelMenu,
  companyAdmin: useCompanyPanelMenu,
  companyMember: useCompanyPanelMenu,
  universityAdmin: useUniversityPanelMenu,
  superAdmin: useAdminPanelMenu,
}

function usePendingCompanyMenu() {
  const { t } = useI18n()
  return computed(() => [
    { key: 'offers', label: t('company.layout.nav.offers'), href: ROUTES.COMPANY_OFFERS_CREATE, icon: IconBriefcase, isActive: true},
  ])
}
export function resolvePanelMenu(role, activePage, isPending) {
  if (isPending){
    if (role === 'companyAdmin' || role === 'companyMember') return usePendingCompanyMenu()
    return computed(() => [])
  }
  const resolver = resolvers[role]
  return resolver ? resolver(activePage) : computed(() => [])
}
