export const ROUTES = {
  registerStudent: '/register/student',
  registerCompany: '/register/company',
  login: '/login',
  googleRedirect: '/auth/google/redirect',
  adminDashboard: '/admin/dashboard',
  adminApplications: '/admin/applications',
} as const

export type AppRoute = (typeof ROUTES)[keyof typeof ROUTES]
