export const ROUTES = {
  ADMIN_DASHBOARD: "/admin/dashboard",
  ADMIN_APPLICATIONS: "/admin/applications",
  LOGIN: "/login",
  DASHBOARD: "/dashboard",
} as const
 
export type AppRoute = (typeof ROUTES)[keyof typeof ROUTES]
 