import { mount } from "@vue/test-utils"
import { createI18n } from "vue-i18n"
import { describe, expect, it, vi } from "vitest"
import StudentDeleteAccountModal from "@/Components/Student/StudentDeleteAccountModal.vue"
import en from "@/lang/en.json"

vi.mock("@inertiajs/vue3", () => ({
  useForm: () => ({
    password: "",
    confirmation: false,
    processing: false,
    errors: {},
    delete: vi.fn(),
  }),
}))

const i18n = createI18n({ legacy: false, locale: "en", messages: { en } })

describe("StudentDeleteAccountModal", () => {
  it("disables delete button initially", () => {
    const wrapper = mount(StudentDeleteAccountModal, {
      props: { open: true },
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

    const buttons = wrapper.findAll("button")
    const deleteBtn = buttons[buttons.length - 1]
    expect(deleteBtn.attributes("disabled")).toBeDefined()
  })
})
