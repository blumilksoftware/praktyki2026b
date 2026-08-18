import { mount } from "@vue/test-utils"
import { describe, expect, it, vi } from "vitest"
import { createI18n } from "vue-i18n"
import Show from "@/Pages/University/Profile/Show.vue"
import en from "@/lang/en.json"

const i18n = createI18n({ legacy: false, locale: "en", messages: { en } })

vi.mock("@inertiajs/vue3", async () => {
  const actual = await vi.importActual("@inertiajs/vue3")

  return {
    ...actual,
    Head: { template: "<div />" },
  }
})

const university = {
  id: "1",
  name: "Northfield University",
  logoUrl: null,
  email: "contact@northfield.example",
  domain: "northfield.example",
  street: "Elm Street 4",
  postalCode: "10-100",
  city: "Northfield",
  phone: "+48 111 222 333",
  website: "https://northfield.example",
  description: null,
  externalFormUrl: null,
  faculties: [],
}

const mountShow = (overrides = {}) => mount(Show, {
  props: { university: { ...university, ...overrides } },
  global: {
    plugins: [i18n],
    stubs: { BaseNavbar: true },
  },
})

describe("University/Profile/Show", () => {
  it("renders the university description", () => {
    const wrapper = mountShow({ description: "A university focused on engineering." })

    expect(wrapper.text()).toContain("A university focused on engineering.")
    expect(wrapper.text()).not.toContain(en.profiles.university.noDescription)
  })

  it("renders the university-specific empty message when there is no description", () => {
    const wrapper = mountShow()

    expect(wrapper.text()).toContain(en.profiles.university.noDescription)
  })
})
