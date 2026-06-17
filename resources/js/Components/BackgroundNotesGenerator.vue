<script setup>
import { ref, onMounted } from 'vue'

const pawPrints = ref([])

function generateRandomPaw() {
  return {
    x: Math.random() * 100,
    y: Math.random() * 100,
    size: Math.random() * 0.6 + 0.9,
    rotation: Math.random() * 360,
    opacity: Math.random() * 0.25 + 0.75,
    delay: Math.random() * 2,
  }
}

function squaredDistance(pointA, pointB) {
  const deltaX = pointA.x - pointB.x
  const deltaY = pointA.y - pointB.y
  return deltaX * deltaX + deltaY * deltaY
}

function getEffectiveRadiusPercent(pawPrint) {
  const baseRadiusPercent = 4.5
  return baseRadiusPercent * pawPrint.size
}

function generateNonOverlappingPawPrints(
  desiredPawCount,
  maxPlacementAttemptsPerPaw = 200,
) {
  const pawPrintList = []

  for (let pawIndex = 0; pawIndex < desiredPawCount; pawIndex++) {
    let isPlaced = false
    let attemptCount = 0

    while (!isPlaced && attemptCount < maxPlacementAttemptsPerPaw) {
      attemptCount++
      const candidatePaw = generateRandomPaw()

      if (
        candidatePaw.x < 4 ||
        candidatePaw.x > 96 ||
        candidatePaw.y < 4 ||
        candidatePaw.y > 96
      ) {
        continue
      }

      const candidateRadius = getEffectiveRadiusPercent(candidatePaw)

      let isPositionValid = true
      for (
        let existingIndex = 0;
        existingIndex < pawPrintList.length;
        existingIndex++
      ) {
        const existingPaw = pawPrintList[existingIndex]
        const combinedRadius =
          candidateRadius + getEffectiveRadiusPercent(existingPaw)
        if (
          squaredDistance(candidatePaw, existingPaw) <
          combinedRadius * combinedRadius
        ) {
          isPositionValid = false
          break
        }
      }

      if (isPositionValid) {
        pawPrintList.push(candidatePaw)
        isPlaced = true
      }
    }

    if (!isPlaced) {
      continue
    }
  }

  return pawPrintList
}

onMounted(() => {
  const pawPrintCount = Math.floor(Math.random() * 10) + 70
  pawPrints.value = generateNonOverlappingPawPrints(pawPrintCount)
})
</script>

<template>
  <div class="absolute inset-0 pointer-events-none overflow-hidden -z-10">
    <div v-for="(paw, index) in pawPrints" :key="index" class="absolute paw-print" :style="{
      left: paw.x + '%',
      top: paw.y + '%',
      transform: `translate(-50%, -50%) rotate(${paw.rotation}deg) scale(${paw.size})`,
      opacity: paw.opacity,
      animationDelay: paw.delay + 's',
    }"
    >
      <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none"
           stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"
           class="icon icon-tabler icons-tabler-notebook notebook-icon text-sky-600"
      >
        <rect x="3" y="3" width="14" height="18" rx="2" fill="currentColor" opacity="0.28" />
        <rect x="5" y="5" width="10" height="12" rx="1" fill="currentColor" opacity="0.15" />
        <path d="M7 7h6" stroke="currentColor" opacity="1" />
        <path d="M7 10h6" stroke="currentColor" opacity="0.9" />
        <path d="M7 13h6" stroke="currentColor" opacity="0.8" />
        <!-- spiral/clip binding -->
        <g fill="currentColor" opacity="1">
          <rect x="2.2" y="5" width="0.9" height="1.8" rx="0.3" />
          <rect x="2.2" y="8" width="0.9" height="1.8" rx="0.3" />
          <rect x="2.2" y="11" width="0.9" height="1.8" rx="0.3" />
        </g>
        <!-- little bookmark tab -->
        <path d="M16 5v3l2-1v10" stroke="currentColor" opacity="0.85" />
      </svg>
    </div>
  </div>
</template>

<style scoped>
.paw-print {
  animation: fadeIn 0.5s ease-in-out forwards;
  opacity: 0;
  filter: drop-shadow(0 1px 2px rgba(2, 6, 23, 0.08));
}

@keyframes fadeIn {
  to {
    opacity: 1;
  }
}
</style>
