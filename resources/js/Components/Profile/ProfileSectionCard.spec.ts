import { mount } from "@vue/test-utils"
import { describe, expect, it } from "vitest"
import ProfileSectionCard from "@/Components/Profile/ProfileSectionCard.vue"

describe("ProfileSectionCard", () => {
  it("renders title and slot", () => {
    const wrapper = mount(ProfileSectionCard, {
      props: { title: "Technical skills", description: "Desc" },
      slots: { default: "<p>Content</p>" },
    })

    expect(wrapper.text()).toContain("Technical skills")
    expect(wrapper.text()).toContain("Desc")
    expect(wrapper.text()).toContain("Content")
  })
})
