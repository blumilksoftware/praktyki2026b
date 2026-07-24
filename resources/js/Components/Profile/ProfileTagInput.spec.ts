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

  it("shows the tag limit hint", () => {
    const wrapper = mount(ProfileTagInput, {
      props: {
        id: "skills",
        label: "Skills",
        max: 15,
        modelValue: ["Python"],
      },
      global: { plugins: [i18n] },
    })

    expect(wrapper.text()).toContain("1/15")
    expect(wrapper.text()).toContain("You can add up to 15")
  })

  it("does not add a tag when the max limit is reached", async () => {
    const wrapper = mount(ProfileTagInput, {
      props: {
        id: "skills",
        label: "Skills",
        max: 15,
        modelValue: Array.from({ length: 15 }, (_, index) => `Skill${index + 1}`),
      },
      global: { plugins: [i18n] },
    })

    const input = wrapper.find("input")
    await input.setValue("Extra")
    await input.trigger("keydown", { key: "Enter" })

    expect(wrapper.emitted("update:modelValue")).toBeUndefined()
    expect(wrapper.text()).toContain("Limit reached")
  })
})
