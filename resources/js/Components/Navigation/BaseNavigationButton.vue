<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  label: {
    type: String,
    required: true,
  },
  icon: {
    type: [Object, Function],
    default: null,
  },
  href: {
    type: String,
    default: null,
  },
  isActive: {
    type: Boolean,
    default: false,
  },
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'outline', 'ghost'].includes(value),
  },
})

const emit = defineEmits(['click'])

const handleClick = (event) => {
  emit('click', event)
}

const buttonClasses = computed(() => {
  const baseClasses = {
    'default': {
      active: 'bg-white/20 text-white shadow-sm',
      inactive: 'text-white/80 hover:text-white hover:bg-white/10',
    },
    'outline': {
      active: 'bg-transparent text-white border border-white/50 shadow-sm',
      inactive: 'bg-transparent text-white/80 border border-white/20 hover:text-white hover:border-white/40 hover:bg-white/5',
    },
    'ghost': {
      active: 'bg-white/10 text-white',
      inactive: 'text-white/60 hover:text-white hover:bg-white/5',
    },
  }

  const variantStyles = baseClasses[props.variant] || baseClasses.default
  return props.isActive ? variantStyles.active : variantStyles.inactive
})

const iconClasses = computed(() => {
  return props.isActive ? 'text-white' : 'text-white/70'
})
</script>

<template>
  <Link 
    v-if="href"
    :href="href"
    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-sm font-medium transition-all duration-200 whitespace-nowrap focus:outline-none focus:ring-2 focus:ring-white/30"
    :class="buttonClasses"
    :aria-current="isActive ? 'page' : undefined"
    @click="handleClick"
  >
    <component 
      :is="icon" 
      v-if="icon" 
      stroke="2" 
      class="w-4 h-4 shrink-0" 
      :class="iconClasses"
    />
    <span>{{ label }}</span>
  </Link>
  
  <button 
    v-else
    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-sm font-medium transition-all duration-200 whitespace-nowrap focus:outline-none focus:ring-2 focus:ring-white/30"
    :class="buttonClasses"
    @click="handleClick"
  >
    <component 
      :is="icon" 
      v-if="icon" 
      stroke="2" 
      class="w-4 h-4 shrink-0" 
      :class="iconClasses"
    />
    <span>{{ label }}</span>
  </button>
</template>
