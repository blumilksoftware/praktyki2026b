<script setup>
import { ref, computed } from 'vue'
import AdminLayout from '../Components/Admin/AdminLayout.vue'
import AdminGlassSection from '../Components/Admin/AdminGlassSection.vue'
import AdminStatCard from '../Components/Admin/AdminStatCard.vue'

const students = ref(1245)
const approvedCompanies = ref(86)
const approvedUniversities = ref(12)
const activeOffers = ref(42)
const pendingVerifications = ref(7)

const stats = computed(() => [
  { label: 'Aktywni studenci', value: students.value, accent: 'border-t-primary' },
  { label: 'Zatwierdzone firmy', value: approvedCompanies.value, accent: 'border-t-primary' },
  { label: 'Zatwierdzone uczelnie', value: approvedUniversities.value, accent: 'border-t-primary' },
  { label: 'Aktywne oferty', value: activeOffers.value, accent: 'border-t-primary' },
])
</script>

<template>
  <AdminLayout active-page="dashboard">
    <AdminGlassSection class="px-4 py-5 md:px-8 md:py-6 text-center">
      <h1 class="text-2xl font-semibold text-text">
        Witaj w panelu administratora!
      </h1>
      <p class="text-sm text-slate-600 mt-3 max-w-2xl mx-auto leading-relaxed">
        Tutaj możesz zarządzać zgłoszeniami i przeglądać statystyki. Użyj menu po lewej stronie, aby
        nawigować do różnych sekcji panelu.
      </p>
      <a
        href="/admin/zgloszenia"
        class="inline-flex mt-5 items-center gap-2 rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white transition hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/60 focus-visible:ring-offset-2"
      >
        Przejdź do zgłoszeń
      </a>
    </AdminGlassSection>

    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <AdminStatCard
        v-for="stat in stats"
        :key="stat.label"
        :label="stat.label"
        :value="stat.value"
        :accent="stat.accent"
      />
    </section>

    <AdminGlassSection class="px-4 py-5 md:px-8 md:py-6 flex flex-col md:flex-row items-center justify-between gap-4">
      <div class="text-center md:text-left">
        <h2 class="text-sm font-medium text-slate-700">Oczekujące weryfikacje</h2>
        <p class="text-3xl font-bold text-primary mt-1">
          {{ pendingVerifications }}
        </p>
      </div>
      <div class="w-full md:max-w-sm">
        <div
          class="w-full bg-white/40 h-2.5 rounded-full overflow-hidden"
          role="progressbar"
          aria-label="Postęp weryfikacji"
          :aria-valuenow="Math.min((pendingVerifications / 20) * 100, 100)"
          aria-valuemin="0"
          aria-valuemax="100"
        >
          <div
            class="h-2.5 bg-primary rounded-full transition-all duration-500"
            :style="{ width: Math.min((pendingVerifications / 20) * 100, 100) + '%' }"
          />
        </div>
        <p class="text-xs text-slate-500 mt-2 text-center md:text-right">Progres weryfikacji</p>
      </div>
    </AdminGlassSection>
  </AdminLayout>
</template>
