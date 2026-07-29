import { mount } from "@vue/test-utils"
import { describe, expect, it, vi, beforeEach } from "vitest"
import { createI18n } from "vue-i18n"
import Dashboard from "@/Pages/Company/Dashboard.vue"
import en from "@/lang/en.json"

const i18n = createI18n({ legacy: false, locale: "en", messages: { en } })

const { routerVisit, routerPatch, routerDelete } = vi.hoisted(() => ({
  routerVisit: vi.fn(),
  routerPatch: vi.fn(),
  routerDelete: vi.fn(),
}))

vi.mock("@inertiajs/vue3", async () => {
  const actual = await vi.importActual("@inertiajs/vue3")
  return {
    ...actual,
    Head: { template: "<div />" },
    router: { visit: routerVisit, patch: routerPatch, delete: routerDelete },
  }
})

vi.mock("@/Helpers/routes", () => ({
  ROUTES: {
    COMPANY_APPLICATIONS: "/company/applications",
    COMPANY_OFFERS_STORE: "/company/offers",
  },
}))

const baseOffer = {
  id: "offer-1",
  title: "Frontend Intern",
  status: "published",
  spots: 5,
  applications_count: 3,
}

function makeOffers(overrides: Partial<typeof baseOffer>[] = [{}]) {
  return overrides.map((o, i) => ({ ...baseOffer, id: `offer-${i}`, ...o }))
}

describe("Company/Dashboard", () => {
  const mountDashboard = (offers = makeOffers()) =>
    mount(Dashboard, {
      props: { offers },
      global: {
        plugins: [i18n],
        stubs: {
          BaseLayout: { template: "<div><slot /></div>" },
          OnboardingBanner: true,
        },
      },
    })

  beforeEach(() => {
    routerVisit.mockClear()
    routerPatch.mockClear()
    routerDelete.mockClear()
    vi.spyOn(window, "confirm").mockReturnValue(true)
  })

  it("renders offer rows with title, status, spots and applications count", () => {
    const wrapper = mountDashboard(
      makeOffers([{ title: "Backend Intern", spots: 4, applications_count: 7 }]),
    )

    expect(wrapper.text()).toContain("Backend Intern")
    expect(wrapper.text()).toContain("4")
    expect(wrapper.text()).toContain("7")
  })

  it("shows the empty state when there are no offers", () => {
    const wrapper = mountDashboard([])

    expect(wrapper.text()).toContain("No offers yet")
  })

  it("applies the correct status class for each status", () => {
    const wrapper = mountDashboard(
      makeOffers([
        { id: "a", status: "published" },
        { id: "b", status: "draft" },
        { id: "c", status: "closed" },
        { id: "d", status: "expired" },
      ]),
    )

    const badges = wrapper.findAll("td span.rounded-full")
    expect(badges[0].classes()).toContain("bg-green-100")
    expect(badges[1].classes()).toContain("bg-gray-100")
    expect(badges[2].classes()).toContain("bg-red-100")
    expect(badges[3].classes()).toContain("bg-orange-100")
  })

  it("navigates to the applications page filtered by offer on click", async () => {
    const wrapper = mountDashboard(makeOffers([{ id: "offer-42", applications_count: 9 }]))

    await wrapper.find('a[href="/company/applications?offer=offer-42"]').trigger("click")

    expect(routerVisit).toHaveBeenCalledWith("/company/applications?offer=offer-42")
  })

  it("opens the actions menu on kebab click", async () => {
    const wrapper = mountDashboard()

    expect(wrapper.text()).not.toContain("Deactivate")

    const menuButton = wrapper.find('button[aria-label="Open actions menu"]')
    await menuButton.trigger("click")

    expect(wrapper.text()).toContain("Edit")
    expect(wrapper.text()).toContain("Deactivate")
    expect(wrapper.text()).toContain("Delete")
  })

  it("closes the actions menu when clicking outside", async () => {
    const wrapper = mountDashboard()

    await wrapper.find('button[aria-label="Open actions menu"]').trigger("click")
    expect(wrapper.text()).toContain("Deactivate")

    document.body.click()
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).not.toContain("Deactivate")
  })

  it("navigates to the edit page when clicking Edit", async () => {
    const wrapper = mountDashboard(makeOffers([{ id: "offer-5" }]))

    await wrapper.find('button[aria-label="Open actions menu"]').trigger("click")
    const editButton = wrapper.findAll("button").find((btn) => btn.text() === "Edit")
    await editButton!.trigger("click")

    expect(routerVisit).toHaveBeenCalledWith("/company/offers/offer-5/edit")
  })

  it("calls the deactivate endpoint when deactivating a published offer", async () => {
    const wrapper = mountDashboard(makeOffers([{ id: "offer-7", status: "published" }]))

    await wrapper.find('button[aria-label="Open actions menu"]').trigger("click")
    const deactivateButton = wrapper.findAll("button").find((btn) => btn.text() === "Deactivate")
    await deactivateButton!.trigger("click")

    expect(routerPatch).toHaveBeenCalledWith("/company/offers/offer-7/deactivate")
  })

  it("calls the publish endpoint when activating a draft offer", async () => {
    const wrapper = mountDashboard(makeOffers([{ id: "offer-8", status: "draft" }]))

    await wrapper.find('button[aria-label="Open actions menu"]').trigger("click")
    const activateButton = wrapper.findAll("button").find((btn) => btn.text() === "Activate")
    await activateButton!.trigger("click")

    expect(routerPatch).toHaveBeenCalledWith("/company/offers/offer-8/publish")
  })

  it("disables the activate button and does not call the API for a closed offer", async () => {
    const wrapper = mountDashboard(makeOffers([{ id: "offer-11", status: "closed" }]))

    await wrapper.find('button[aria-label="Open actions menu"]').trigger("click")
    const activateButton = wrapper.findAll("button").find((btn) => btn.text() === "Activate")

    expect(activateButton!.attributes("disabled")).toBeDefined()

    await activateButton!.trigger("click")

    expect(routerPatch).not.toHaveBeenCalled()
  })

  it("deletes the offer after confirmation", async () => {
    const wrapper = mountDashboard(makeOffers([{ id: "offer-9" }]))

    await wrapper.find('button[aria-label="Open actions menu"]').trigger("click")
    const deleteButton = wrapper.findAll("button").find((btn) => btn.text() === "Delete")
    await deleteButton!.trigger("click")

    expect(window.confirm).toHaveBeenCalled()
    expect(routerDelete).toHaveBeenCalledWith("/company/offers/offer-9")
  })

  it("does not delete the offer if the confirmation is cancelled", async () => {
    vi.spyOn(window, "confirm").mockReturnValue(false)
    const wrapper = mountDashboard(makeOffers([{ id: "offer-10" }]))

    await wrapper.find('button[aria-label="Open actions menu"]').trigger("click")
    const deleteButton = wrapper.findAll("button").find((btn) => btn.text() === "Delete")
    await deleteButton!.trigger("click")

    expect(routerDelete).not.toHaveBeenCalled()
  })
})
