import { mount } from "@vue/test-utils"
import { describe, expect, it } from "vitest"
import ProfilePageCard from "@/Components/Profile/ProfilePageCard.vue"

describe("ProfilePageCard", () => {
  it("renders slot content with shared card styles", () => {
    const wrapper = mount(ProfilePageCard, {
      slots: { default: "<p>Card body</p>" },
    })

    expect(wrapper.text()).toContain("Card body")
    expect(wrapper.classes()).toEqual(
      expect.arrayContaining([
        "rounded-xl",
        "border",
        "border-secondary/20",
        "bg-white",
        "p-6",
        "shadow-sm",
        "sm:p-8",
      ]),
    )
  })

  it("centers content when centered prop is true", () => {
    const wrapper = mount(ProfilePageCard, {
      props: { centered: true },
      slots: { default: "<p>Centered</p>" },
    })

    expect(wrapper.classes()).toEqual(
      expect.arrayContaining(["flex", "flex-col", "items-center", "text-center"]),
    )
  })

  it("uses compact padding when compact prop is true", () => {
    const wrapper = mount(ProfilePageCard, {
      props: { compact: true },
      slots: { default: "<p>Compact</p>" },
    })

    expect(wrapper.classes()).toContain("p-5")
    expect(wrapper.classes()).not.toContain("p-6")
  })
})
