<script setup>
import { ref, onMounted } from 'vue'

const backgroundNotes = ref([])

function generateRandomNote() {
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

function getEffectiveRadiusPercent(note) {
  const baseRadiusPercent = 4.5
  return baseRadiusPercent * note.size
}

function generateNonOverlappingNotes(
  desiredNoteCount,
  maxPlacementAttemptsPerNote = 200,
) {
  const noteList = []

  for (let noteIndex = 0; noteIndex < desiredNoteCount; noteIndex++) {
    let isPlaced = false
    let attemptCount = 0

    while (!isPlaced && attemptCount < maxPlacementAttemptsPerNote) {
      attemptCount++
      const candidateNote = generateRandomNote()

      if (
        candidateNote.x < 4 ||
        candidateNote.x > 96 ||
        candidateNote.y < 4 ||
        candidateNote.y > 96
      ) {
        continue
      }

      const candidateRadius = getEffectiveRadiusPercent(candidateNote)

      let isPositionValid = true
      for (
        let existingIndex = 0;
        existingIndex < noteList.length;
        existingIndex++
      ) {
        const existingNote = noteList[existingIndex]
        const combinedRadius =
          candidateRadius + getEffectiveRadiusPercent(existingNote)
        if (
          squaredDistance(candidateNote, existingNote) <
          combinedRadius * combinedRadius
        ) {
          isPositionValid = false
          break
        }
      }

      if (isPositionValid) {
        noteList.push(candidateNote)
        isPlaced = true
      }
    }

    if (!isPlaced) {
      continue
    }
  }

  return noteList
}

onMounted(() => {
  const noteCount = Math.floor(Math.random() * 10) + 70
  backgroundNotes.value = generateNonOverlappingNotes(noteCount)
})
</script>

<template>
  <div class="absolute inset-0 pointer-events-none overflow-hidden -z-10">
    <div v-for="(note, index) in backgroundNotes" :key="index" class="absolute background-note" :style="{
      left: note.x + '%',
      top: note.y + '%',
      transform: `translate(-50%, -50%) rotate(${note.rotation}deg) scale(${note.size})`,
      opacity: note.opacity,
      animationDelay: note.delay + 's',
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
.background-note {
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
