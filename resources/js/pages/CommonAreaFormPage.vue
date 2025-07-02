<template>
  <AuthenticatedLayout :page-title="pageTitle">
    <div class="max-w-6xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-white">{{ pageTitle }}</h1>
          <p class="text-gray-400 mt-1">{{ selectedCondominiumName }}</p>
        </div>
        
        <div class="flex space-x-4">
          <NeonButton 
            @click="goBack" 
            variant="secondary"
            size="lg"
          >
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Voltar
          </NeonButton>
          
          <NeonButton 
            @click="saveDraft" 
            variant="secondary"
            size="lg"
            :loading="savingDraft"
          >
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
            </svg>
            Salvar Rascunho
          </NeonButton>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="flex items-center space-x-3">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
          <span class="text-gray-400">Carregando área comum...</span>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-500/10 border border-red-500/30 rounded-lg p-4 text-red-300">
        <div class="flex items-center space-x-2">
          <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span>{{ error }}</span>
        </div>
      </div>

      <!-- Form -->
      <div v-else class="space-y-6">
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Informações Básicas -->
          <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-xl p-6 border border-gray-700/50">
            <h3 class="text-lg font-semibold text-indigo-300 mb-4 border-b border-indigo-500/30 pb-2">
              Informações Básicas
            </h3>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
              <!-- Nome -->
              <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  Nome da Área Comum *
                </label>
                <input
                  v-model="form.name"
                  type="text"
                  required
                  class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                  placeholder="Ex: Piscina, Salão de Festas, Academia..."
                  :class="{ 'border-red-500': errors.name }"
                />
                <p v-if="errors.name" class="mt-1 text-sm text-red-400">{{ errors.name }}</p>
              </div>

              <!-- Capacidade -->
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  Capacidade Máxima *
                </label>
                <input
                  v-model.number="form.capacity"
                  type="number"
                  min="1"
                  required
                  class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                  placeholder="Número de pessoas"
                  :class="{ 'border-red-500': errors.capacity }"
                />
                <p v-if="errors.capacity" class="mt-1 text-sm text-red-400">{{ errors.capacity }}</p>
              </div>

              <!-- Preço por hora -->
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  Preço por Hora (R$)
                </label>
                <input
                  v-model.number="form.price_per_hour"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                  placeholder="0.00"
                  :class="{ 'border-red-500': errors.price_per_hour }"
                />
                <p v-if="errors.price_per_hour" class="mt-1 text-sm text-red-400">{{ errors.price_per_hour }}</p>
              </div>

              <!-- Descrição -->
              <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  Descrição
                </label>
                <textarea
                  v-model="form.description"
                  rows="3"
                  class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors resize-none"
                  placeholder="Descreva a área comum, suas características e facilidades..."
                  :class="{ 'border-red-500': errors.description }"
                ></textarea>
                <p v-if="errors.description" class="mt-1 text-sm text-red-400">{{ errors.description }}</p>
              </div>
            </div>
          </div>

          <!-- Configurações de Reserva -->
          <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-xl p-6 border border-gray-700/50">
            <h3 class="text-lg font-semibold text-indigo-300 mb-4 border-b border-indigo-500/30 pb-2">
              Configurações de Reserva
            </h3>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
              <!-- Tempo mínimo de reserva -->
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  Tempo Mínimo (horas)
                </label>
                <input
                  v-model.number="form.min_booking_hours"
                  type="number"
                  min="1"
                  max="24"
                  class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                  :class="{ 'border-red-500': errors.min_booking_hours }"
                />
                <p v-if="errors.min_booking_hours" class="mt-1 text-sm text-red-400">{{ errors.min_booking_hours }}</p>
              </div>

              <!-- Tempo máximo de reserva -->
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  Tempo Máximo (horas)
                </label>
                <input
                  v-model.number="form.max_booking_hours"
                  type="number"
                  min="1"
                  max="24"
                  class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                  :class="{ 'border-red-500': errors.max_booking_hours }"
                />
                <p v-if="errors.max_booking_hours" class="mt-1 text-sm text-red-400">{{ errors.max_booking_hours }}</p>
              </div>

              <!-- Configurações gerais -->
              <div class="space-y-3">
                <div class="flex items-center">
                  <input
                    id="active"
                    v-model="form.active"
                    type="checkbox"
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-600 rounded bg-gray-800"
                  />
                  <label for="active" class="ml-2 text-gray-300">Área ativa</label>
                </div>
                
                <div class="flex items-center">
                  <input
                    id="requires_approval"
                    v-model="form.requires_approval"
                    type="checkbox"
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-600 rounded bg-gray-800"
                  />
                  <label for="requires_approval" class="ml-2 text-gray-300">Requer aprovação</label>
                </div>

                <div class="flex items-center">
                  <input
                    id="allow_custom_periods"
                    v-model="form.allow_custom_periods"
                    type="checkbox"
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-600 rounded bg-gray-800"
                  />
                  <label for="allow_custom_periods" class="ml-2 text-gray-300">Permitir reservas personalizadas</label>
                </div>
              </div>
            </div>
          </div>

          <!-- Períodos Padrão de Reserva -->
          <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-xl p-6 border border-gray-700/50">
            <h3 class="text-lg font-semibold text-indigo-300 mb-4 border-b border-indigo-500/30 pb-2">
              Períodos Padrão de Reserva
            </h3>
            
            <p class="text-gray-400 mb-4 text-sm">
              Configure os períodos padrão que estarão disponíveis para reserva. Os preços são calculados automaticamente com base no preço por hora.
            </p>
            
            <div class="space-y-4">
              <!-- Manhã -->
              <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-4 bg-gray-800/30 rounded-lg">
                <div class="flex items-center space-x-2">
                  <input
                    id="morning_enabled"
                    v-model="form.booking_periods.morning.enabled"
                    type="checkbox"
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-600 rounded bg-gray-800"
                  />
                  <label for="morning_enabled" class="text-gray-300 font-medium">Manhã</label>
                </div>
                
                <div>
                  <label class="block text-sm text-gray-400 mb-1">Início</label>
                  <input
                    v-model="form.booking_periods.morning.start_time"
                    type="time"
                    class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                  />
                </div>
                
                <div>
                  <label class="block text-sm text-gray-400 mb-1">Fim</label>
                  <input
                    v-model="form.booking_periods.morning.end_time"
                    type="time"
                    class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                  />
                </div>
                
                <div>
                  <label class="block text-sm text-gray-400 mb-1">Preço (R$)</label>
                  <input
                    v-model.number="form.booking_periods.morning.price"
                    type="number"
                    step="0.01"
                    min="0"
                    class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Automático"
                  />
                </div>
              </div>

              <!-- Tarde -->
              <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-4 bg-gray-800/30 rounded-lg">
                <div class="flex items-center space-x-2">
                  <input
                    id="afternoon_enabled"
                    v-model="form.booking_periods.afternoon.enabled"
                    type="checkbox"
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-600 rounded bg-gray-800"
                  />
                  <label for="afternoon_enabled" class="text-gray-300 font-medium">Tarde</label>
                </div>
                
                <div>
                  <label class="block text-sm text-gray-400 mb-1">Início</label>
                  <input
                    v-model="form.booking_periods.afternoon.start_time"
                    type="time"
                    class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                  />
                </div>
                
                <div>
                  <label class="block text-sm text-gray-400 mb-1">Fim</label>
                  <input
                    v-model="form.booking_periods.afternoon.end_time"
                    type="time"
                    class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                  />
                </div>
                
                <div>
                  <label class="block text-sm text-gray-400 mb-1">Preço (R$)</label>
                  <input
                    v-model.number="form.booking_periods.afternoon.price"
                    type="number"
                    step="0.01"
                    min="0"
                    class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Automático"
                  />
                </div>
              </div>

              <!-- Noite -->
              <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-4 bg-gray-800/30 rounded-lg">
                <div class="flex items-center space-x-2">
                  <input
                    id="evening_enabled"
                    v-model="form.booking_periods.evening.enabled"
                    type="checkbox"
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-600 rounded bg-gray-800"
                  />
                  <label for="evening_enabled" class="text-gray-300 font-medium">Noite</label>
                </div>
                
                <div>
                  <label class="block text-sm text-gray-400 mb-1">Início</label>
                  <input
                    v-model="form.booking_periods.evening.start_time"
                    type="time"
                    class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                  />
                </div>
                
                <div>
                  <label class="block text-sm text-gray-400 mb-1">Fim</label>
                  <input
                    v-model="form.booking_periods.evening.end_time"
                    type="time"
                    class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                  />
                </div>
                
                <div>
                  <label class="block text-sm text-gray-400 mb-1">Preço (R$)</label>
                  <input
                    v-model.number="form.booking_periods.evening.price"
                    type="number"
                    step="0.01"
                    min="0"
                    class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Automático"
                  />
                </div>
              </div>

              <!-- Dia Todo -->
              <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-4 bg-gray-800/30 rounded-lg">
                <div class="flex items-center space-x-2">
                  <input
                    id="full_day_enabled"
                    v-model="form.booking_periods.full_day.enabled"
                    type="checkbox"
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-600 rounded bg-gray-800"
                  />
                  <label for="full_day_enabled" class="text-gray-300 font-medium">Dia Todo</label>
                </div>
                
                <div>
                  <label class="block text-sm text-gray-400 mb-1">Início</label>
                  <input
                    v-model="form.booking_periods.full_day.start_time"
                    type="time"
                    class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                  />
                </div>
                
                <div>
                  <label class="block text-sm text-gray-400 mb-1">Fim</label>
                  <input
                    v-model="form.booking_periods.full_day.end_time"
                    type="time"
                    class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                  />
                </div>
                
                <div>
                  <label class="block text-sm text-gray-400 mb-1">Preço (R$)</label>
                  <input
                    v-model.number="form.booking_periods.full_day.price"
                    type="number"
                    step="0.01"
                    min="0"
                    class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Automático"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Fotos -->
          <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-xl p-6 border border-gray-700/50">
            <h3 class="text-lg font-semibold text-indigo-300 mb-4 border-b border-indigo-500/30 pb-2">
              Fotos da Área Comum
            </h3>
            
            <!-- Foto Principal -->
            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-300 mb-3">
                Foto Principal
              </label>
              <div class="space-y-4">
                <div v-if="form.main_photo" class="relative inline-block">
                  <img 
                    :src="getImageUrl(form.main_photo)" 
                    class="w-40 h-32 object-cover rounded-lg border border-gray-600" 
                    alt="Foto principal"
                  />
                  <button 
                    @click="removeMainPhoto" 
                    type="button"
                    class="absolute -top-2 -right-2 bg-red-500 hover:bg-red-600 text-white rounded-full p-1 transition-colors"
                  >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
                
                <WebcamCapture v-model="mainPhotoCapture" />
              </div>
            </div>

            <!-- Fotos Adicionais -->
            <div>
              <label class="block text-sm font-medium text-gray-300 mb-3">
                Fotos Adicionais (máximo 5)
              </label>
              
              <!-- Fotos existentes -->
              <div v-if="form.additional_photos && form.additional_photos.length > 0" class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                <div 
                  v-for="(photo, index) in form.additional_photos" 
                  :key="index"
                  class="relative"
                >
                  <img 
                    :src="getImageUrl(photo)" 
                    class="w-full h-24 object-cover rounded-lg border border-gray-600" 
                    alt="Foto adicional"
                  />
                  <button 
                    @click="removeAdditionalPhoto(index)" 
                    type="button"
                    class="absolute -top-2 -right-2 bg-red-500 hover:bg-red-600 text-white rounded-full p-1 transition-colors"
                  >
                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              </div>

              <!-- Adicionar nova foto -->
              <div v-if="!form.additional_photos || form.additional_photos.length < 5">
                <WebcamCapture v-model="additionalPhotoCapture" />
              </div>
            </div>
          </div>

          <!-- Horários de Funcionamento -->
          <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-xl p-6 border border-gray-700/50">
            <h3 class="text-lg font-semibold text-indigo-300 mb-4 border-b border-indigo-500/30 pb-2">
              Horários de Funcionamento
            </h3>
            
            <div class="space-y-3">
              <div 
                v-for="(day, dayIndex) in weekDays" 
                :key="dayIndex"
                class="flex flex-col lg:flex-row lg:items-center lg:space-x-4 p-3 bg-gray-800/30 rounded-lg space-y-3 lg:space-y-0"
              >
                <div class="flex items-center space-x-2 min-w-0 flex-1">
                  <input
                    :id="`day-${dayIndex}`"
                    v-model="form.availability_schedule[dayIndex].enabled"
                    type="checkbox"
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-600 rounded bg-gray-800"
                  />
                  <label :for="`day-${dayIndex}`" class="text-gray-300 font-medium min-w-0 flex-1">
                    {{ day }}
                  </label>
                </div>
                
                <div v-if="form.availability_schedule[dayIndex].enabled" class="flex items-center space-x-2">
                  <input
                    v-model="form.availability_schedule[dayIndex].start_time"
                    type="time"
                    class="px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                  />
                  <span class="text-gray-400">até</span>
                  <input
                    v-model="form.availability_schedule[dayIndex].end_time"
                    type="time"
                    class="px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Configuração de Tipo de Reserva por Dia -->
          <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-xl p-6 border border-gray-700/50">
            <h3 class="text-lg font-semibold text-indigo-300 mb-4 border-b border-indigo-500/30 pb-2">
              Tipo de Reserva por Dia da Semana
            </h3>
            
            <p class="text-gray-400 mb-4 text-sm">
              Configure como as reservas funcionam em cada dia da semana. Você pode escolher entre reservas por hora ou por período (manhã, tarde, noite, dia todo).
            </p>
            
            <div class="space-y-4">
              <div 
                v-for="(day, dayIndex) in weekDays" 
                :key="dayIndex"
                class="p-4 bg-gray-800/30 rounded-lg border border-gray-700/50"
              >
                <!-- Cabeçalho do dia -->
                <div class="flex items-center justify-between mb-3">
                  <div class="flex items-center space-x-3">
                    <input
                      :id="`booking-day-${dayIndex}`"
                      v-model="form.booking_schedule[dayIndex].enabled"
                      type="checkbox"
                      class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-600 rounded bg-gray-800"
                    />
                    <label :for="`booking-day-${dayIndex}`" class="text-gray-300 font-medium">
                      {{ day }}
                    </label>
                  </div>
                  
                  <div v-if="form.booking_schedule[dayIndex].enabled" class="flex items-center space-x-3">
                    <div class="flex items-center space-x-2">
                      <input
                        :id="`hourly-${dayIndex}`"
                        v-model="form.booking_schedule[dayIndex].type"
                        type="radio"
                        value="hourly"
                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-600 bg-gray-800"
                      />
                      <label :for="`hourly-${dayIndex}`" class="text-gray-300 text-sm">
                        Por Hora
                      </label>
                    </div>
                    
                    <div class="flex items-center space-x-2">
                      <input
                        :id="`period-${dayIndex}`"
                        v-model="form.booking_schedule[dayIndex].type"
                        type="radio"
                        value="period"
                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-600 bg-gray-800"
                      />
                      <label :for="`period-${dayIndex}`" class="text-gray-300 text-sm">
                        Por Período
                      </label>
                    </div>
                  </div>
                </div>
                
                <!-- Descrição do tipo selecionado -->
                <div v-if="form.booking_schedule[dayIndex].enabled" class="ml-7">
                  <div v-if="form.booking_schedule[dayIndex].type === 'hourly'" class="p-3 bg-blue-500/10 border border-blue-500/30 rounded-lg">
                    <div class="flex items-start space-x-2">
                      <svg class="h-5 w-5 text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      <div>
                        <p class="text-blue-300 font-medium mb-1">Reserva por Hora</p>
                        <p class="text-blue-200 text-sm">
                          O morador poderá selecionar de {{ form.min_booking_hours }} a {{ form.max_booking_hours }} horas neste dia.
                        </p>
                        <p class="text-blue-200 text-sm mt-1">
                          Preço: R$ {{ form.price_per_hour || 0 }},00 por hora
                        </p>
                      </div>
                    </div>
                  </div>
                  
                  <div v-else-if="form.booking_schedule[dayIndex].type === 'period'" class="p-3 bg-green-500/10 border border-green-500/30 rounded-lg">
                    <div class="flex items-start space-x-2">
                      <svg class="h-5 w-5 text-green-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                      <div>
                        <p class="text-green-300 font-medium mb-1">Reserva por Período</p>
                        <p class="text-green-200 text-sm">
                          O morador poderá escolher os períodos: 
                          <span v-if="form.booking_periods.morning.enabled" class="font-medium">Manhã</span>
                          <span v-if="form.booking_periods.morning.enabled && (form.booking_periods.afternoon.enabled || form.booking_periods.evening.enabled || form.booking_periods.full_day.enabled)">, </span>
                          <span v-if="form.booking_periods.afternoon.enabled" class="font-medium">Tarde</span>
                          <span v-if="form.booking_periods.afternoon.enabled && (form.booking_periods.evening.enabled || form.booking_periods.full_day.enabled)">, </span>
                          <span v-if="form.booking_periods.evening.enabled" class="font-medium">Noite</span>
                          <span v-if="form.booking_periods.evening.enabled && form.booking_periods.full_day.enabled">, </span>
                          <span v-if="form.booking_periods.full_day.enabled" class="font-medium">Dia Todo</span>
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- Dia desabilitado -->
                <div v-else class="ml-7 p-3 bg-gray-500/10 border border-gray-500/30 rounded-lg">
                  <div class="flex items-center space-x-2">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <p class="text-gray-400 text-sm">Dia não disponível para reservas</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Limites de Reserva -->
          <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-xl p-6 border border-gray-700/50">
            <h3 class="text-lg font-semibold text-indigo-300 mb-4 border-b border-indigo-500/30 pb-2">
              Limites de Reserva
            </h3>
            
            <p class="text-gray-400 mb-4 text-sm">
              Configure limites de reserva por apartamento. Deixe em branco para não aplicar limite.
            </p>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <!-- Limite por dia -->
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  Máximo por Dia
                </label>
                <input
                  v-model.number="form.max_bookings_per_day"
                  type="number"
                  min="1"
                  class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                  placeholder="Ex: 2"
                  :class="{ 'border-red-500': errors.max_bookings_per_day }"
                />
                <p v-if="errors.max_bookings_per_day" class="mt-1 text-sm text-red-400">{{ errors.max_bookings_per_day }}</p>
                <p class="mt-1 text-xs text-gray-500">Máximo de reservas por dia por apartamento</p>
              </div>

              <!-- Limite por mês -->
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  Máximo por Mês
                </label>
                <input
                  v-model.number="form.max_bookings_per_month"
                  type="number"
                  min="1"
                  class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                  placeholder="Ex: 10"
                  :class="{ 'border-red-500': errors.max_bookings_per_month }"
                />
                <p v-if="errors.max_bookings_per_month" class="mt-1 text-sm text-red-400">{{ errors.max_bookings_per_month }}</p>
                <p class="mt-1 text-xs text-gray-500">Máximo de reservas por mês por apartamento</p>
              </div>

              <!-- Limite por ano -->
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  Máximo por Ano
                </label>
                <input
                  v-model.number="form.max_bookings_per_year"
                  type="number"
                  min="1"
                  class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                  placeholder="Ex: 50"
                  :class="{ 'border-red-500': errors.max_bookings_per_year }"
                />
                <p v-if="errors.max_bookings_per_year" class="mt-1 text-sm text-red-400">{{ errors.max_bookings_per_year }}</p>
                <p class="mt-1 text-xs text-gray-500">Máximo de reservas por ano por apartamento</p>
              </div>
            </div>
          </div>

          <!-- Períodos de Indisponibilidade -->
          <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-xl p-6 border border-gray-700/50">
            <h3 class="text-lg font-semibold text-indigo-300 mb-4 border-b border-indigo-500/30 pb-2">
              Períodos de Indisponibilidade
            </h3>
            
            <!-- Lista de períodos existentes -->
            <div v-if="form.unavailability_periods && form.unavailability_periods.length > 0" class="space-y-3 mb-4">
              <div 
                v-for="(period, index) in form.unavailability_periods" 
                :key="index"
                class="flex items-center justify-between p-3 bg-red-500/10 border border-red-500/30 rounded-lg"
              >
                <div>
                  <p class="text-white font-medium">{{ period.name }}</p>
                  <p class="text-gray-400 text-sm">
                    {{ formatDate(period.start_date) }} - {{ formatDate(period.end_date) }}
                  </p>
                </div>
                <button 
                  @click="removeUnavailabilityPeriod(index)" 
                  type="button"
                  class="text-red-400 hover:text-red-300 transition-colors"
                >
                  <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1-1H9a1 1 0 00-1 1v1M4 7h16" />
                  </svg>
                </button>
              </div>
            </div>

            <!-- Adicionar novo período -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
              <input
                v-model="newUnavailabilityPeriod.name"
                type="text"
                placeholder="Nome do período (ex: Natal)"
                class="px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
              />
              <input
                v-model="newUnavailabilityPeriod.start_date"
                type="date"
                class="px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
              />
              <input
                v-model="newUnavailabilityPeriod.end_date"
                type="date"
                class="px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
              />
              <NeonButton 
                @click="addUnavailabilityPeriod" 
                type="button"
                :disabled="!canAddUnavailabilityPeriod"
                size="md"
              >
                Adicionar
              </NeonButton>
            </div>
          </div>

          <!-- Regras -->
          <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-xl p-6 border border-gray-700/50">
            <h3 class="text-lg font-semibold text-indigo-300 mb-4 border-b border-indigo-500/30 pb-2">
              Regras de Uso
            </h3>
            
            <textarea
              v-model="form.rules"
              rows="4"
              class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors resize-none"
              placeholder="Descreva as regras de uso da área comum..."
              :class="{ 'border-red-500': errors.rules }"
            ></textarea>
            <p v-if="errors.rules" class="mt-1 text-sm text-red-400">{{ errors.rules }}</p>
          </div>

          <!-- Botões -->
          <div class="flex justify-end space-x-4 pt-6 border-t border-gray-700">
            <NeonButton 
              @click="goBack" 
              type="button" 
              variant="secondary"
              size="lg"
            >
              Cancelar
            </NeonButton>
            <NeonButton 
              type="submit" 
              :loading="loading"
              size="lg"
            >
              {{ area ? 'Atualizar' : 'Criar' }} Área Comum
            </NeonButton>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script>
