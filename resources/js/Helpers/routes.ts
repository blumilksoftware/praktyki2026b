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

  COMPANY_OFFERS_STORE: "/company/offers",
  COMPANY_DASHBOARD: "/company/dashboard",

  EMAIL_VERIFICATION: "/email/verify/{id}/{token}",
  EMAIL_VERIFICATION_RESEND: "/email/resend",

  RESET_PASSWORD: "/reset-password/{token}",
  RESET_PASSWORD_STORE: "/reset-password",

  GOOGLE_AUTH: "/auth/google/redirect",
  GOOGLE_AUTH_CALLBACK: "/auth/google/callback",

  LANGUAGE_SWITCH: "/language/{locale}",

  STUDENT_DASHBOARD: "/student/dashboard",
  STUDENT_PROFILE: "/student/profile",
  STUDENT_PROFILE_EDIT: "/student/profile/edit",
  STUDENT_SETTINGS: "/student/settings",
  STUDENT_PROFILE_UPDATE: "/student/profile",
  STUDENT_PROFILE_PHOTO: "/student/profile/photo",
  STUDENT_PROFILE_PHOTO_DELETE: "/student/profile/photo",
  STUDENT_CV_PREVIEW: "/student/cv",
  STUDENT_CV_UPLOAD: "/student/cv",
  STUDENT_CV_DELETE: "/student/cv",
  STUDENT_PASSWORD_UPDATE: "/student/password",
  STUDENT_EMAIL_UPDATE: "/student/email",
  STUDENT_ACCOUNT_DELETE: "/student/account",
  STUDENT_APPLICATIONS: "/student/applications",
  STUDENT_OFFER_WITHDRAW: "/student/offers/{offer}/withdraw",
  COMPANY_PROFILE: "/company/profile",
  COMPANY_APPLICATIONS: "/company/applications",
  UNIVERSITY_PROFILE: "/university/profile",

  HOME: "/",
  OFFERS_SEARCH: "/offers",

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

export function studentOfferWithdraw(offerId: string): string {
  return ROUTES.STUDENT_OFFER_WITHDRAW.replace("{offer}", offerId)
}
