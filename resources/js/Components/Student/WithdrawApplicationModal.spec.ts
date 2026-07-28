import { mount } from "@vue/test-utils"
import { createI18n } from "vue-i18n"
import { describe, expect, it } from "vitest"
import WithdrawApplicationModal from "@/Components/Student/WithdrawApplicationModal.vue"
import en from "@/lang/en.json"

const i18n = createI18n({ legacy: false, locale: "en", messages: { en } })

function mountModal(props = {}) {
  return mount(WithdrawApplicationModal, {
    props: {
      open: true,
      offerTitle: "Frontend Intern",
      ...props,
    },
    global: {
      plugins: [i18n],
      stubs: {
        BaseModal: {
          props: ["open", "title"],
          emits: ["close"],
          template: `
            <section v-if="open">
              <h2>{{ title }}</h2>
              <slot />
            </section>
          `,
        },
      },
    },
  })
}

describe("WithdrawApplicationModal", () => {
  it("shows offer title in confirmation", () => {
    const wrapper = mountModal()

    expect(wrapper.text()).toContain("Frontend Intern")
  })

  it("emits confirm when confirm button is clicked", async () => {
    const wrapper = mountModal()
    const buttons = wrapper.findAll("button")
    const confirmBtn = buttons[buttons.length - 1]

    await confirmBtn.trigger("click")

    expect(wrapper.emitted("confirm")).toBeTruthy()
  })

  it("emits close when cancel button is clicked", async () => {
    const wrapper = mountModal()
    const buttons = wrapper.findAll("button")
    const cancelBtn = buttons[0]

    await cancelBtn.trigger("click")

    expect(wrapper.emitted("close")).toBeTruthy()
  })
})
