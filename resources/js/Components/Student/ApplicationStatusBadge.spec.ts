import { mount } from "@vue/test-utils"
import { createI18n } from "vue-i18n"
import { describe, expect, it } from "vitest"
import ApplicationStatusBadge from "@/Components/Student/ApplicationStatusBadge.vue"
import en from "@/lang/en.json"

const i18n = createI18n({ legacy: false, locale: "en", messages: { en } })

describe("ApplicationStatusBadge", () => {
  it("renders pending label", () => {
    const wrapper = mount(ApplicationStatusBadge, {
      props: { status: "pending" },
      global: { plugins: [i18n] },
    })

    expect(wrapper.text()).toContain("Pending")
    expect(wrapper.classes().join(" ")).toContain("bg-amber-50")
  })

  it("uses distinct classes for accepted and rejected", () => {
    const accepted = mount(ApplicationStatusBadge, {
      props: { status: "accepted" },
      global: { plugins: [i18n] },
    })
    const rejected = mount(ApplicationStatusBadge, {
      props: { status: "rejected" },
      global: { plugins: [i18n] },
    })

    expect(accepted.classes().join(" ")).toContain("bg-green-50")
    expect(rejected.classes().join(" ")).toContain("bg-red-50")
    expect(accepted.classes().join(" ")).not.toBe(rejected.classes().join(" "))
  })

  it("does not crash on unknown status", () => {
    const wrapper = mount(ApplicationStatusBadge, {
      props: { status: "unknown" },
      global: { plugins: [i18n] },
    })

    expect(wrapper.classes().join(" ")).toContain("bg-slate-50")
  })
})
