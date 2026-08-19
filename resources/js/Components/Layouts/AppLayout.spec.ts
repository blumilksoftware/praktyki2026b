import { mount } from "@vue/test-utils"
import { beforeEach, describe, expect, it, vi } from "vitest"
import { createI18n } from "vue-i18n"
import AppLayout from "@/Components/Layouts/AppLayout.vue"
import { ROUTES } from "@/Helpers/routes"
import en from "@/lang/en.json"

const { pageProps } = vi.hoisted(() => ({
  pageProps: { auth: { user: null as Record<string, unknown> | null } },
}))

vi.mock("@inertiajs/vue3", () => ({
  usePage: () => ({ props: pageProps, url: "/offers" }),
}))

const i18n = createI18n({ legacy: false, locale: "en", messages: { en } })

const NavbarStub = {
  props: ["menuItems", "navigationButtons"],
  template: "<nav />",
}

const mountLayout = (props = {}) => mount(AppLayout, {
  props,
  global: {
    plugins: [i18n],
    stubs: { BaseNavbar: NavbarStub },
  },
})

const menuOf = (wrapper: ReturnType<typeof mountLayout>) =>
  wrapper.findComponent(NavbarStub).props("navigationButtons") as { key?: string, href: string, isActive: boolean }[]

const hrefsOf = (wrapper: ReturnType<typeof mountLayout>) => menuOf(wrapper).map((item) => item.href)

describe("AppLayout", () => {
  beforeEach(() => {
    pageProps.auth.user = null
  })

  it("leaves the menu empty for guests", () => {
    expect(hrefsOf(mountLayout())).toEqual([])
  })

  it("shows the menu of a signed in student", () => {
    pageProps.auth.user = { role: "student" }

    expect(hrefsOf(mountLayout())).toContain(ROUTES.STUDENT_APPLICATIONS)
  })

  it("shows the menu of a signed in company admin", () => {
    pageProps.auth.user = { role: "companyAdmin" }

    expect(hrefsOf(mountLayout())).toContain(ROUTES.COMPANY_OFFERS_INDEX)
  })

  it("shows the menu of a signed in company member", () => {
    pageProps.auth.user = { role: "companyMember" }

    expect(hrefsOf(mountLayout())).toContain(ROUTES.COMPANY_OFFERS_INDEX)
  })

  it("shows the menu of a signed in university admin", () => {
    pageProps.auth.user = { role: "universityAdmin" }

    expect(hrefsOf(mountLayout())).toContain(ROUTES.UNIVERSITY_DASHBOARD)
  })

  it("shows the menu of a signed in university member", () => {
    pageProps.auth.user = { role: "universityMember" }

    expect(hrefsOf(mountLayout())).toContain(ROUTES.UNIVERSITY_DASHBOARD)
  })

  it("shows the menu of a signed in super admin", () => {
    pageProps.auth.user = { role: "superAdmin" }

    expect(hrefsOf(mountLayout())).toContain(ROUTES.ADMIN_APPLICATIONS)
  })

  it("marks the current page as active in the menu", () => {
    pageProps.auth.user = { role: "companyAdmin" }

    const active = menuOf(mountLayout({ activePage: "offers" })).find((item) => item.href === ROUTES.COMPANY_OFFERS_INDEX)

    expect(active?.isActive).toBe(true)
  })

  it("restricts a pending company to the draft-offer entry only", () => {
    pageProps.auth.user = { role: "companyAdmin", company: { verification_status: "pending" } }

    expect(hrefsOf(mountLayout())).toEqual([ROUTES.COMPANY_OFFERS_CREATE])
  })

  it("hides the menu entirely for a pending university account", () => {
    pageProps.auth.user = { role: "universityAdmin", universityOrganization: { verification_status: "pending" } }

    expect(hrefsOf(mountLayout())).toEqual([])
  })

  it("renders a skip-to-content link targeting the main region", () => {
    const wrapper = mountLayout()

    const skipLink = wrapper.find("a[href='#main-content']")

    expect(skipLink.exists()).toBe(true)
    expect(skipLink.text()).toBe("Skip to content")
  })

  it("renders the page content inside the main landmark", () => {
    const wrapper = mount(AppLayout, {
      slots: { default: "<p>Page body</p>" },
      global: {
        plugins: [i18n],
        stubs: { BaseNavbar: NavbarStub },
      },
    })

    expect(wrapper.find("main#main-content p").text()).toBe("Page body")
  })
})
