<script setup>
import { ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import BaseInput from '@/Components/Base/BaseInput.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import BaseCheckbox from '@/Components/Base/BaseCheckbox.vue'

const email = ref('student@example.com')
const password = ref('secret')
const inputWithError = ref('')
const terms = ref(false)
</script>

<template>
  <Head title="Components" />
  <div class="min-h-screen bg-secondary px-4 py-10">
    <div class="mx-auto w-full max-w-4xl flex flex-col gap-10">
      <header class="text-center">
        <h1 class="text-3xl md:text-4xl font-normal text-text">
          Components preview
        </h1>
        <p class="mt-3 text-sm text-additional">
          Developer page for previewing reusable components.
        </p>
      </header>
      <section class="flex flex-col gap-4">
        <h2 class="text-xl font-medium text-text">
          BaseInput
        </h2>
        <div class="rounded-lg border border-border bg-white p-6 flex flex-col gap-6">
          <BaseInput
            id="showcase-email"
            v-model="email"
            type="email"
            autocomplete="email"
            label="E-mail"
          />
          <BaseInput
            id="showcase-password"
            v-model="password"
            type="password"
            autocomplete="current-password"
            label="Password"
          />
          <BaseInput
            id="showcase-error"
            v-model="inputWithError"
            type="text"
            label="E-mail"
            error="Sample validation error"
          />
        </div>
      </section>
      <section class="flex flex-col gap-4">
        <h2 class="text-xl font-medium text-text">
          BaseButton
        </h2>
        <div class="rounded-lg border border-border bg-white p-6 flex flex-col gap-4">
          <BaseButton type="button">
            Primary button
          </BaseButton>
          <BaseButton type="button" variant="outline">
            Outline button
          </BaseButton>
          <BaseButton type="button" disabled>
            Disabled button
          </BaseButton>
        </div>
      </section>
      <section class="flex flex-col gap-4">
        <h2 class="text-xl font-medium text-text">
          BaseCheckbox
        </h2>
        <div class="rounded-lg border border-border bg-white p-6">
          <BaseCheckbox id="showcase-terms" v-model="terms">
            I accept the terms
          </BaseCheckbox>
        </div>
      </section>
    </div>
  </div>
</template>
