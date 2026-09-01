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

  it("links the university name to its public profile only when verified", () => {
    expect(mountProfile().find("a[href^=\"/universities/\"]").exists()).toBe(false)

    const wrapper = mountProfile({
      student: { ...student, university_id: "9f1d1a2b-3c4d-4e5f-8a9b-0c1d2e3f4a5b" },
    })
    const link = wrapper.find("a[href=\"/universities/9f1d1a2b-3c4d-4e5f-8a9b-0c1d2e3f4a5b\"]")

    expect(link.exists()).toBe(true)
    expect(link.text()).toBe("Test University")
  })

  it("links to the CV only when the candidate attached one", () => {
    expect(mountProfile().text()).toContain("No CV available")

    const wrapper = mountProfile({ cvUrl: "/company/applications/1/cv" })
    const link = wrapper.get("a[href=\"/company/applications/1/cv\"]")

    expect(link.attributes("rel")).toBe("noopener noreferrer")
    expect(wrapper.text()).toContain("Download CV")
  })
})
