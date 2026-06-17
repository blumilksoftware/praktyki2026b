<script setup>
import { ref } from 'vue'

const students = ref(1245)
const approvedCompanies = ref(86)
const approvedUniversities = ref(12)
const activeOffers = ref(42)
const pendingVerifications = ref(7)
</script>

<template>
  <div class="min-h-screen relative flex flex-col bg-white/85 text-slate-900">
    <div class="flex flex-1 min-h-0">
      <aside
        class="hidden md:flex w-72 flex-col isolate bg-linear-to-b from-sky-100/40 to-sky-50/10 shadow-md ring-1 ring-sky-200 p-6 rounded-2xl"
      >
        <div class="mb-6 flex items-center gap-3">
          <div class="h-10 w-10 bg-sky-600 text-white rounded-md flex items-center justify-center font-semibold">
            A
          </div>
          <div>
            <div class="text-2xl text-slate-900 font-semibold">Admin</div>
            <div class="text-xs text-slate-400">Panel zarządzania</div>
          </div>
        </div>
        <nav class="flex-1">
          <ul class="space-y-2">
            <li>
              <a class="flex items-center gap-3 rounded-xl bg-sky-100 px-4 py-3 text-text font-semibold transition hover:bg-sky-200"
                 href="#"
              >
                <svg class="h-5 w-5 text-sky-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18" />
                </svg>
                Dashboard
              </a>
            </li>
            <li>
              <a class="flex items-center gap-3 rounded-xl px-4 py-3 text-text transition hover:bg-sky-50 hover:text-sky-700"
                 href="#"
              >
                <svg class="h-5 w-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3" />
                </svg>
                Zgłoszenia
              </a>
            </li>
            <li>
              <a class="flex items-center gap-3 rounded-xl px-4 py-3 text-text transition hover:bg-sky-50 hover:text-sky-700"
                 href="#"
              >
                <svg class="h-5 w-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2" />
                </svg>
                Ustawienia
              </a>
            </li>
            <li>
              <a class="flex items-center gap-3 rounded-xl px-4 py-3 text-red-600 transition hover:bg-red-50" href="#">
                <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7" />
                </svg>
                Wyloguj
              </a>
            </li>
          </ul>
        </nav>

        <div class="mt-6 pt-6 border-t border-slate-100 flex items-center gap-3">
          <img class="h-12 w-12 rounded-full ring-1 ring-slate-100" src="https://www.gravatar.com/avatar?d=mp&s=48"
               alt="admin"
          >
          <div>
            <p class="font-medium text-slate-900">Admin Name</p>
            <p class="text-sm text-slate-500">Administrator</p>
          </div>
        </div>
      </aside>

      <main
        class="flex-1 overflow-y-auto text-center align-text-center bg-linear-to-b from-slate-100 to-slate-200 p-6 relative"
      >
        <div class="max-w-7xl h-full mx-auto">
          <div class="h-full grid grid-cols-2 grid-rows-4">
            <div
              class="isolate rounded-xl bg-white/20 shadow-lg ring-1 ring-black/5 col-span-2 h-auto col-start-1 p-6 mb-2 flex flex-col"
            >
              <h2 class="text-2xl font-semibold text-slate-900">
                Witaj w panelu administratora!
              </h2>
              <p class="text-sm text-slate-500 mt-2">
                Tutaj możesz zarządzać zgłoszeniami, przeglądać statystyki i
                konfigurować ustawienia systemu. Użyj menu po lewej stronie, aby
                nawigować do różnych sekcji panelu.
              </p>
            </div>
            <div
              class="isolate rounded-xl bg-white/20 shadow-lg ring-1 ring-black/5 col-start-1 row-start-2 p-4 m-2 flex items-center justify-between"
            >
              <div>
                <div class="text-lg text-slate-500">Aktywni studenci</div>
                <div class="text-xl font-bold text-slate-900 mt-1">
                  {{ students }}
                </div>
              </div>
            </div>
            <div
              class="isolate rounded-xl bg-white/20 shadow-lg ring-1 ring-black/5 col-start-2 row-start-2 p-4 m-2 flex items-center justify-between"
            >
              <div>
                <div class="text-lg text-slate-500">Zatwierdzone firmy</div>
                <div class="text-xl font-bold text-slate-900 mt-1">
                  {{ approvedCompanies }}
                </div>
              </div>
            </div>
            <div
              class="isolate rounded-xl bg-white/20 shadow-lg ring-1 ring-black/5 col-start-1 m-2 row-start-3 p-4 flex items-center justify-between"
            >
              <div>
                <div class="text-lg text-slate-500">Zatwierdzone uczelnie</div>
                <div class="text-xl font-bold text-slate-900 mt-1">
                  {{ approvedUniversities }}
                </div>
              </div>
            </div>
            <div
              class="isolate rounded-xl bg-white/20 shadow-lg ring-1 ring-black/5 col-start-2 row-start-3 p-4 m-2 flex items-center justify-between"
            >
              <div>
                <div class="text-lg text-slate-500">Aktywne oferty</div>
                <div class="text-xl font-bold text-slate-900 mt-1">
                  {{ activeOffers }}
                </div>
              </div>
            </div>
            <div
              class="isolate rounded-xl bg-white/20 shadow-lg ring-1 ring-black/5 col-span-2 h-36 m-2 row-start-4 p-4 flex flex-col justify-between"
            >
              <div class="flex items-center justify-between">
                <div>
                  <div class="text-lg text-slate-500">
                    Oczekujące weryfikacje
                  </div>
                  <div class="text-lg font-semibold text-slate-900 mt-1">
                    {{ pendingVerifications }}
                  </div>
                </div>
              </div>
              <div>
                <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden mt-2">
                  <div class="h-2 bg-amber-400 rounded-full" :style="{
                    width:
                      Math.min((pendingVerifications / 20) * 100, 100) + '%',
                  }"
                  />
                </div>
                <div class="text-xs text-slate-400 mt-2">
                  Progres weryfikacji
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>
