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

  STUDENT_DASHBOARD: "/student/dashboard",
  STUDENT_PROFILE: "/student/profile",
  STUDENT_PROFILE_EDIT: "/student/profile/edit",
  STUDENT_PROFILE_UPDATE: "/student/profile",
  STUDENT_PROFILE_PHOTO: "/student/profile/photo",
  STUDENT_PROFILE_PHOTO_DELETE: "/student/profile/photo",
  STUDENT_CV_PREVIEW: "/student/cv",
  STUDENT_CV_UPLOAD: "/student/cv",
  STUDENT_CV_DELETE: "/student/cv",
  STUDENT_PASSWORD_UPDATE: "/student/password",
  STUDENT_EMAIL_UPDATE: "/student/email",
  STUDENT_ACCOUNT_DELETE: "/student/account",
  COMPANY_PROFILE: "/company/profile",
  UNIVERSITY_PROFILE: "/university/profile",

  HOME: "/",
} as const

export type AppRoute = (typeof ROUTES)[keyof typeof ROUTES]