import { ref, reactive, computed, watch, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import AuthenticatedLayout from '../components/layouts/AuthenticatedLayout.vue';
import NeonButton from '../components/NeonButton.vue';
import WebcamCapture from '../components/WebcamCapture.vue';

export default {
  name: 'CommonAreaFormPage',
  components: { AuthenticatedLayout, NeonButton, WebcamCapture },
  setup() {
    const router = useRouter();
    const route = useRoute();
    
    const loading = ref(false);
    const savingDraft = ref(false);
    const errors = ref({});
    const error = ref('');
    const mainPhotoCapture = ref('');
    const additionalPhotoCapture = ref('');
    const area = ref(null);

    const weekDays = [
      'Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 
      'Quinta-feira', 'Sexta-feira', 'Sábado'
    ];

    const defaultForm = () => ({
      name: '',
      description: '',
      capacity: 1,
      main_photo: null,
      additional_photos: [],
      active: true,
      availability_schedule: weekDays.map(() => ({
        enabled: true,
        start_time: '08:00',
        end_time: '22:00'
      })),
      unavailability_periods: [],
      price_per_hour: null,
      rules: '',
      min_booking_hours: 1,
      max_booking_hours: 8,
      requires_approval: false,
      allow_custom_periods: false,
      booking_periods: {
        morning: { enabled: true, start_time: '08:00', end_time: '12:00', price: null },
        afternoon: { enabled: true, start_time: '12:00', end_time: '18:00', price: null },
        evening: { enabled: true, start_time: '18:00', end_time: '22:00', price: null },
        full_day: { enabled: true, start_time: '08:00', end_time: '22:00', price: null },
      },
      booking_schedule: weekDays.map(() => ({
        enabled: true,
        type: 'period'
      })),
      max_bookings_per_day: null,
      max_bookings_per_month: null,
      max_bookings_per_year: null,
    });

    const form = reactive(defaultForm());

    const newUnavailabilityPeriod = reactive({
      name: '',
      start_date: '',
      end_date: ''
    });

    // Estado do condomínio selecionado
    const selectedCondominiumId = ref(localStorage.getItem('selectedCondominium'));
    const selectedCondominiumName = ref(localStorage.getItem('selectedCondominiumName'));

    // Computed properties
    const pageTitle = computed(() => {
      return area.value ? 'Editar Área Comum' : 'Nova Área Comum';
    });

    const canAddUnavailabilityPeriod = computed(() => {
      return newUnavailabilityPeriod.name && 
             newUnavailabilityPeriod.start_date && 
             newUnavailabilityPeriod.end_date &&
             newUnavailabilityPeriod.start_date <= newUnavailabilityPeriod.end_date;
    });

    // Watch para captura de foto principal
    watch(mainPhotoCapture, (newPhoto) => {
      if (newPhoto) {
        form.main_photo = newPhoto;
        mainPhotoCapture.value = '';
      }
    });

    // Watch para captura de fotos adicionais
    watch(additionalPhotoCapture, (newPhoto) => {
      if (newPhoto) {
        if (!form.additional_photos) {
          form.additional_photos = [];
        }
        if (form.additional_photos.length < 5) {
          form.additional_photos.push(newPhoto);
        }
        additionalPhotoCapture.value = '';
      }
    });

    // Funções
    const loadArea = async () => {
      if (!route.params.id) return;
      
      try {
        loading.value = true;
        errors.value = {};
        
        const token = localStorage.getItem('token');
        const response = await fetch(`/api/common-areas/${route.params.id}`, {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        });

        if (response.ok) {
          area.value = await response.json();
          // Preencher formulário com dados da área
          Object.assign(form, {
            name: area.value.name || '',
            description: area.value.description || '',
            capacity: area.value.capacity || 1,
            main_photo: area.value.main_photo || null,
            additional_photos: area.value.additional_photos || [],
            active: area.value.active !== undefined ? area.value.active : true,
            availability_schedule: area.value.availability_schedule || weekDays.map(() => ({
              enabled: true,
              start_time: '08:00',
              end_time: '22:00'
            })),
            unavailability_periods: area.value.unavailability_periods || [],
            price_per_hour: area.value.price_per_hour || null,
            rules: area.value.rules || '',
            min_booking_hours: area.value.min_booking_hours || 1,
            max_booking_hours: area.value.max_booking_hours || 8,
            requires_approval: area.value.requires_approval || false,
            allow_custom_periods: area.value.allow_custom_periods || false,
            booking_periods: area.value.booking_periods || {
              morning: { enabled: true, start_time: '08:00', end_time: '12:00', price: null },
              afternoon: { enabled: true, start_time: '12:00', end_time: '18:00', price: null },
              evening: { enabled: true, start_time: '18:00', end_time: '22:00', price: null },
              full_day: { enabled: true, start_time: '08:00', end_time: '22:00', price: null },
            },
            booking_schedule: area.value.booking_schedule || weekDays.map(() => ({
              enabled: true,
              type: 'period'
            })),
            max_bookings_per_day: area.value.max_bookings_per_day || null,
            max_bookings_per_month: area.value.max_bookings_per_month || null,
            max_bookings_per_year: area.value.max_bookings_per_year || null,
          });
        } else {
          error.value = 'Erro ao carregar área comum';
        }
      } catch (error) {
        console.error('Erro ao carregar área comum:', error);
        error.value = 'Erro de conexão. Tente novamente.';
      } finally {
        loading.value = false;
      }
    };

    const getImageUrl = (image) => {
      if (!image) return '';
      if (image.startsWith('data:')) return image;
      if (image.startsWith('common-areas/')) return `/storage/${image}`;
      return image;
    };

    const removeMainPhoto = () => {
      form.main_photo = null;
    };

    const removeAdditionalPhoto = (index) => {
      form.additional_photos.splice(index, 1);
    };

    const addUnavailabilityPeriod = () => {
      if (!canAddUnavailabilityPeriod.value) return;
      
      if (!form.unavailability_periods) {
        form.unavailability_periods = [];
      }
      
      form.unavailability_periods.push({
        name: newUnavailabilityPeriod.name,
        start_date: newUnavailabilityPeriod.start_date,
        end_date: newUnavailabilityPeriod.end_date
      });

      newUnavailabilityPeriod.name = '';
      newUnavailabilityPeriod.start_date = '';
      newUnavailabilityPeriod.end_date = '';
    };

    const removeUnavailabilityPeriod = (index) => {
      form.unavailability_periods.splice(index, 1);
    };

    const formatDate = (dateString) => {
      return new Date(dateString).toLocaleDateString('pt-BR');
    };

    const saveDraft = async () => {
      savingDraft.value = true;
      try {
        // Implementar salvamento de rascunho se necessário
        console.log('Salvando rascunho...');
      } catch (error) {
        console.error('Erro ao salvar rascunho:', error);
      } finally {
        savingDraft.value = false;
      }
    };

    const submit = async () => {
      loading.value = true;
      errors.value = {};

      try {
        const token = localStorage.getItem('token');
        const condo = localStorage.getItem('selectedCondominium');
        
        if (!condo) {
          errors.value = { general: 'Nenhum condomínio selecionado' };
          return;
        }

        const url = area.value 
          ? `/api/common-areas/${area.value.id}`
          : '/api/common-areas';
        
        const method = area.value ? 'PUT' : 'POST';
        
        const body = {
          ...form,
          condominium_id: parseInt(condo)
        };

        const response = await fetch(url, {
          method,
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify(body)
        });

        const data = await response.json();

        if (!response.ok) {
          if (data.errors) {
            errors.value = data.errors;
          } else {
            errors.value = { general: data.message || 'Erro ao salvar área comum' };
          }
          return;
        }

        // Redirecionar para a lista de áreas comuns
        router.push({ name: 'areas' });
      } catch (error) {
        console.error('Erro ao salvar área comum:', error);
        errors.value = { general: 'Erro de conexão. Tente novamente.' };
      } finally {
        loading.value = false;
      }
    };

    const goBack = () => {
      router.push({ name: 'areas' });
    };

    // Carregar dados ao montar o componente
    onMounted(() => {
      console.log('Formulário inicializado:', form.booking_schedule);
      loadArea();
    });

    return {
      form,
      loading,
      savingDraft,
      errors,
      error,
      weekDays,
      mainPhotoCapture,
      additionalPhotoCapture,
      newUnavailabilityPeriod,
      selectedCondominiumName,
      area,
      pageTitle,
      canAddUnavailabilityPeriod,
      getImageUrl,
      removeMainPhoto,
      removeAdditionalPhoto,
      addUnavailabilityPeriod,
      removeUnavailabilityPeriod,
      formatDate,
      saveDraft,
      submit,
      goBack
    };
  }
};
</script> 