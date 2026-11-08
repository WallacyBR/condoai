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
          ? 'border-cyan-500 bg-cyan-500/10 scale-105'
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

    <div class="flex gap-2 justify-center">
      <NeonButton
        type="button"
        @click="triggerFileInput"
        size="sm"
        variant="info"
        class="flex items-center"
      >
        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
        </svg>
        Selecionar Arquivos
      </NeonButton>
      
      <NeonButton
        v-if="showCameraButton"
        type="button"
        @click="openCamera"
        size="sm"
        variant="success"
        class="flex items-center"
      >
        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        Tirar Foto
      </NeonButton>
    </div>

    <input
      ref="fileInput"
      type="file"
      :accept="accept"
      :multiple="multiple"
      @change="handleFileInput"
      class="hidden"
    />

    <div v-if="files.length > 0" class="space-y-3">
      <h4 class="text-sm font-medium text-gray-300 flex items-center">
        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        Arquivos selecionados ({{ files.length }})
      </h4>
      <div class="space-y-2 max-h-48 overflow-y-auto">
        <div
          v-for="(file, index) in files"
          :key="index"
          class="flex items-center justify-between bg-gray-800/50 rounded-lg p-3 border border-gray-700/50 hover:border-gray-600/50 transition-colors"
        >
          <div class="flex items-center space-x-3">
            <div class="h-10 w-10 rounded bg-blue-500/20 flex items-center justify-center">
              <svg v-if="isImageFile(file)" class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <svg v-else class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-white truncate">{{ file.name }}</p>
              <p class="text-xs text-gray-400">{{ formatFileSize(file.size) }}</p>
            </div>
          </div>
          <div class="flex items-center space-x-2">
            <NeonButton
              @click="previewFile(file)"
              size="sm"
              variant="info"
              class="p-1"
              v-if="isImageFile(file)"
            >
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
            </NeonButton>
            <NeonButton
              @click="removeFile(index)"
              size="sm"
              variant="danger"
              class="p-1"
            >
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </NeonButton>
          </div>
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

    <div v-if="showPreview" class="fixed inset-0 z-50 flex items-center justify-center bg-black/75" @click="closePreview">
      <div class="max-w-2xl max-h-2xl p-4" @click.stop>
        <img :src="previewUrl" alt="Preview" class="max-w-full max-h-full object-contain rounded-lg" />
      </div>
    </div>

    <div v-if="showCamera" class="fixed inset-0 z-50 flex items-center justify-center bg-black/75">
      <div class="bg-gray-900 rounded-lg p-6 max-w-md w-full mx-4">
        <div class="text-center mb-4">
          <h3 class="text-lg font-medium text-white">Capturar Foto</h3>
          <p class="text-sm text-gray-400">Posicione a câmera e tire a foto</p>
        </div>
        
        <div class="space-y-4">
          <div class="relative">
            <video
              v-if="!capturedImage"
              ref="videoRef"
              class="w-full h-64 bg-gray-800 rounded-lg object-cover"
              autoplay
              muted
              playsinline
              preload="metadata"
            ></video>
            
            <img
              v-if="capturedImage"
              :src="capturedImage"
              class="w-full h-64 bg-gray-800 rounded-lg object-cover"
              alt="Foto capturada"
            />
            
            <!-- Indicador de câmera ativa -->
            <div v-if="!capturedImage && !cameraLoading && stream" class="absolute top-3 right-3 flex items-center space-x-2">
              <div class="w-3 h-3 bg-red-500 rounded-full animate-pulse"></div>
              <span class="text-white text-xs bg-black/50 px-2 py-1 rounded-full">AO VIVO</span>
            </div>
            
            <!-- Loading overlay -->
            <div v-if="cameraLoading" class="absolute inset-0 bg-gray-900/80 rounded-lg flex items-center justify-center">
              <div class="text-center">
                <svg class="animate-spin h-8 w-8 text-cyan-400 mx-auto mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-white text-sm">Carregando câmera...</p>
              </div>
            </div>
          </div>
          
          <div class="flex justify-center space-x-2">
            <NeonButton
              v-if="!capturedImage"
              @click="capturePhoto"
              size="sm"
              variant="success"
              class="flex items-center"
            >
              <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              Capturar
            </NeonButton>
            
            <NeonButton
              v-if="capturedImage"
              @click="retakePhoto"
              size="sm"
              variant="secondary"
              class="flex items-center"
            >
              <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              Nova Foto
            </NeonButton>
            
            <NeonButton
              v-if="capturedImage"
              @click="savePhoto"
              size="sm"
              variant="success"
              class="flex items-center"
            >
              <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              Salvar
            </NeonButton>
            
            <NeonButton
              @click="closeCamera"
              size="sm"
              variant="danger"
              class="flex items-center"
            >
              <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
              Cancelar
            </NeonButton>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'
import NeonButton from './NeonButton.vue'

