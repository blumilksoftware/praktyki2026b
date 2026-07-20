import { mount } from "@vue/test-utils"
import { createI18n } from "vue-i18n"
import { describe, expect, it, vi } from "vitest"
import StudentAccountSettingsSection from "@/Components/Student/StudentAccountSettingsSection.vue"
import en from "@/lang/en.json"

vi.mock("@inertiajs/vue3", () => ({
  useForm: (data: Record<string, unknown>) => ({
    ...data,
    processing: false,
    errors: {},
    isDirty: false,
    reset: vi.fn(),
    patch: vi.fn(),
    put: vi.fn(),
  }),
  router: { post: vi.fn() },
}))

const i18n = createI18n({ legacy: false, locale: "en", messages: { en } })

describe("StudentAccountSettingsSection", () => {
  it("shows unverified email notice", () => {
    const wrapper = mount(StudentAccountSettingsSection, {
      props: { email: "jan@example.com", emailVerifiedAt: undefined },
      global: { plugins: [i18n], stubs: { StudentDeleteAccountModal: true } },
    })

    expect(wrapper.text()).toContain("inactive until you confirm")
  })
})
