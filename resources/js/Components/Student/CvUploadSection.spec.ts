import { mount } from "@vue/test-utils"
import type { VueWrapper } from "@vue/test-utils"
import { nextTick } from "vue"
import { beforeEach, describe, expect, it, vi } from "vitest"
import CvUploadSection from "@/Components/Student/CvUploadSection.vue"

const post = vi.fn()
const deleteRequest = vi.fn()
const reset = vi.fn()

const mockFormState = {
  errors: {} as Record<string, string>,
  processing: false,
}

vi.mock("@inertiajs/vue3", async () => {
  const actual = await vi.importActual<typeof import("@inertiajs/vue3")>("@inertiajs/vue3")

  return {
    ...actual,
    useForm: () => ({
      cv: null,
      get errors() {
        return mockFormState.errors
      },
      get processing() {
        return mockFormState.processing
      },
      post,
      delete: deleteRequest,
      reset,
    }),
  }
})

function mountComponent(props: Record<string, unknown> = {}) {
  return mount(CvUploadSection, {
    props: {
      cvPath: null,
      ...props,
    },
  })
}

async function selectFile(wrapper: VueWrapper, file: File) {
  const input = wrapper.find('input[type="file"]')

  Object.defineProperty(input.element, "files", {
    value: [file],
    configurable: true,
  })

  await input.trigger("change")
}

describe("CvUploadSection", () => {
  beforeEach(() => {
    Object.defineProperty(URL, "createObjectURL", {
      value: vi.fn(() => "blob:cv-preview"),
      configurable: true,
    })
    Object.defineProperty(URL, "revokeObjectURL", {
      value: vi.fn(),
      configurable: true,
    })
    document.body.innerHTML = ""
    mockFormState.errors = {}
    mockFormState.processing = false
    post.mockReset()
    deleteRequest.mockReset()
    reset.mockReset()
  })

  it("shows accepted format and size limit before file selection", () => {
    const wrapper = mountComponent()

    expect(wrapper.text()).toContain("Accepted format: PDF")
    expect(wrapper.text()).toContain("Maximum size: 5 MB")
    expect(wrapper.text()).toContain("Choose PDF file")
  })

  it("shows filename and replace option after upload", async () => {
    post.mockImplementation((_url: string, options: { onSuccess: () => void }) => options.onSuccess())
    const wrapper = mountComponent()
    const file = new File(["cv"], "Jan_Kowalski_CV.pdf", { type: "application/pdf" })

    await selectFile(wrapper, file)
    await nextTick()

    expect(post).toHaveBeenCalledOnce()
    expect(wrapper.text()).toContain("Jan_Kowalski_CV.pdf")
    expect(wrapper.text()).toContain("Preview")
    expect(wrapper.text()).toContain("Replace")
  })

  it("uploads a dropped PDF instead of letting the browser open it", async () => {
    post.mockImplementation((_url: string, options: { onSuccess: () => void }) => options.onSuccess())
    const wrapper = mountComponent()
    const file = new File(["cv"], "Dropped_CV.pdf", { type: "application/pdf" })

    await wrapper.findAll("button").find(button => button.text().includes("Choose PDF file"))?.trigger("drop", {
      dataTransfer: { files: [file] },
    })
    await nextTick()

    expect(post).toHaveBeenCalledOnce()
    expect(wrapper.text()).toContain("Dropped_CV.pdf")
  })

  it("opens a preview modal for an uploaded CV", async () => {
    post.mockImplementation((_url: string, options: { onSuccess: () => void }) => options.onSuccess())
    const wrapper = mountComponent()
    const file = new File(["cv"], "Jan_Kowalski_CV.pdf", { type: "application/pdf" })

    await selectFile(wrapper, file)
    await nextTick()
    await wrapper.findAll("button").find(button => button.text().includes("Preview"))?.trigger("click")
    await nextTick()

    expect(document.body.textContent).toContain("CV preview")
    expect(document.body.querySelector("iframe")?.getAttribute("src")).toBe("blob:cv-preview")
  })

  it("shows upload progress while processing", () => {
    mockFormState.processing = true
    const wrapper = mountComponent()

    expect(wrapper.text()).toContain("Uploading")
    expect(wrapper.find('[role="progressbar"]').exists()).toBe(true)
  })

  it("rejects non-pdf files before upload", async () => {
    const wrapper = mountComponent()
    const file = new File(["image"], "avatar.png", { type: "image/png" })

    await selectFile(wrapper, file)

    expect(post).not.toHaveBeenCalled()
    expect(wrapper.text()).toContain("The file must be a PDF.")
  })

  it("rejects files larger than 5 MB before upload", async () => {
    const wrapper = mountComponent()
    const file = new File([new ArrayBuffer((5 * 1024 * 1024) + 1)], "large.pdf", { type: "application/pdf" })

    await selectFile(wrapper, file)

    expect(post).not.toHaveBeenCalled()
    expect(wrapper.text()).toContain("The file must not be larger than 5 MB.")
  })

  it("shows server-side error", () => {
    mockFormState.errors = { cv: "The cv field must be a file of type: pdf." }
    const wrapper = mountComponent()

    expect(wrapper.text()).toContain("The cv field must be a file of type: pdf.")
  })

  it("sends delete request when delete action is clicked", async () => {
    deleteRequest.mockImplementation((_url: string, options: { onSuccess: () => void }) => options.onSuccess())
    const wrapper = mountComponent({ cvPath: "cvs/existing.pdf" })
    const deleteButton = wrapper.findAll("button").find(button => button.text() === "Delete file")

    await deleteButton?.trigger("click")
    await nextTick()

    expect(deleteRequest).toHaveBeenCalledOnce()
    expect(wrapper.text()).not.toContain("CV.pdf")
  })
})
