export const ROUTES = {
  // Public & Home
  HOME: "/",
  LOGOUT: "/logout",

  // Authentication - Login & Admin
  LOGIN: "/login",
  LOGIN_ADMIN: "/admin/login",
  DASHBOARD: "/dashboard",

  // Authentication - Registration
  REGISTER_STUDENT: "/register/student",
  REGISTER_UNIVERSITY: "/register/university",
  REGISTER_COMPANY: "/register/company",

  // Authentication - Password & Email
  FORGOT_PASSWORD: "/forgot-password",
  RESET_PASSWORD: "/reset-password/{token}",
  RESET_PASSWORD_STORE: "/reset-password",
  EMAIL_VERIFICATION: "/email/verify/{id}/{token}",
  EMAIL_VERIFICATION_RESEND: "/email/resend",

  // Authentication - OAuth
  GOOGLE_AUTH: "/auth/google/redirect",
  GOOGLE_AUTH_CALLBACK: "/auth/google/callback",

  // Admin
  ADMIN_DASHBOARD: "/admin/dashboard",
  ADMIN_APPLICATIONS: "/admin/applications",

  // Student Profile & Settings
  STUDENT_DASHBOARD: "/student/dashboard",
  STUDENT_PROFILE: "/student/profile",
  STUDENT_PROFILE_EDIT: "/student/profile/edit",
  STUDENT_PROFILE_UPDATE: "/student/profile",
  STUDENT_PROFILE_PHOTO: "/student/profile/photo",
  STUDENT_PROFILE_PHOTO_SHOW: "/student/profile/photo",
  STUDENT_PROFILE_PHOTO_DELETE: "/student/profile/photo",
  STUDENT_PASSWORD_UPDATE: "/student/password",
  STUDENT_EMAIL_UPDATE: "/student/email",
  STUDENT_SETTINGS: "/student/settings",
  STUDENT_ACCOUNT_DELETE: "/student/account",

  // Student CV
  STUDENT_CV_PREVIEW: "/student/cv",
  STUDENT_CV_UPLOAD: "/student/cv",
  STUDENT_CV_DELETE: "/student/cv",

  // Student Offers & Applications
  STUDENT_OFFERS: "/student/offers",
  STUDENT_OFFER_WITHDRAW: "/student/offers/{offer}/withdraw",
  STUDENT_APPLICATIONS: "/student/applications",
  STUDENT_FAVORITES: "/student/favorites",

  // Offers & Search
  OFFERS: "/offers",
  OFFERS_SEARCH: "/offers",
  OFFER_SHOW: `/offers/{offer}`,
  COMPANY_OFFERS: "/search?company_id={companyId}",

  // Profiles
  PROFILE: "/profile",
  PROFILE_EDIT: "/profile/edit",
  COMPANY_PROFILE: "/company/profile",
  UNIVERSITY_PROFILE: "/university/profile",

  // General
  APPLICATIONS: "/applications",
  TEAM: "/team",
  LANGUAGE_SWITCH: "/language/{locale}",
  SETTINGS: "/settings",
} as const

export type AppRoute = (typeof ROUTES)[keyof typeof ROUTES]

export function studentOfferWithdraw(offerId: string): string {
  return ROUTES.STUDENT_OFFER_WITHDRAW.replace("{offer}", offerId)
}
