export const ROUTES = {
  ADMIN_DASHBOARD: "/admin/dashboard",
  ADMIN_APPLICATIONS: "/admin/applications",
  DASHBOARD: "/dashboard",
  LOGIN: "/login",
  LOGIN_ADMIN: "/admin/login",
  FORGOT_PASSWORD: "/forgot-password",

  REGISTER_STUDENT: "/register/student",
  REGISTER_UNIVERSITY: "/register/university",
  REGISTER_COMPANY: "/register/company",

  EMAIL_VERIFICATION: "/email/verify/{id}/{token}",
  EMAIL_VERIFICATION_RESEND: "/email/resend",

  RESET_PASSWORD: "/reset-password/{token}",
  RESET_PASSWORD_STORE: "/reset-password",

  GOOGLE_AUTH: "/auth/google/redirect",
  GOOGLE_AUTH_CALLBACK: "/auth/google/callback",

  LANGUAGE_SWITCH: "/language/{locale}",
  
  HOME: "/",

  OFFER_SHOW: `/offers/{offer}`,
  OFFERS: "/offers",
  COMPANY_OFFERS: "/search?company_id={companyId}",
  APPLICATIONS: "/applications",
  PROFILE: "/profile",
  PROFILE_EDIT: "/profile/edit",
  TEAM: "/team",
  
  SETTINGS: "/settings",
  LOGOUT: "/logout",
} as const

export type AppRoute = (typeof ROUTES)[keyof typeof ROUTES]