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

  COMPANY_DASHBOARD: "/company/dashboard",
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
  ADMIN_USERS_UPDATE_ROLE: (id: string) => `/admin/users/${id}/role`,
  ADMIN_USERS_UPDATE_STATUS: (id: string) => `/admin/users/${id}/status`,
  ADMIN_OFFERS: "/admin/offers",
  ADMIN_OFFERS_TAKE_DOWN: (id: string) => `/admin/offers/${id}/take-down`,
  ADMIN_COMPANY_DELETE: (id: string) => `/admin/companies/${id}`,
  ADMIN_UNIVERSITY_DELETE: (id: string) => `/admin/universities/${id}`,
  ADMIN_DELETE_USER: (id: string) => `/admin/users/${id}`,
  ADMIN_USER_DELETION_IMPACT: (id: string) => `/admin/users/${id}/deletion-impact`,
  ADMIN_INDUSTRY_TAGS: "/admin/industry-tags",
  ADMIN_INDUSTRY_TAG: (id: string) => `/admin/industry-tags/${id}`,

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
  STUDENT_UNIVERSITY_FACULTIES: (universityId: string) => `/student/universities/${universityId}/faculties`,

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
  OFFER_PREVIEW: `/offers/{offer}/preview`,
  COMPANY_MY_OFFERS: "/company/offers",

  // Profiles
  PROFILE: "/profile",
  PROFILE_EDIT: "/profile/edit",
  COMPANY_PROFILE: "/company/profile",
  UNIVERSITY_PROFILE: "/university/profile",


  COMPANY_APPLICATIONS: "/company/applications",
  COMPANY_APPLICATIONS_CV: "/company/applications/{application}/cv",
  COMPANY_APPLICATIONS_STATUS_UPDATE: "/company/applications/{application}/status",
  COMPANY_SHOW: '/companies/{company}',
  COMPANY_REVIEWS_STORE: '/student/companies/{company}/reviews',
  COMPANY_REVIEW_HIDE: '/company/reviews/{review}/hide',
  COMPANY_REVIEW_UNHIDE: '/company/reviews/{review}/unhide',
  ADMIN_REVIEW_DELETE: '/admin/reviews/{review}',
  //University routes
  UNIVERSITY_DASHBOARD: "/university/dashboard",
  UNIVERSITY_SHOW: "/universities/{university}",
  UNIVERSITY_FACULTIES: "/university/faculties",
  UNIVERSITY_FACULTY: (facultyId: string) => `/university/faculties/${facultyId}`,
  UNIVERSITY_FACULTY_STUDY_FIELDS: (facultyId: string) => `/university/faculties/${facultyId}/study-fields`,
  UNIVERSITY_STUDY_FIELD: (studyFieldId: string) => `/university/study-fields/${studyFieldId}`,
  UNIVERSITY_COMPANIES: "/university/companies",
  UNIVERSITY_COMPANY_PARTNERSHIP: "/university/companies/{company}/partnership",
  UNIVERSITY_COMPANY_PARTNERSHIP_ACCEPT: "/university/companies/{company}/partnership/accept",

  COMPANY_UNIVERSITIES: "/company/universities",
  COMPANY_UNIVERSITY_PARTNERSHIP: "/company/universities/{university}/partnership",
  COMPANY_UNIVERSITY_PARTNERSHIP_ACCEPT: "/company/universities/{university}/partnership/accept",

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

export function offerPreview(offerId: string): string {
  return ROUTES.OFFER_PREVIEW.replace("{offer}", offerId)
}

export function companyShow(companyId: string): string {
  return ROUTES.COMPANY_SHOW.replace("{company}", companyId)
}

export function universityCompanyPartnership(companyId: string): string {
  return ROUTES.UNIVERSITY_COMPANY_PARTNERSHIP.replace("{company}", companyId)
}

export function universityCompanyPartnershipAccept(companyId: string): string {
  return ROUTES.UNIVERSITY_COMPANY_PARTNERSHIP_ACCEPT.replace("{company}", companyId)
}

export function companyUniversityPartnership(universityId: string): string {
  return ROUTES.COMPANY_UNIVERSITY_PARTNERSHIP.replace("{university}", universityId)
}

export function companyUniversityPartnershipAccept(universityId: string): string {
  return ROUTES.COMPANY_UNIVERSITY_PARTNERSHIP_ACCEPT.replace("{university}", universityId)
}

export function universityShow(universityId: string): string {
  return ROUTES.UNIVERSITY_SHOW.replace("{university}", universityId)
}

export function companyReviewsStore(companyId: string): string {
  return ROUTES.COMPANY_REVIEWS_STORE.replace("{company}", companyId)
}

export function companyReviewHide(reviewId: string): string {
  return ROUTES.COMPANY_REVIEW_HIDE.replace("{review}", reviewId)
}

export function companyReviewUnhide(reviewId: string): string {
  return ROUTES.COMPANY_REVIEW_UNHIDE.replace("{review}", reviewId)
}

export function adminReviewDelete(reviewId: string): string {
  return ROUTES.ADMIN_REVIEW_DELETE.replace("{review}", reviewId)
}
