<template>
  <div class="bg-gradient-to-br rounded-2xl p-6 border-2 shadow-xl transition-all duration-300 hover:scale-105 hover:shadow-2xl"
       :class="cardClasses">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-sm font-medium" :class="textClasses.label">{{ label }}</p>
        <p class="text-2xl font-bold" :class="textClasses.value">{{ value }}</p>
      </div>
      <div class="h-12 w-12 rounded-full flex items-center justify-center"
           :class="iconClasses">
        <slot name="icon">
          <svg class="h-6 w-6" :class="textClasses.icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="defaultIconPath" />
          </svg>
        </slot>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'StatsCard',
  props: {
    label: {
      type: String,
      required: true
    },
    value: {
      type: [String, Number],
      required: true
    },
    variant: {
      type: String,
      default: 'primary',
      validator: (value) => ['primary', 'blue', 'orange', 'red', 'green', 'purple'].includes(value)
    },
    defaultIconPath: {
      type: String,
      default: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'
    }
  },
  computed: {
    cardClasses() {
      const variants = {
        primary: 'from-purple-900/50 via-purple-800/50 to-purple-900/50 border-purple-500/30 hover:border-purple-500/50',
        blue: 'from-blue-900/50 via-blue-800/50 to-blue-900/50 border-blue-500/30 hover:border-blue-500/50',
        orange: 'from-orange-900/50 via-orange-800/50 to-orange-900/50 border-orange-500/30 hover:border-orange-500/50',
        red: 'from-red-900/50 via-red-800/50 to-red-900/50 border-red-500/30 hover:border-red-500/50',
        green: 'from-green-900/50 via-green-800/50 to-green-900/50 border-green-500/30 hover:border-green-500/50',
        purple: 'from-purple-900/50 via-purple-800/50 to-purple-900/50 border-purple-500/30 hover:border-purple-500/50'
      };
      return variants[this.variant];
    },
    textClasses() {
      const variants = {
        primary: {
          label: 'text-purple-300',
          value: 'text-purple-100',
          icon: 'text-purple-400'
        },
        blue: {
          label: 'text-blue-300',
          value: 'text-blue-100',
          icon: 'text-blue-400'
        },
        orange: {
          label: 'text-orange-300',
          value: 'text-orange-100',
          icon: 'text-orange-400'
        },
        red: {
          label: 'text-red-300',
          value: 'text-red-100',
          icon: 'text-red-400'
        },
        green: {
          label: 'text-green-300',
          value: 'text-green-100',
          icon: 'text-green-400'
        },
        purple: {
          label: 'text-purple-300',
          value: 'text-purple-100',
          icon: 'text-purple-400'
        }
      };
      return variants[this.variant];
    },
    iconClasses() {
      const variants = {
        primary: 'bg-purple-500/20',
        blue: 'bg-blue-500/20',
        orange: 'bg-orange-500/20',
        red: 'bg-red-500/20',
        green: 'bg-green-500/20',
        purple: 'bg-purple-500/20'
      };
      return variants[this.variant];
    }
  }
};
</script> 