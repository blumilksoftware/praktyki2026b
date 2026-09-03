import { mount } from "@vue/test-utils"
import { beforeEach, describe, expect, it, vi } from "vitest"
import { useFlashToast } from "@/Composables/useFlashToast"

const { page, toastSuccess, toastError } = vi.hoisted(() => ({
  page: {
    props: undefined as { flash: { status: string | null; error: string | null } } | undefined,
  },
  toastSuccess: vi.fn(),
  toastError: vi.fn(),
}))

vi.mock("@inertiajs/vue3", () => ({
  usePage: () => page,
}))

vi.mock("@/Composables/useToast", () => ({
  useToast: () => ({ toastSuccess, toastError }),
}))

const Host = {
  setup() {
    useFlashToast()

    return () => null
  },
}

describe("useFlashToast", () => {
  beforeEach(() => {
    page.props = { flash: { status: null, error: null } }
    toastSuccess.mockClear()
    toastError.mockClear()
  })

  it("stays quiet while inertia has not published the page yet", async () => {
    page.props = undefined

    const wrapper = mount(Host)
    await wrapper.vm.$nextTick()

    expect(toastSuccess).not.toHaveBeenCalled()
    expect(toastError).not.toHaveBeenCalled()
  })

  it("raises a toast for the flash status of the current page", async () => {
    page.props!.flash.status = "Your offer changes were saved successfully."

    const wrapper = mount(Host)
    await wrapper.vm.$nextTick()

    expect(toastSuccess).toHaveBeenCalledWith("Your offer changes were saved successfully.")
  })

  it("raises a failing toast for the flash error of the current page", async () => {
    page.props!.flash.error = "Only a published offer can be taken down."

    const wrapper = mount(Host)
    await wrapper.vm.$nextTick()

    expect(toastError).toHaveBeenCalledWith("Only a published offer can be taken down.")
  })

  it("stays quiet when the page carries no flash", async () => {
    const wrapper = mount(Host)
    await wrapper.vm.$nextTick()

    expect(toastSuccess).not.toHaveBeenCalled()
    expect(toastError).not.toHaveBeenCalled()
  })
})
