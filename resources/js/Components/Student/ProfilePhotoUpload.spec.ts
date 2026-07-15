import { mount } from "@vue/test-utils"
import { createI18n } from "vue-i18n"
import { beforeAll, describe, expect, it, vi } from "vitest"
import ProfilePhotoUpload from "@/Components/Student/ProfilePhotoUpload.vue"
import en from "@/lang/en.json"

beforeAll(() => {
  globalThis.URL.createObjectURL = vi.fn(() => "blob://preview") as unknown as typeof URL.createObjectURL
  globalThis.URL.revokeObjectURL = vi.fn() as unknown as typeof URL.revokeObjectURL
})

const i18n = createI18n({ legacy: false, locale: "en", messages: { en } })

describe("ProfilePhotoUpload", () => {
  it("shows preview hint after selecting file", async () => {
    const wrapper = mount(ProfilePhotoUpload, {
      props: {
        photoUrl: null,
        firstName: "John",
        lastName: "Doe",
        pendingFile: null,
        "onUpdate:pendingFile": (file: File | null) => wrapper.setProps({ pendingFile: file }),
      },
      global: {
        plugins: [i18n],
      },
    })

    const input = wrapper.find('input[type="file"]')
    const file = new File(["x"], "avatar.png", { type: "image/png" })
    Object.defineProperty(input.element, "files", { value: [file] })
    await input.trigger("change")

    expect(wrapper.text()).toContain("Preview")
    expect(wrapper.text()).toContain("Upload a file")
  })
})
