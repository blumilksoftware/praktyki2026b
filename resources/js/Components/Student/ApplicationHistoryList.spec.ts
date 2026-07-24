import { mount } from "@vue/test-utils"
import { createI18n } from "vue-i18n"
import { describe, expect, it } from "vitest"
import ApplicationHistoryList from "@/Components/Student/ApplicationHistoryList.vue"
import en from "@/lang/en.json"

const i18n = createI18n({ legacy: false, locale: "en", messages: { en } })

const applications = [
  {
    id: "a1",
    offer_id: "o1",
    offer_title: "Frontend Intern",
    company_name: "Example Corp",
    date_applied: "2026-07-01T10:00:00.000000Z",
    status: "pending",
  },
  {
    id: "a2",
    offer_id: "o2",
    offer_title: "Backend Intern",
    company_name: "Other Corp",
    date_applied: "2026-06-15T10:00:00.000000Z",
    status: "accepted",
  },
]

function mountList(props = {}) {
  return mount(ApplicationHistoryList, {
    props: {
      applications: [],
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

describe("ApplicationHistoryList", () => {
  it("shows empty state when there are no applications", () => {
    const wrapper = mountList()

    expect(wrapper.text()).toContain("You have not applied to any offers yet.")
    expect(wrapper.find("[role='status']").exists()).toBe(true)
  })

  it("renders offer title, company and status badge", () => {
    const wrapper = mountList({ applications })

    expect(wrapper.text()).toContain("Frontend Intern")
    expect(wrapper.text()).toContain("Example Corp")
    expect(wrapper.text()).toContain("Pending")
    expect(wrapper.text()).toContain("Backend Intern")
    expect(wrapper.text()).toContain("Accepted")
  })

  it("shows withdraw only for pending applications", () => {
    const wrapper = mountList({ applications })
    const withdrawButtons = wrapper.findAll("button").filter((button) => button.text().includes("Withdraw"))

    expect(withdrawButtons).toHaveLength(1)
  })

  it("emits withdraw with application after confirm", async () => {
    const wrapper = mountList({ applications })
    const withdrawBtn = wrapper.findAll("button").find((button) => button.text().includes("Withdraw"))

    await withdrawBtn!.trigger("click")

    const confirmBtn = wrapper
      .findAll("button")
      .find((button) => button.text().includes("Withdraw application"))

    await confirmBtn!.trigger("click")

    expect(wrapper.emitted("withdraw")).toBeTruthy()
    expect(wrapper.emitted("withdraw")![0][0]).toMatchObject({
      id: "a1",
      offer_id: "o1",
    })
  })
})
