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

  COMPANY_APPLICATIONS: "/company/applications",
  COMPANY_APPLICATIONS_CV: "/company/applications/{application}/cv",
  COMPANY_APPLICATIONS_STATUS_UPDATE: "/company/applications/{application}/status",
  COMPANY_SHOW: '/companies/{company}',
  COMPANY_DASHBOARD: "/company/dashboard",
  COMPANY_PARTNERSHIP: "/company/partnership",
  COMPANY_OFFERS_INDEX: "/company/offers",
  COMPANY_OFFERS_CREATE: "/company/offers/create",
  COMPANY_OFFERS_STORE: "/company/offers",
  COMPANY_OFFERS_UPDATE: (id: number | string) => `/company/offers/${id}`,
  COMPANY_OFFERS_EDIT: (id: number | string) => `/company/offers/${id}/edit`,
  COMPANY_OFFERS_PUBLISH: (id: number | string) => `/company/offers/${id}/publish`,
  COMPANY_OFFERS_DEACTIVATE: (id: number | string) => `/company/offers/${id}/deactivate`,
  COMPANY_OFFERS_DELETE: (id: number | string) => `/company/offers/${id}`,

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
  ADMIN_USERS: "/admin/users",

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
  STUDENT_UNIVERSITY_SEARCH: "/student/universities/search",
  STUDENT_UNIVERSITY_UPDATE: "/student/university",

  // Student CV
  STUDENT_CV_PREVIEW: "/student/cv",
  STUDENT_CV_UPLOAD: "/student/cv",
  STUDENT_CV_DELETE: "/student/cv",

  // Student Offers & Applications
  STUDENT_OFFER_APPLY: "/student/offers/{offer}/apply",
  STUDENT_OFFER_WITHDRAW: "/student/offers/{offer}/withdraw",
  STUDENT_OFFER_FAVOURITE: "/student/offers/{offer}/favourite",
  STUDENT_APPLICATIONS: "/student/applications",
  STUDENT_FAVORITES: "/student/favourites",

  // Offers & Search
  OFFERS: "/offers",
  OFFER_SHOW: `/offers/{offer}`,
  COMPANY_MY_OFFERS: "/company/offers",

  // Profiles
  PROFILE: "/profile",
  PROFILE_EDIT: "/profile/edit",
  COMPANY_PROFILE: "/company/profile",
  UNIVERSITY_PROFILE: "/university/profile",



  //University routes
  UNIVERSITY_DASHBOARD: "/university/dashboard",
  UNIVERSITY_COMPANIES_INDEX: "/university/companies",
  UNIVERSITY_PARTNERSHIP: "/university/partnership",
  UNIVERSITY_DEPARTMENT: "/university/department",

  // General
  APPLICATIONS: "/applications",
  TEAM: "/team",
  LANGUAGE_SWITCH: "/language/{locale}",
  SETTINGS: "/settings",

  NOTIFICATIONS_READ_ALL: "/notifications/read-all",
  NOTIFICATIONS_READ: "/notifications/{notification}/read",
} as const

export type AppRoute = (typeof ROUTES)[keyof typeof ROUTES]

export function studentOfferApply(offerId: string): string {
  return ROUTES.STUDENT_OFFER_APPLY.replace("{offer}", offerId)
}

export function studentOfferWithdraw(offerId: string): string {
  return ROUTES.STUDENT_OFFER_WITHDRAW.replace("{offer}", offerId)
}

export function notificationRead(notificationId: string): string {
  return ROUTES.NOTIFICATIONS_READ.replace("{notification}", notificationId)
}

export function studentOfferFavourite(offerId: string): string {
  return ROUTES.STUDENT_OFFER_FAVOURITE.replace("{offer}", offerId)
}

export function offerShow(offerId: string): string {
  return ROUTES.OFFER_SHOW.replace("{offer}", offerId)
}

export function companyShow(companyId: string): string {
  return ROUTES.COMPANY_SHOW.replace("{company}", companyId)
}
