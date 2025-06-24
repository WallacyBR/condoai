<template>
  <div class="space-y-4">
    <div
      @drop="handleDrop"
      @dragover.prevent
      @dragenter.prevent
      @dragleave="handleDragLeave"
      @click="triggerFileInput"
      :class="[
        'w-full h-48 border-2 border-dashed rounded-lg flex flex-col items-center justify-center cursor-pointer transition-all duration-200',
        isDragging
          ? 'border-blue-500 bg-blue-500/10 scale-105'
          : 'border-gray-600/50 bg-gray-800/20 hover:border-gray-500 hover:bg-gray-800/30'
      ]"
    >
      <div class="text-center">
        <svg class="h-12 w-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
        </svg>
        <p class="text-lg font-medium text-gray-300 mb-1">
          {{ isDragging ? 'Solte os arquivos aqui' : 'Arraste arquivos aqui' }}
        </p>
        <p class="text-sm text-gray-500">
          ou clique para selecionar arquivos
        </p>
        <p class="text-xs text-gray-600 mt-2">
          {{ acceptText }} - Máximo {{ maxSize }}MB por arquivo
        </p>
      </div>
    </div>

    <input
      ref="fileInput"
      type="file"
      :accept="accept"
      :multiple="multiple"
      @change="handleFileInput"
      class="hidden"
    />

    <div v-if="files.length > 0" class="space-y-2">
      <h4 class="text-sm font-medium text-gray-300">Arquivos selecionados:</h4>
      <div class="space-y-2 max-h-32 overflow-y-auto">
        <div
          v-for="(file, index) in files"
          :key="index"
          class="flex items-center justify-between bg-gray-800/50 rounded-lg p-3 border border-gray-700/50"
        >
          <div class="flex items-center space-x-3">
            <div class="h-8 w-8 rounded bg-blue-500/20 flex items-center justify-center">
              <svg class="h-4 w-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <div>
              <p class="text-sm font-medium text-white">{{ file.name }}</p>
              <p class="text-xs text-gray-400">{{ formatFileSize(file.size) }}</p>
            </div>
          </div>
          <button
            @click="removeFile(index)"
            class="p-1 text-red-400 hover:text-red-300 transition-colors"
            title="Remover arquivo"
          >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <div v-if="error" class="bg-red-500/20 border border-red-500/50 rounded-lg p-3 text-red-300 text-sm">
      <div class="flex items-center space-x-2">
        <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>{{ error }}</span>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, watch } from 'vue'

export default {
  name: 'FileUpload',
  props: {
    modelValue: {
      type: Array,
      default: () => []
    },
    accept: {
      type: String,
      default: '*'
    },
    multiple: {
      type: Boolean,
      default: false
    },
    maxSize: {
      type: Number,
      default: 10
    }
  },
  emits: ['update:modelValue'],
  setup(props, { emit }) {
    const fileInput = ref(null)
    const files = ref([...props.modelValue])
    const isDragging = ref(false)
    const error = ref('')

    const acceptText = computed(() => {
      if (props.accept === '*') return 'Todos os tipos de arquivo'
      return props.accept.replace(/\./g, '').toUpperCase()
    })

    watch(files, (newFiles) => {
      emit('update:modelValue', newFiles)
    }, { deep: true })

    const validateFile = (file) => {
      if (file.size > props.maxSize * 1024 * 1024) {
        throw new Error(`Arquivo "${file.name}" excede o tamanho máximo de ${props.maxSize}MB`)
      }
      if (props.accept !== '*') {
        const accepted = props.accept.split(',').map(t => t.trim())
        const ext = '.' + file.name.split('.').pop().toLowerCase()
        if (!accepted.includes(ext)) {
          throw new Error(`Tipo de arquivo "${ext}" não permitido`)
        }
      }
    }

    const addFiles = (newFiles) => {
      try {
        error.value = ''
        const valid = []
        for (const file of newFiles) {
          validateFile(file)
          if (!files.value.some(f => f.name === file.name && f.size === file.size)) {
            valid.push(file)
          }
        }
        if (props.multiple) {
          files.value.push(...valid)
        } else {
          files.value = valid.slice(0, 1)
        }
      } catch (e) {
        error.value = e.message
      }
    }

    const handleDrop = (e) => {
      e.preventDefault()
      isDragging.value = false
      const droppedFiles = Array.from(e.dataTransfer.files)
      addFiles(droppedFiles)
    }

    const handleDragLeave = (e) => {
      if (!e.currentTarget.contains(e.relatedTarget)) {
        isDragging.value = false
      }
    }

    const triggerFileInput = () => {
      fileInput.value?.click()
    }

    const handleFileInput = (e) => {
      const selected = Array.from(e.target.files || [])
      addFiles(selected)
      e.target.value = ''
    }

    const removeFile = (index) => {
      files.value.splice(index, 1)
    }

    const formatFileSize = (bytes) => {
      if (bytes === 0) return '0 Bytes'
      const k = 1024
      const sizes = ['Bytes', 'KB', 'MB', 'GB']
      const i = Math.floor(Math.log(bytes) / Math.log(k))
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
    }

    return {
      fileInput,
      files,
      isDragging,
      error,
      acceptText,
      handleDrop,
      handleDragLeave,
      triggerFileInput,
      handleFileInput,
      removeFile,
      formatFileSize
    }
  }
}
</script>
