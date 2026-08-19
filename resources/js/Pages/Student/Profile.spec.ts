import { mount } from "@vue/test-utils"
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
  street: "Szeroka 12",
  postal_code: "59-220",
  city: "Legnica",
  university: "Collegium Witelona",
  university_verified: true,
  study_field: "Computer science",
  study_year: "3",
  specialization: "Web applications",
  preferred_fields: ["IT"],
  preferred_cities: ["Wroclaw"],
  study_field_ids: [],
  cv_path: null,
  skills: ["Python", "Django", "React", "TypeScript"],
  work_modes: ["hybrid", "remote"],
  applications: [],
}

const { routerPatch } = vi.hoisted(() => ({ routerPatch: vi.fn() }))

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
    router: { post: vi.fn(), delete: vi.fn(), patch: routerPatch },
  }
})

describe("Student/Profile", () => {
  const mountProfile = () => mount(Profile, {
    props: { user, workModeOptions: ["onSite", "hybrid", "remote"] },
    global: {
      plugins: [i18n],
      stubs: {
        StudentPanelLayout: { template: "<div><slot /></div>" },
        BaseModal: {
          props: ["open", "title"],
          template: '<section v-if="open"><h2>{{ title }}</h2><slot /></section>',
        },
        OnboardingBanner: true,
        ProfileProgress: true,
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

  it("renders skills and work mode tags from user data", () => {
    const wrapper = mountProfile()

    expect(wrapper.text()).toContain("Python")
    expect(wrapper.text()).toContain("Hybrid")
  })

  it("saves work modes as enum values, not labels, via the shared profile patch endpoint", async () => {
    routerPatch.mockClear()
    const wrapper = mountProfile()

    const editButton = wrapper.findAll("button").find((btn) => btn.text() === "Edit")
    await editButton!.trigger("click")

    const onsiteButton = wrapper.findAll("button").find((btn) => btn.text() === "On-site")
    await onsiteButton!.trigger("click")

    const saveButton = wrapper.findAll("button").find((btn) => btn.text() === "Save")
    await saveButton!.trigger("click")

    expect(routerPatch).toHaveBeenCalledTimes(1)
    const [url, payload] = routerPatch.mock.calls[0]
    expect(url).toBe("/student/profile")
    expect(payload.first_name).toBe("Jan")
    expect(payload.last_name).toBe("Kowalski")
    expect(payload.work_modes).toEqual(["hybrid", "remote", "onSite"])
    expect(payload.skills).toEqual(["Python", "Django", "React", "TypeScript"])
  })

  it("saves skills via the shared profile patch endpoint", async () => {
    routerPatch.mockClear()
    const wrapper = mountProfile()

    const addButton = wrapper.findAll("button").find((btn) => btn.text() === "Add")
    await addButton!.trigger("click")

    const input = wrapper.find("#profile_skills")
    await input.setValue("Vue.js")
    await input.trigger("keydown", { key: "Enter" })

    const saveButton = wrapper.findAll("button").find((btn) => btn.text() === "Save")
    await saveButton!.trigger("click")

    expect(routerPatch).toHaveBeenCalledTimes(1)
    const [url, payload] = routerPatch.mock.calls[0]
    expect(url).toBe("/student/profile")
    expect(payload.skills).toEqual(["Python", "Django", "React", "TypeScript", "Vue.js"])
    expect(payload.work_modes).toEqual(["hybrid", "remote"])
  })

  it("does not show the temporary mock data notice", () => {
    const wrapper = mountProfile()

    expect(wrapper.text()).not.toContain(
      "Data is temporary (mock) and is not saved to the server",
    )
  })

  it("keeps the skills modal open and shows an inline error on validation failure", async () => {
    routerPatch.mockImplementation((_url, _payload, options) => {
      options?.onError?.({ skills: "Too many skills." })
      options?.onFinish?.()
    })

    try {
      const wrapper = mountProfile()
      const addButton = wrapper.findAll("button").find((btn) => btn.text() === "Add")
      await addButton!.trigger("click")

      const saveButton = wrapper.findAll("button").find((btn) => btn.text() === "Save")
      await saveButton!.trigger("click")

      expect(wrapper.text()).toContain("Add technical skills")
      expect(wrapper.text()).toContain("Too many skills.")
    } finally {
      routerPatch.mockReset()
    }
  })
})
