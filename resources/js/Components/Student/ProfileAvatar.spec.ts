import { mount } from "@vue/test-utils"
import { describe, expect, it } from "vitest"
import ProfileAvatar from "@/Components/Student/ProfileAvatar.vue"

describe("ProfileAvatar", () => {
  it("shows initials when no image", () => {
    const wrapper = mount(ProfileAvatar, {
      props: { firstName: "Jan", lastName: "Kowalski", photoUrl: undefined },
    })

    expect(wrapper.text()).toContain("JK")
  })
})
