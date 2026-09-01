import { mount } from "@vue/test-utils"
import { beforeEach, describe, expect, it, vi } from "vitest"
import { createI18n } from "vue-i18n"
import BaseLayout from "@/Components/Layouts/BaseLayout.vue"
import { ROUTES } from "@/Helpers/routes"
import en from "@/lang/en.json"

const { pageProps } = vi.hoisted(() => ({
  pageProps: {
    auth: { user: null as { role: string } | null },
  },
}))

vi.mock("@inertiajs/vue3", () => ({
  usePage: () => ({ props: pageProps, url: "/universities/northfield" }),
}))

const i18n = createI18n({ legacy: false, locale: "en", messages: { en } })

const NavbarStub = {
  props: ["menuItems", "navigationButtons"],
  template: "<nav />",
}

const mountLayout = (props = {}) => mount(BaseLayout, {
  props,
  global: {
    plugins: [i18n],
    stubs: { BaseNavbar: NavbarStub },
  },
})

const hrefsOf = (wrapper: ReturnType<typeof mountLayout>, prop: "menuItems" | "navigationButtons") =>
  wrapper.findComponent(NavbarStub).props(prop).map((item: { href: string }) => item.href)

describe("BaseLayout", () => {
  beforeEach(() => {
    pageProps.auth.user = null
  })

  it("leaves the menu empty for guests", () => {
    expect(hrefsOf(mountLayout(), "navigationButtons")).toEqual([])
  })

  it("shows the menu of the signed in student", () => {
    pageProps.auth.user = { role: "student" }

    expect(hrefsOf(mountLayout(), "navigationButtons")).toContain(ROUTES.STUDENT_APPLICATIONS)
  })

  it("shows the menu of the signed in company member", () => {
    pageProps.auth.user = { role: "companyMember" }

    expect(hrefsOf(mountLayout(), "navigationButtons")).toContain(ROUTES.COMPANY_OFFERS_INDEX)
  })

  it("shows the menu of the signed in university member", () => {
    pageProps.auth.user = { role: "universityMember" }

    expect(hrefsOf(mountLayout(), "navigationButtons")).toContain(ROUTES.UNIVERSITY_DASHBOARD)
  })

  it("shows the menu of the signed in super admin", () => {
    pageProps.auth.user = { role: "superAdmin" }

    expect(hrefsOf(mountLayout(), "navigationButtons")).toContain(ROUTES.ADMIN_APPLICATIONS)
  })

  it("lets the page override the menu of the current role", () => {
    pageProps.auth.user = { role: "student" }
    const wrapper = mountLayout({ navItems: [{ key: "own", label: "Own", href: "/own" }] })

    expect(hrefsOf(wrapper, "menuItems")).toEqual(["/own"])
  })

  it("keeps the navigation bar empty when the page defines hamburger items only", () => {
    pageProps.auth.user = { role: "superAdmin" }
    const wrapper = mountLayout({ navItems: [{ key: "own", label: "Own", href: "/own" }] })

    expect(hrefsOf(wrapper, "navigationButtons")).toEqual([])
  })
})
