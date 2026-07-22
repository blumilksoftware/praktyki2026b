import { mount } from "@vue/test-utils"
import { createI18n } from "vue-i18n"
import { describe, expect, it } from "vitest"
import ProfileTagInput from "@/Components/Profile/ProfileTagInput.vue"
import en from "@/lang/en.json"

const i18n = createI18n({
  legacy: false,
  locale: "en",
  messages: { en },
})

describe("ProfileTagInput", () => {
  it("adds tag on Enter", async () => {
    const wrapper = mount(ProfileTagInput, {
      props: { id: "fields", label: "Fields", modelValue: [] },
      global: { plugins: [i18n] },
    })

    const input = wrapper.find("input")
    await input.setValue("Python")
    await input.trigger("keydown", { key: "Enter" })

    expect(wrapper.emitted("update:modelValue")?.[0]?.[0]).toEqual(["Python"])
  })

  it("removes last tag on Backspace when input empty", async () => {
    const wrapper = mount(ProfileTagInput, {
      props: { id: "fields", label: "Fields", modelValue: ["JS"] },
      global: { plugins: [i18n] },
    })

    const input = wrapper.find("input")
    await input.trigger("keydown", { key: "Backspace" })

    expect(wrapper.emitted("update:modelValue")?.[0]?.[0]).toEqual([])
  })
})
