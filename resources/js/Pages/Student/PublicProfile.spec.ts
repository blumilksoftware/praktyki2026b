import { mount } from "@vue/test-utils"
import { describe, expect, it, vi } from "vitest"
import { createI18n } from "vue-i18n"
import PublicProfile from "@/Pages/Student/PublicProfile.vue"
import en from "@/lang/en.json"

const i18n = createI18n({ legacy: false, locale: "en", messages: { en } })

vi.mock("@inertiajs/vue3", async () => {
  const actual = await vi.importActual("@inertiajs/vue3")
  return {
    ...actual,
    Head: { template: "<div />" },
  }
})

const student = {
  id: "1e0e6a2c-8f4c-4a5a-9b2e-1c0b0b0f0a11",
  first_name: "John",
  last_name: "Doe",
  full_name: "John Doe",
  email: "john@example.com",
  age: 22,
  city: "Student City",
  university: "Test University",
  study_field: "Computer science",
  study_year: 3,
  specialization: "Web applications",
  skills: ["PHP", "Vue"],
  work_modes: ["remote"],
  preferred_cities: ["Preferred City"],
  preferred_study_fields: ["IT"],
}

const mountProfile = (props = {}) => mount(PublicProfile, {
  props: { student, ...props },
  global: {
    plugins: [i18n],
    stubs: {
      BaseLayout: { template: "<div><slot /></div>" },
    },
  },
})

describe("Student/PublicProfile", () => {
  it("renders the candidate details a company is allowed to see", () => {
    const wrapper = mountProfile()

    expect(wrapper.text()).toContain("John Doe")
    expect(wrapper.text()).toContain("john@example.com")
    expect(wrapper.text()).toContain("Test University")
    expect(wrapper.text()).toContain("Computer science")
    expect(wrapper.text()).toContain("year 3")
    expect(wrapper.text()).toContain("Web applications")
    expect(wrapper.text()).toContain("Student City")
  })

  it("renders skills, work modes and search preferences", () => {
    const wrapper = mountProfile()

    expect(wrapper.text()).toContain("PHP")
    expect(wrapper.text()).toContain("Remote")
    expect(wrapper.text()).toContain("Preferred City")
    expect(wrapper.text()).toContain("IT")
  })

  it("shows empty states when the candidate filled nothing in", () => {
    const wrapper = mountProfile({
      student: { ...student, skills: [], work_modes: [], preferred_cities: [], preferred_study_fields: [] },
    })

    expect(wrapper.text()).toContain("The candidate has not added any technical skills.")
    expect(wrapper.text()).toContain("The candidate has not selected an expected work mode.")
    expect(wrapper.text()).toContain("The candidate has not set any search preferences.")
  })

  it("links to the CV only when the candidate attached one", () => {
    expect(mountProfile().text()).toContain("No CV available")

    const wrapper = mountProfile({ cvUrl: "/company/applications/1/cv" })

    expect(wrapper.find("a[href=\"/company/applications/1/cv\"]").exists()).toBe(true)
    expect(wrapper.text()).toContain("Download CV")
  })
})
