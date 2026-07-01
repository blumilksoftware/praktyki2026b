export const ROUTES = {
  REGISTER_STUDENT: '/register/student',
  REGISTER_COMPANY: '/register/company',
  LOGIN: '/login',
  GOOGLE_REDIRECT: '/auth/google/redirect',
  ADMIN_DASHBOARD: '/admin/dashboard',
  ADMIN_APPLICATIONS: '/admin/applications',
} as const

export type AppRoute = (typeof ROUTES)[keyof typeof ROUTES]
