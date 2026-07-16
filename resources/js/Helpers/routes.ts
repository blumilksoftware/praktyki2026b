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

  STUDENT_DASHBOARD: "/student/dashboard",
  STUDENT_OFFERS: "/student/offers",
  STUDENT_FAVORITES: "/student/favorites",

  EMAIL_VERIFICATION: "/email/verify/{id}/{token}",
  EMAIL_VERIFICATION_RESEND: "/email/resend",

  RESET_PASSWORD: "/reset-password/{token}",
  RESET_PASSWORD_STORE: "/reset-password",

  GOOGLE_AUTH: "/auth/google/redirect",
  GOOGLE_AUTH_CALLBACK: "/auth/google/callback",

  HOME: "/",
} as const

export type AppRoute = (typeof ROUTES)[keyof typeof ROUTES]
