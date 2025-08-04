<template>
  <AuthenticatedLayout page-title="Minhas Tarefas de Manutenção Hoje">
    <div class="p-4 sm:p-6 lg:p-8">
      <!-- Header com estatísticas -->
      <div class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
          <!-- Total de Tarefas -->
          <div class="bg-gradient-to-br from-blue-900/50 via-blue-800/50 to-blue-900/50 rounded-2xl p-6 border-2 border-blue-500/30 shadow-xl transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:border-blue-500/50">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-blue-300 text-sm font-medium">Total de Tarefas</p>
                <p class="text-2xl font-bold text-blue-100">{{ tasks.length }}</p>
              </div>
              <div class="h-12 w-12 rounded-full bg-blue-500/20 flex items-center justify-center">
                <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
              </div>
            </div>
          </div>
          
          <!-- Próxima Tarefa -->
          <div class="bg-gradient-to-br from-green-900/50 via-green-800/50 to-green-900/50 rounded-2xl p-6 border-2 border-green-500/30 shadow-xl transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:border-green-500/50">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-green-300 text-sm font-medium">Próxima Tarefa</p>
                <p class="text-lg font-semibold text-green-100">{{ nextTaskTime || 'Nenhuma' }}</p>
              </div>
              <div class="h-12 w-12 rounded-full bg-green-500/20 flex items-center justify-center">
                <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
            </div>
          </div>
          
          <!-- Data Selecionada -->
          <div class="bg-gradient-to-br from-purple-900/50 via-purple-800/50 to-purple-900/50 rounded-2xl p-6 border-2 border-purple-500/30 shadow-xl transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:border-purple-500/50">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-purple-300 text-sm font-medium">Data Selecionada</p>
                <p class="text-lg font-semibold text-purple-100">{{ formattedTargetDate }}</p>
              </div>
              <div class="h-12 w-12 rounded-full bg-purple-500/20 flex items-center justify-center">
                <svg class="h-6 w-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Controles -->
      <div class="bg-gray-800/50 backdrop-blur-md shadow-xl rounded-2xl p-6 border border-cyan-500/30 mb-6">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
          <h2 class="text-2xl font-semibold text-white">Minhas Tarefas para {{ formattedTargetDate }}</h2>
          <div class="flex items-end space-x-3">
            <button @click="refreshTasks" 
                    class="p-2 bg-cyan-600/20 hover:bg-cyan-600/30 rounded-lg transition-colors duration-200"
                    :disabled="isLoading">
              <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
              </svg>
            </button>
            <button @click="checkRecords" 
                    class="p-2 bg-orange-600/20 hover:bg-orange-600/30 rounded-lg transition-colors duration-200"
                    :disabled="isLoading"
                    title="Verificar Registros">
              <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
              </svg>
            </button>
            <div class="flex flex-col">
              <label for="target-date" class="text-sm font-medium text-gray-300 mb-1">Data</label>
              <input type="date" id="target-date" v-model="targetDate" @change="fetchMyTasks"
                     class="bg-gray-700 border border-gray-600 text-white rounded-lg shadow-sm focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 sm:text-sm p-3 w-full min-w-[160px]">
            </div>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-show="isLoading" class="text-center text-gray-400 py-16">
        <div class="relative">
          <svg class="animate-spin h-12 w-12 text-cyan-500 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <p class="text-lg font-medium">Carregando suas tarefas...</p>
          <p class="text-sm text-gray-500 mt-2">Aguarde um momento</p>
        </div>
      </div>

      <!-- Empty State -->
      <div v-show="!isLoading && tasks.length === 0" class="text-center text-gray-400 py-16">
        <div class="bg-gray-800/30 backdrop-blur-md rounded-2xl p-8 border border-gray-600/30">
          <div class="w-24 h-24 bg-gray-700/50 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-white mb-2">Nenhuma tarefa encontrada</h3>
          <p class="text-gray-400 mb-6">Você não possui tarefas de manutenção agendadas para {{ formattedTargetDate }}.</p>
          <button @click="refreshTasks" 
                  class="inline-flex items-center px-4 py-2 bg-cyan-600 hover:bg-cyan-700 text-white rounded-lg transition-colors duration-200">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            Atualizar
          </button>
        </div>
      </div>

      <!-- Tasks List -->
      <div v-show="!isLoading && tasks.length > 0" class="space-y-6">
        <div v-for="(task, index) in tasks" :key="task.id"
             class="bg-gradient-to-br backdrop-blur-xl rounded-2xl p-6 border hover:shadow-xl transition-all duration-300"
             :class="{
               'from-green-900/50 via-green-800/50 to-green-900/50 border-green-500/50 hover:border-green-400/70 hover:shadow-green-500/10': task.is_completed_today,
               'from-gray-900/90 via-gray-800/90 to-gray-900/90 border-gray-700/50 hover:border-cyan-500/50 hover:shadow-cyan-500/10': !task.is_completed_today
             }"
             :style="{ animationDelay: `${index * 150}ms` }">
          
          <!-- Task Header -->
          <div class="flex items-start justify-between mb-6">
            <div class="flex items-center space-x-4 flex-1">
              <!-- Task Icon -->
              <div class="h-10 w-10 rounded-full bg-cyan-500/20 flex items-center justify-center">
                <svg class="h-5 w-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
              </div>
              
              <!-- Task Info -->
              <div class="flex-1">
                <h3 class="text-lg font-semibold text-white mb-1">{{ task.title }}</h3>
                <p v-if="task.description" class="text-sm text-gray-400 line-clamp-2">{{ task.description }}</p>
              </div>
            </div>
            
            <!-- Status Badge -->
            <div class="flex flex-col items-end space-y-2">
              <div v-if="task.is_completed_today" class="flex items-center space-x-2 bg-green-500/20 px-3 py-1 rounded-full border border-green-500/30">
                <div class="w-2 h-2 bg-green-400 rounded-full"></div>
                <span class="text-xs font-medium text-green-400">Concluído</span>
              </div>
              <div v-else class="flex items-center space-x-2 bg-orange-500/20 px-3 py-1 rounded-full border border-orange-500/30">
                <div class="w-2 h-2 bg-orange-400 rounded-full animate-pulse"></div>
                <span class="text-xs font-medium text-orange-400">Pendente</span>
              </div>
              <div class="bg-gray-700/50 px-3 py-1 rounded-full border border-gray-600/30">
                <span class="text-xs text-gray-300">
                  {{ task.due_time ? `⏰ ${task.due_time.substring(0,5)}` : '🕐 Sem horário' }}
                </span>
              </div>
            </div>
          </div>

          <!-- Task Details -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <!-- Equipment Info -->
            <div class="bg-gray-700/30 rounded-xl p-4 border border-gray-600/30">
              <div class="flex items-center space-x-3">
                <div class="h-8 w-8 rounded-lg bg-blue-500/20 flex items-center justify-center">
                  <svg class="h-4 w-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                  </svg>
                </div>
                <div>
                  <p class="text-xs font-medium text-gray-400">Equipamento</p>
                  <p class="text-sm font-semibold text-white">{{ task.equipment?.name || 'Não especificado' }}</p>
                </div>
              </div>
            </div>
            
            <!-- Responsible User Info -->
            <div class="bg-gray-700/30 rounded-xl p-4 border border-gray-600/30">
              <div class="flex items-center space-x-3">
                <div class="h-8 w-8 rounded-lg bg-purple-500/20 flex items-center justify-center">
                  <svg class="h-4 w-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                  </svg>
                </div>
                <div>
                  <p class="text-xs font-medium text-gray-400">Responsável</p>
                  <p class="text-sm font-semibold text-white">{{ task.responsible_users?.[0]?.name || 'Não atribuído' }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Informações de Conclusão (se concluída) -->
          <div v-if="task.is_completed_today" class="bg-green-900/30 backdrop-blur-sm rounded-xl p-4 border border-green-500/30">
            <div class="flex items-start space-x-3">
              <div class="h-8 w-8 rounded-lg bg-green-500/20 flex items-center justify-center flex-shrink-0 mt-1">
                <svg class="h-4 w-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              <div class="flex-1">
                <div class="flex items-center justify-between mb-2">
                  <p class="text-sm font-medium text-green-300">✅ Manutenção Concluída</p>
                  <p class="text-xs text-green-400">
                    {{ task.completed_at ? new Date(task.completed_at).toLocaleString('pt-BR', {
                      day: '2-digit',
                      month: '2-digit', 
                      year: 'numeric',
                      hour: '2-digit',
                      minute: '2-digit'
                    }) : 'Hoje' }}
                  </p>
                </div>
                <p v-if="task.completion_notes" class="text-sm text-gray-300 italic">
                  "{{ task.completion_notes }}"
                </p>
                <p v-else class="text-sm text-gray-400 italic">
                  Sem notas de conclusão
                </p>
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-gray-600/30">
            <template v-if="task.is_completed_today">
              <!-- Tarefa já concluída -->
              <button @click="viewCompletedRecord(task)"
                      class="glass-btn glass-btn-success flex-1 font-medium py-3 px-4 text-white transition-all duration-300">
                <div class="flex items-center justify-center space-x-2">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                  <span>Ver Registro Concluído</span>
                </div>
              </button>
              
              <button @click="viewTaskDetails(task)"
                      class="glass-btn glass-btn-info flex-1 font-medium py-3 px-4 text-white transition-all duration-300">
                <div class="flex items-center justify-center space-x-2">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                  <span>Ver Detalhes</span>
                </div>
              </button>
            </template>
            
            <template v-else>
              <!-- Tarefa pendente -->
              <button @click="goToRecordForm(task)"
                      class="glass-btn glass-btn-success flex-1 font-medium py-3 px-4 text-white transition-all duration-300">
                <div class="flex items-center justify-center space-x-2">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                  <span>Registrar Execução</span>
                </div>
              </button>
              
              <button @click="viewTaskDetails(task)"
                      class="glass-btn glass-btn-info flex-1 font-medium py-3 px-4 text-white transition-all duration-300">
                <div class="flex items-center justify-center space-x-2">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                  <span>Ver Detalhes</span>
                </div>
              </button>
            </template>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Detalhes da Tarefa -->
    <div v-if="selectedTask" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-black bg-opacity-75 transition-opacity backdrop-blur-sm" @click="closeTaskDetails"></div>
        
        <!-- Modal Container -->
        <div class="inline-block align-bottom bg-gray-800/95 backdrop-blur-xl rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-600/50">
          <!-- Header -->
          <div class="bg-gradient-to-r from-indigo-600/60 to-purple-600/60 backdrop-blur-md px-6 py-4 border-b border-gray-600/30">
            <div class="flex items-center justify-between">
              <h3 class="text-xl font-semibold text-white">Detalhes da Tarefa</h3>
              <button @click="closeTaskDetails" class="text-white/80 hover:text-white transition-colors p-1 rounded-lg hover:bg-white/10">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </button>
            </div>
          </div>
          
          <!-- Content -->
          <div class="px-6 py-6 space-y-6">
            <!-- Título e Descrição -->
            <div>
              <h4 class="text-lg font-semibold text-white mb-2">{{ selectedTask.title }}</h4>
              <p v-if="selectedTask.description" class="text-gray-300 leading-relaxed">{{ selectedTask.description }}</p>
              <p v-else class="text-gray-500 italic">Nenhuma descrição disponível</p>
            </div>
            
            <!-- Informações Principais -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Horário -->
              <div class="bg-gray-700/30 backdrop-blur-sm rounded-xl p-4 border border-gray-600/30">
                <div class="flex items-center space-x-3">
                  <div class="h-10 w-10 rounded-lg bg-green-500/20 flex items-center justify-center">
                    <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-400">Horário Previsto</p>
                    <p class="text-lg font-semibold text-white">
                      {{ selectedTask.due_time ? selectedTask.due_time.substring(0,5) : 'Sem horário definido' }}
                    </p>
                  </div>
                </div>
              </div>
              
              <!-- Equipamento -->
              <div class="bg-gray-700/30 backdrop-blur-sm rounded-xl p-4 border border-gray-600/30">
                <div class="flex items-center space-x-3">
                  <div class="h-10 w-10 rounded-lg bg-blue-500/20 flex items-center justify-center">
                    <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                    </svg>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-400">Equipamento</p>
                    <p class="text-lg font-semibold text-white">{{ selectedTask.equipment?.name || 'Não especificado' }}</p>
                    <p v-if="selectedTask.equipment?.description" class="text-sm text-gray-400">{{ selectedTask.equipment.description }}</p>
                  </div>
                </div>
              </div>
              
              <!-- Área de Manutenção -->
              <div class="bg-gray-700/30 backdrop-blur-sm rounded-xl p-4 border border-gray-600/30">
                <div class="flex items-center space-x-3">
                  <div class="h-10 w-10 rounded-lg bg-orange-500/20 flex items-center justify-center">
                    <svg class="h-5 w-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-400">Área de Manutenção</p>
                    <p class="text-lg font-semibold text-white">{{ selectedTask.equipment?.maintenance_area?.name || 'Não especificado' }}</p>
                  </div>
                </div>
              </div>
              
              <!-- Responsável -->
              <div class="bg-gray-700/30 backdrop-blur-sm rounded-xl p-4 border border-gray-600/30">
                <div class="flex items-center space-x-3">
                  <div class="h-10 w-10 rounded-lg bg-purple-500/20 flex items-center justify-center">
                    <svg class="h-5 w-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-400">Responsável</p>
                    <p class="text-lg font-semibold text-white">{{ selectedTask.responsible_users?.[0]?.name || 'Não atribuído' }}</p>
                    <p v-if="selectedTask.responsible_users?.[0]?.email" class="text-sm text-gray-400">{{ selectedTask.responsible_users[0].email }}</p>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Instruções Especiais -->
            <div v-if="selectedTask.instructions" class="bg-gray-700/30 backdrop-blur-sm rounded-xl p-4 border border-gray-600/30">
              <h5 class="text-sm font-medium text-gray-400 mb-2">Instruções Especiais</h5>
              <p class="text-white leading-relaxed">{{ selectedTask.instructions }}</p>
            </div>
          </div>
          
          <!-- Footer -->
          <div class="bg-gray-700/30 backdrop-blur-md px-6 py-4 border-t border-gray-600/30">
            <div class="flex justify-end space-x-3">
              <button @click="closeTaskDetails" 
                      class="glass-btn glass-btn-secondary px-4 py-2 text-white transition-all duration-200">
                Fechar
              </button>
              <button @click="goToRecordFormFromModal(selectedTask)"
                      class="glass-btn glass-btn-success px-4 py-2 text-white transition-all duration-200">
                Registrar Execução
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Debug dos Registros -->
    <div v-if="showDebugModal && debugInfo" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-black bg-opacity-75 transition-opacity backdrop-blur-sm" @click="closeDebugModal"></div>
        
        <!-- Modal Container -->
        <div class="inline-block align-bottom bg-gray-800/95 backdrop-blur-xl rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full border border-gray-600/50">
          <!-- Header -->
          <div class="bg-gradient-to-r from-orange-600/60 to-red-600/60 backdrop-blur-md px-6 py-4 border-b border-gray-600/30">
            <div class="flex items-center justify-between">
              <h3 class="text-xl font-semibold text-white">Debug - Verificação de Registros</h3>
              <button @click="closeDebugModal" class="text-white/80 hover:text-white transition-colors p-1 rounded-lg hover:bg-white/10">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </button>
            </div>
          </div>
          
          <!-- Content -->
          <div class="px-6 py-6 space-y-6 max-h-96 overflow-y-auto">
            <!-- Info Geral -->
            <div class="bg-gray-700/30 backdrop-blur-sm rounded-xl p-4 border border-gray-600/30">
              <h4 class="text-lg font-semibold text-white mb-3">Informações Gerais</h4>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                  <span class="text-gray-400">Data verificada:</span>
                  <span class="text-white ml-2">{{ debugInfo.target_date }}</span>
                </div>
                <div>
                  <span class="text-gray-400">Registros encontrados:</span>
                  <span class="text-white ml-2">{{ debugInfo.records_found }}</span>
                </div>
              </div>
            </div>
            
            <!-- Lista de Registros -->
            <div v-if="debugInfo.records && debugInfo.records.length > 0">
              <h4 class="text-lg font-semibold text-white mb-3">Registros Encontrados</h4>
              <div class="space-y-3">
                <div v-for="record in debugInfo.records" :key="record.id" 
                     class="bg-gray-700/30 backdrop-blur-sm rounded-xl p-4 border border-gray-600/30">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                    <div>
                      <span class="text-gray-400">ID do Registro:</span>
                      <span class="text-white ml-2">{{ record.id }}</span>
                    </div>
                    <div>
                      <span class="text-gray-400">ID do Agendamento:</span>
                      <span class="text-white ml-2">{{ record.schedule_id }}</span>
                    </div>
                    <div class="md:col-span-2">
                      <span class="text-gray-400">Título:</span>
                      <span class="text-white ml-2">{{ record.schedule_title }}</span>
                    </div>
                    <div>
                      <span class="text-gray-400">Status:</span>
                      <span :class="record.status === 'completed' ? 'text-green-400' : 'text-orange-400'" class="ml-2 font-medium">
                        {{ record.status }}
                      </span>
                    </div>
                    <div>
                      <span class="text-gray-400">Executado em:</span>
                      <span class="text-white ml-2">{{ record.performed_at || 'N/A' }}</span>
                    </div>
                    <div class="md:col-span-2">
                      <span class="text-gray-400">Criado em:</span>
                      <span class="text-white ml-2">{{ record.created_at }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Nenhum Registro -->
            <div v-else class="bg-gray-700/30 backdrop-blur-sm rounded-xl p-4 border border-gray-600/30 text-center">
              <p class="text-gray-400">Nenhum registro encontrado para esta data.</p>
            </div>
          </div>
          
          <!-- Footer -->
          <div class="bg-gray-700/30 backdrop-blur-md px-6 py-4 border-t border-gray-600/30">
            <div class="flex justify-end">
              <button @click="closeDebugModal" 
                      class="glass-btn glass-btn-secondary px-4 py-2 text-white transition-all duration-200">
                Fechar
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted, computed, h } from 'vue';
import AuthenticatedLayout from '@/components/layouts/AuthenticatedLayout.vue';
import api from '@/utils/api';
import { format, parseISO, startOfToday } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import { useRouter } from 'vue-router';
import { useToast } from '@/composables/useToast';

const router = useRouter();


const { errorToast } = useToast();



const tasks = ref([]);
const isLoading = ref(false);
const targetDate = ref(format(startOfToday(), 'yyyy-MM-dd'));
const selectedTask = ref(null);
const debugInfo = ref(null);
const showDebugModal = ref(false);

const formattedTargetDate = computed(() => {
  try {
    return format(parseISO(targetDate.value), "dd 'de' MMMM 'de' yyyy", { locale: ptBR });
  } catch (e) {
    return "Data Inválida";
  }
});

const nextTaskTime = computed(() => {
  if (tasks.value.length === 0) return null;
  const tasksWithTime = tasks.value.filter(task => !!task.due_time);
  if (tasksWithTime.length === 0) return null;
  const now = new Date();
  const today = format(now, 'yyyy-MM-dd');
  const nextTask = tasksWithTime
    .sort((a, b) => a.due_time.localeCompare(b.due_time))
    .find(task => {
      const taskTime = new Date(`${today}T${task.due_time}`);
      return taskTime > now;
    });
  return nextTask ? nextTask.due_time.substring(0, 5) : tasksWithTime[0].due_time.substring(0, 5);
});

async function fetchMyTasks() {
  isLoading.value = true;
  try {
    const response = await api.get(`/maintenance/schedules/my-today?date=${targetDate.value}`);
    const dataArray = Array.isArray(response.data) ? response.data : [];
    tasks.value = dataArray;
  } catch (error) {
    console.error('Erro ao buscar minhas tarefas de manutenção:', error);
    errorToast('Erro ao carregar tarefas de manutenção.');
    tasks.value = [];
  } finally {
    isLoading.value = false;
  }
}

async function refreshTasks() {
  await fetchMyTasks();
}

async function checkRecords() {
  try {
    const response = await api.post(`/maintenance/schedules/refresh-today?date=${targetDate.value}`);
    console.log('Verificação de registros:', response.data);
    
    // Armazenar informações de debug
    debugInfo.value = response.data;
    showDebugModal.value = true;
    
    // Recarregar as tarefas após a verificação
    await fetchMyTasks();
  } catch (error) {
    console.error('Erro ao verificar registros:', error);
    errorToast('Erro ao verificar registros. Veja o console para mais detalhes.');
  }
}

function closeDebugModal() {
  showDebugModal.value = false;
  debugInfo.value = null;
}

function goToRecordForm(task) {
  const routeName = 'maintenance.records.new';
  if (!task || !task.id) {
    errorToast('Tarefa inválida para registrar execução.');
    return;
  }
  try {
    router.push({ 
      name: routeName, 
      params: { scheduleId: task.id } 
    });
  } catch (error) {
    console.warn('Rota de registro não encontrada, redirecionando para dashboard', error);
    errorToast('Não foi possível abrir o formulário de registro.');
    router.push({ name: 'maintenance.dashboard' });
  }
}

function goToRecordFormFromModal(task) {
  closeTaskDetails();
  setTimeout(() => {
    goToRecordForm(task);
  }, 300);
}

function viewCompletedRecord(task) {
  if (!task || !task.completed_record_id) {
    errorToast('Registro de conclusão não encontrado.');
    return;
  }
  router.push(`/maintenance/records/${task.completed_record_id}`);
}

function viewTaskDetails(task) {
  if (!task) {
    errorToast('Tarefa inválida para visualizar detalhes.');
    return;
  }
  selectedTask.value = task;
}

function closeTaskDetails() {
  selectedTask.value = null;
}

onMounted(async () => {
  try {
    await fetchMyTasks();
  } catch (error) {
    console.error('Erro na montagem do componente:', error);
    errorToast('Erro ao carregar a página de tarefas.');
  }
});
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Botões Glass Style */
.glass-btn {
  position: relative;
  background: rgba(255, 255, 255, 0.15);
  border-radius: 1rem;
  border: 1.5px solid rgba(255,255,255,0.25);
  box-shadow:
    0 8px 32px rgba(31, 38, 135, 0.15),
    0 1.5px 4px rgba(255,255,255,0.1),
    inset 0 4px 24px rgba(255,255,255,0.15);
  backdrop-filter: blur(12px) saturate(180%);
  -webkit-backdrop-filter: blur(12px) saturate(180%);
  overflow: hidden;
  transition: 
    box-shadow 0.3s,
    border-color 0.3s,
    backdrop-filter 0.3s,
    -webkit-backdrop-filter 0.3s,
    background 0.3s,
    transform 0.2s;
}

.glass-btn:hover {
  background: rgba(255,255,255,0.2);
  box-shadow:
    0 12px 48px rgba(31, 38, 135, 0.25),
    0 0 0 2px rgba(34, 211, 238, 0.15),
    0 2px 8px rgba(255,255,255,0.15),
    inset 0 6px 32px rgba(255,255,255,0.25);
  border-color: rgba(255,255,255,0.4);
  backdrop-filter: blur(16px) saturate(200%);
  -webkit-backdrop-filter: blur(16px) saturate(200%);
  transform: translateY(-1px);
}

.glass-btn:active {
  transform: translateY(0);
  box-shadow: 0 4px 16px rgba(31, 38, 135, 0.2);
}

/* Variantes de cor */
.glass-btn-success {
  background: rgba(34, 197, 94, 0.15);
  border-color: rgba(34, 197, 94, 0.3);
}

.glass-btn-success:hover {
  background: rgba(34, 197, 94, 0.25);
  border-color: rgba(34, 197, 94, 0.5);
  box-shadow:
    0 12px 48px rgba(34, 197, 94, 0.3),
    0 0 0 2px rgba(34, 197, 94, 0.2),
    0 2px 8px rgba(34, 197, 94, 0.15),
    inset 0 6px 32px rgba(255,255,255,0.25);
}

.glass-btn-info {
  background: rgba(99, 102, 241, 0.15);
  border-color: rgba(99, 102, 241, 0.3);
}

.glass-btn-info:hover {
  background: rgba(99, 102, 241, 0.25);
  border-color: rgba(99, 102, 241, 0.5);
  box-shadow:
    0 12px 48px rgba(99, 102, 241, 0.3),
    0 0 0 2px rgba(99, 102, 241, 0.2),
    0 2px 8px rgba(99, 102, 241, 0.15),
    inset 0 6px 32px rgba(255,255,255,0.25);
}

.glass-btn-secondary {
  background: rgba(107, 114, 128, 0.15);
  border-color: rgba(107, 114, 128, 0.3);
}

.glass-btn-secondary:hover {
  background: rgba(107, 114, 128, 0.25);
  border-color: rgba(107, 114, 128, 0.5);
  box-shadow:
    0 12px 48px rgba(107, 114, 128, 0.3),
    0 0 0 2px rgba(107, 114, 128, 0.2),
    0 2px 8px rgba(107, 114, 128, 0.15),
    inset 0 6px 32px rgba(255,255,255,0.25);
}

/* Animação de entrada simplificada para os cards */
.bg-gradient-to-br {
  animation: slideInUp 0.6s ease-out forwards;
  opacity: 0;
  transform: translateY(20px);
}

@keyframes slideInUp {
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Animação do modal */
.fixed.inset-0 {
  animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.inline-block.align-bottom {
  animation: slideInModal 0.3s ease-out;
}

@keyframes slideInModal {
  from {
    opacity: 0;
    transform: translateY(20px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}
</style>
