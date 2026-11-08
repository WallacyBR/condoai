<template>
  <div class="bg-gray-800/40 rounded-xl p-6 border border-gray-700/50">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-semibold text-cyan-300">{{ title }}</h3>
      <div class="flex gap-2">
        <button
          v-if="allowExport"
          @click="exportChart"
          class="px-3 py-1 text-xs bg-blue-600/20 text-blue-300 rounded-lg hover:bg-blue-600/30"
        >
          <i class="fas fa-download mr-1"></i>
          Exportar
        </button>
        <button
          v-if="allowFullscreen"
          @click="toggleFullscreen"
          class="px-3 py-1 text-xs bg-gray-600/20 text-gray-300 rounded-lg hover:bg-gray-600/30"
        >
          <i class="fas fa-expand mr-1"></i>
          Expandir
        </button>
      </div>
    </div>
    
    <div v-if="loading" class="flex justify-center items-center h-64">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-cyan-300"></div>
    </div>
    
    <div v-else-if="error" class="flex flex-col items-center justify-center h-64 text-red-400">
      <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
      <p>Erro ao carregar gráfico</p>
    </div>
    
    <div v-else :class="{ 'h-64': !fullscreen, 'h-96': fullscreen }">
      <canvas ref="chartCanvas" :class="{ 'max-h-64': !fullscreen, 'max-h-96': fullscreen }"></canvas>
    </div>

    <!-- Chart Info -->
    <div v-if="showInfo && chartInfo" class="mt-4 p-3 bg-gray-700/30 rounded-lg">
      <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
        <div v-for="(value, key) in chartInfo" :key="key">
          <span class="text-gray-400">{{ formatInfoKey(key) }}:</span>
          <span class="text-white ml-1">{{ value }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'

const props = defineProps({
  title: { type: String, required: true },
  chartType: { type: String, default: 'bar' },
  data: { type: Object, required: true },
  options: { type: Object, default: () => ({}) },
  loading: { type: Boolean, default: false },
  error: { type: Boolean, default: false },
  allowExport: { type: Boolean, default: true },
  allowFullscreen: { type: Boolean, default: true },
  showInfo: { type: Boolean, default: false },
  chartInfo: { type: Object, default: null }
})

const emit = defineEmits(['chart-ready', 'chart-click'])

const chartCanvas = ref(null)
const fullscreen = ref(false)
let chartInstance = null

const defaultOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      labels: { color: '#d1d5db' }
    },
    tooltip: {
      backgroundColor: 'rgba(31, 41, 55, 0.95)',
      titleColor: '#06b6d4',
      bodyColor: '#ffffff',
      borderColor: '#374151',
      borderWidth: 1
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      ticks: { color: '#d1d5db' },
      grid: { color: '#374151' }
    },
    x: {
      ticks: { color: '#d1d5db' },
      grid: { color: '#374151' }
    }
  },
  onClick: (event, elements) => {
    if (elements.length > 0) {
      emit('chart-click', elements[0])
    }
  }
}

const createChart = async () => {
  if (!chartCanvas.value || props.loading || props.error) return

  try {
    const Chart = (await import('chart.js/auto')).default
    
    if (chartInstance) {
      chartInstance.destroy()
    }

    const mergedOptions = {
      ...defaultOptions,
      ...props.options,
      plugins: {
        ...defaultOptions.plugins,
        ...props.options.plugins
      },
      scales: props.chartType === 'doughnut' || props.chartType === 'pie' ? 
        undefined : {
          ...defaultOptions.scales,
          ...props.options.scales
        }
    }

    chartInstance = new Chart(chartCanvas.value, {
      type: props.chartType,
      data: props.data,
      options: mergedOptions
    })

    emit('chart-ready', chartInstance)
  } catch (error) {
    console.error('Error creating chart:', error)
  }
}

const exportChart = () => {
  if (!chartInstance) return

  const link = document.createElement('a')
  link.download = `${props.title.toLowerCase().replace(/\s+/g, '-')}.png`
  link.href = chartInstance.toBase64Image()
  link.click()
}

const toggleFullscreen = () => {
  fullscreen.value = !fullscreen.value
  setTimeout(() => {
    if (chartInstance) {
      chartInstance.resize()
    }
  }, 100)
}

const formatInfoKey = (key) => {
  return key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

watch(() => props.data, () => {
  createChart()
}, { deep: true })

watch(() => props.loading, (newVal) => {
  if (!newVal) {
    setTimeout(createChart, 100)
  }
})

onMounted(() => {
  if (!props.loading) {
    createChart()
  }
})

onUnmounted(() => {
  if (chartInstance) {
    chartInstance.destroy()
  }
})
</script> 