export default {
  name: 'FileUpload',
  components: {
    NeonButton
  },
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
    },
    showCamera: {
      type: Boolean,
      default: true
    }
  },
  emits: ['update:modelValue'],
  setup(props, { emit }) {
    const fileInput = ref(null)
    const videoRef = ref(null)
    const files = ref([...props.modelValue])
    const isDragging = ref(false)
    const error = ref('')
    const showPreview = ref(false)
    const previewUrl = ref('')
    const showCamera = ref(false)
    const capturedImage = ref(null)
    const stream = ref(null)
    const cameraLoading = ref(false)

    const acceptText = computed(() => {
      if (props.accept === '*') return 'Todos os tipos de arquivo'
      return props.accept.replace(/\./g, '').toUpperCase()
    })

    const showCameraButton = computed(() => {
      if (!props.showCamera) return false
      
      const accept = props.accept.toLowerCase()
      return accept.includes('image') || 
             accept.includes('.jpg') || 
             accept.includes('.jpeg') || 
             accept.includes('.png') || 
             accept.includes('.gif') || 
             accept.includes('.webp') ||
             accept === '*'
    })

    watch(files, (newFiles) => {
      emit('update:modelValue', newFiles)
    }, { deep: true })

    const isImageFile = (file) => {
      return file.type.startsWith('image/')
    }

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

    const previewFile = (file) => {
      if (isImageFile(file)) {
        previewUrl.value = URL.createObjectURL(file)
        showPreview.value = true
      }
    }

    const closePreview = () => {
      showPreview.value = false
      if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value)
        previewUrl.value = ''
      }
    }

    const openCamera = async () => {
      try {
        error.value = ''
        cameraLoading.value = true
        showCamera.value = true
        
        // Configurações mais robustas para a câmera
        const constraints = {
          video: {
            facingMode: 'environment', // Câmera traseira primeiro
            width: { ideal: 1280 },
            height: { ideal: 720 }
          }
        }
        
        // Tentar acessar a câmera
        stream.value = await navigator.mediaDevices.getUserMedia(constraints)
        
        // Aguardar o próximo tick para garantir que o DOM foi atualizado
        await nextTick()
        
        if (videoRef.value) {
          videoRef.value.srcObject = stream.value
          
          // Aguardar o video carregar
          videoRef.value.onloadedmetadata = () => {
            cameraLoading.value = false
          }
          
          videoRef.value.onerror = (e) => {
            console.error('Erro no video:', e)
            error.value = 'Erro ao carregar o stream de vídeo'
            cameraLoading.value = false
          }
        } else {
          throw new Error('Elemento de vídeo não encontrado')
        }
        
        capturedImage.value = null
      } catch (err) {
        console.error('Erro ao acessar câmera:', err)
        showCamera.value = false
        cameraLoading.value = false
        
        if (err.name === 'NotAllowedError') {
          error.value = 'Permissão de câmera negada. Por favor, permita o acesso à câmera.'
        } else if (err.name === 'NotFoundError') {
          error.value = 'Nenhuma câmera encontrada no dispositivo.'
        } else if (err.name === 'NotSupportedError') {
          error.value = 'Câmera não suportada neste navegador.'
        } else if (err.name === 'NotReadableError') {
          error.value = 'Câmera está sendo usada por outro aplicativo.'
        } else {
          error.value = 'Erro ao acessar a câmera: ' + err.message
        }
      }
    }

    const capturePhoto = () => {
      if (videoRef.value && stream.value) {
        const canvas = document.createElement('canvas')
        const ctx = canvas.getContext('2d')
        canvas.width = videoRef.value.videoWidth
        canvas.height = videoRef.value.videoHeight
        ctx.drawImage(videoRef.value, 0, 0)
        capturedImage.value = canvas.toDataURL('image/jpeg')
      }
    }

    const retakePhoto = () => {
      capturedImage.value = null
    }

    const savePhoto = () => {
      if (capturedImage.value) {
        fetch(capturedImage.value)
          .then(res => res.blob())
          .then(blob => {
            const file = new File([blob], `camera_${Date.now()}.jpg`, { type: 'image/jpeg' })
            addFiles([file])
            closeCamera()
          })
      }
    }

    const closeCamera = () => {
      showCamera.value = false
      capturedImage.value = null
      if (stream.value) {
        stream.value.getTracks().forEach(track => track.stop())
        stream.value = null
      }
    }

    onUnmounted(() => {
      closeCamera()
      closePreview()
    })

    return {
      fileInput,
      videoRef,
      files,
      isDragging,
      error,
      showPreview,
      previewUrl,
      showCamera,
      capturedImage,
      stream,
      cameraLoading,
      acceptText,
      showCameraButton,
      isImageFile,
      handleDrop,
      handleDragLeave,
      triggerFileInput,
      handleFileInput,
      removeFile,
      formatFileSize,
      previewFile,
      closePreview,
      openCamera,
      capturePhoto,
      retakePhoto,
      savePhoto,
      closeCamera
    }
  }
}
</script>

<style scoped>
/* Estilos específicos para o componente */

/* Remover controles do vídeo */
video {
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
}

video::-webkit-media-controls {
  display: none !important;
}

video::-webkit-media-controls-panel {
  display: none !important;
}

video::-webkit-media-controls-play-button {
  display: none !important;
}

video::-webkit-media-controls-start-playback-button {
  display: none !important;
}

/* Garantir que o vídeo seja apenas um preview */
video {
  pointer-events: none;
  user-select: none;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
}

/* Melhorar aparência do container de vídeo */
.relative {
  overflow: hidden;
}

/* Estilo para o loading overlay */
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>
