import { mount } from "@vue/test-utils"
import { describe, expect, it, vi } from "vitest"
import { createI18n } from "vue-i18n"
import PublicProfile from "@/Pages/University/PublicProfile.vue"
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
  id: "1e0e6a2c-8f4c-4a5a-9b2e-1c0b0b0f0a11",
  name: "Northfield University",
  logoUrl: null,
  description: "A university focused on engineering.",
  email: "contact@northfield.example",
  phone: "+48 111 222 333",
  website: "https://northfield.example",
  street: "Elm Street 4",
  postalCode: "10-100",
  city: "Northfield",
  externalFormUrl: null,
  faculties: [
    { id: "1", name: "Faculty of Computer Science", study_fields: [{ id: "1", name: "Software Engineering" }] },
    { id: "2", name: "Faculty of Physics", study_fields: [] },
  ],
}

const mountProfile = (props = {}) => mount(PublicProfile, {
  props: { university, ...props },
  global: {
    plugins: [i18n],
    stubs: {
      BaseLayout: { template: "<div><slot /></div>" },
    },
  },
})

describe("University/PublicProfile", () => {
  it("renders the public university details", () => {
    const wrapper = mountProfile()

    expect(wrapper.text()).toContain("Northfield University")
    expect(wrapper.text()).toContain("A university focused on engineering.")
    expect(wrapper.text()).toContain("contact@northfield.example")
    expect(wrapper.text()).toContain("Elm Street 4, 10-100 Northfield")
    expect(wrapper.text()).toContain("Faculty of Computer Science")
    expect(wrapper.text()).toContain("Software Engineering")
  })

  it("hides the description card when the university has not written one", () => {
    const wrapper = mountProfile({ university: { ...university, description: null } })

    expect(wrapper.text()).not.toContain(en.profiles.aboutUs)
  })

  it("links to the external application form only when the university provided one", () => {
    expect(mountProfile().text()).not.toContain(en.profiles.university.applicationForm)

    const wrapper = mountProfile({
      university: { ...university, externalFormUrl: "https://northfield.example/form" },
    })
    const link = wrapper.find("a[href=\"https://northfield.example/form\"]")

    expect(link.exists()).toBe(true)
    expect(link.attributes("rel")).toBe("noopener noreferrer")
    expect(wrapper.text()).toContain(en.profiles.university.goToApplicationForm)
  })

  it("reveals further faculties in chunks", async () => {
    const faculties = [0, 1, 2, 3, 4, 5].map(index => ({
      id: String(index),
      name: `Faculty ${index}`,
      study_fields: [],
    }))
    const wrapper = mountProfile({ university: { ...university, faculties } })

    expect(wrapper.text()).toContain("Faculty 3")
    expect(wrapper.text()).not.toContain("Faculty 4")

    const loadMore = wrapper.findAll("button").filter(button => button.text() === en.buttons.load_more)

    expect(loadMore).toHaveLength(1)

    await loadMore[0].trigger("click")

    expect(wrapper.text()).toContain("Faculty 5")
    expect(wrapper.text()).not.toContain(en.buttons.load_more)
  })
})
