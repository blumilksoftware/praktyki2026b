import { mount } from "@vue/test-utils"
import { createI18n } from "vue-i18n"
import { beforeAll, describe, expect, it, vi } from "vitest"
import StudentProfileEditModal from "@/Components/Student/StudentProfileEditModal.vue"
import en from "@/lang/en.json"

const postMock = vi.fn()

vi.mock("@inertiajs/vue3", () => ({
  useForm: (data: Record<string, unknown>) => ({
    ...data,
    processing: false,
    errors: {},
    isDirty: false,
    clearErrors: vi.fn(),
    defaults: () => ({ reset: vi.fn() }),
    patch: vi.fn(),
    post: postMock,
  }),
}))

beforeAll(() => {
  globalThis.URL.createObjectURL = vi.fn(() => "blob://preview") as unknown as typeof URL.createObjectURL
  globalThis.URL.revokeObjectURL = vi.fn() as unknown as typeof URL.revokeObjectURL
})

const i18n = createI18n({ legacy: false, locale: "en", messages: { en } })

describe("StudentProfileEditModal", () => {
  it("shows preview hint after selecting file", async () => {
    const wrapper = mount(StudentProfileEditModal, {
      props: {
        open: true,
        user: { first_name: "Jan", last_name: "Kowalski", email: "jan@example.com" },
      },
      global: {
        plugins: [i18n],
        stubs: {
          BaseModal: {
            props: ["open"],
            template: '<section v-if="open"><slot /></section>',
          },
        },
      },
    })

    const input = wrapper.find('input[type="file"]')
    const file = new File(["x"], "avatar.png", { type: "image/png" })
    Object.defineProperty(input.element, "files", { value: [file] })
    await input.trigger("change")

    expect(wrapper.text()).toContain("Preview")
    expect(postMock).not.toHaveBeenCalled()
  })
})
