import { mount } from "@vue/test-utils"
import { nextTick } from "vue"
import { describe, expect, it, vi } from "vitest"
import { createI18n } from "vue-i18n"
import Profile from "@/Pages/Student/Profile.vue"
import en from "@/lang/en.json"

const i18n = createI18n({ legacy: false, locale: "en", messages: { en } })

const user = {
  first_name: "Jan",
  last_name: "Kowalski",
  email: "jan@example.com",
  email_verified_at: "2026-01-15T10:00:00.000000Z",
  photo_url: null,
  age: 22,
  location: "Legnica, Poland",
  university: "Collegium Witelona",
  university_verified: true,
  study_field: "Computer science",
  study_year: "3",
  specialization: "Web applications",
  preferred_fields: ["IT"],
  preferred_cities: ["Wroclaw"],
  cv_path: null,
  skills: ["Python", "Django", "React", "TypeScript"],
  work_modes: ["Hybrid", "Remote"],
  applications: [],
}

vi.mock("@inertiajs/vue3", async () => {
  const actual = await vi.importActual("@inertiajs/vue3")
  return {
    ...actual,
    Head: { template: "<div />" },
    usePage: () => ({ props: { onboarding: { steps: [], show: false }, auth: { user: null } } }),
    useForm: (data: Record<string, unknown>) => ({
      ...data,
      processing: false,
      errors: {},
      isDirty: false,
      reset: vi.fn(),
      clearErrors: vi.fn(),
      patch: vi.fn(),
      put: vi.fn(),
      post: vi.fn(),
      delete: vi.fn(),
      defaults: () => ({ reset: vi.fn() }),
    }),
    router: { post: vi.fn(), delete: vi.fn() },
  }
})

describe("Student/Profile", () => {
  const mountProfile = (props: Record<string, unknown> = {}) => mount(Profile, {
    props: { user, ...props },
    global: {
      plugins: [i18n],
      stubs: {
        BaseLayout: { template: "<div><slot /></div>" },
        BaseModal: {
          props: ["open", "title"],
          template: '<section v-if="open"><h2>{{ title }}</h2><slot /></section>',
        },
        OnboardingBanner: true,
        ProfileProgress: true,
        StudentProfileEditModal: true,
        StudentDeleteAccountModal: true,
      },
    },
  })

  it("renders sidebar and profile sections", () => {
    const wrapper = mountProfile()

    expect(wrapper.text()).toContain("Jan")
    expect(wrapper.text()).toContain("Edit profile")
    expect(wrapper.text()).toContain("Technical skills")
    expect(wrapper.text()).toContain("Expected work mode")
  })

  it("opens a dedicated skills modal from the skills section", async () => {
    const wrapper = mountProfile()

    const addButton = wrapper.findAll("button").find((button) => button.text() === "Add")
    await addButton?.trigger("click")

    expect(wrapper.text()).toContain("Add technical skills")
    expect(wrapper.find("#profile_skills").exists()).toBe(true)
  })

  it("opens a dedicated work mode modal from the work mode section", async () => {
    const wrapper = mountProfile()

    const editButton = wrapper.findAll("button").find((button) => button.text() === "Edit")
    await editButton?.trigger("click")

    expect(wrapper.text()).toContain("Edit expected work mode")
    expect(wrapper.text()).toContain("On-site")
    expect(wrapper.text()).toContain("Remote")
    expect(wrapper.text()).toContain("Hybrid")
  })

  it("converts on-site and remote selection into hybrid work mode", async () => {
    const wrapper = mountProfile({ user: { ...user, work_modes: [] } })

    const editButton = wrapper.findAll("button").find((button) => button.text() === "Edit")
    await editButton?.trigger("click")

    const onSiteButton = wrapper.findAll("button").find((button) => button.text() === "On-site")
    const remoteButton = wrapper.findAll("button").find((button) => button.text() === "Remote")

    await remoteButton?.trigger("click")
    await onSiteButton?.trigger("click")
    await nextTick()

    expect(onSiteButton?.attributes("aria-pressed")).toBe("false")
    expect(remoteButton?.attributes("aria-pressed")).toBe("false")
    expect(wrapper.findAll("button").find((button) => button.text() === "Hybrid")?.attributes("aria-pressed")).toBe("true")
  })
})
