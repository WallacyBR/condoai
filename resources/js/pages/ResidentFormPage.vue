<template>
  <AuthenticatedLayout :page-title="isEditing ? 'Editar Morador' : 'Novo Morador'">
    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-white">
            {{ isEditing ? 'Editar Morador' : 'Novo Morador' }}
          </h1>
          <p class="text-gray-400 mt-1">
            {{ isEditing ? 'Atualize as informações do morador' : 'Cadastre um novo morador no sistema' }}
          </p>
        </div>
        <NeonButton @click="goBack" variant="secondary" size="md">
          <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Voltar
        </NeonButton>
      </div>

      <!-- Formulário -->
      <div class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-2xl shadow-xl p-8 border-2 border-indigo-500/20">
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <!-- Dados Básicos -->
          <div class="space-y-4">
            <h3 class="text-xl font-semibold text-indigo-300 border-b border-indigo-500/30 pb-2">
              Dados Básicos
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Nome Completo -->
              <div class="md:col-span-2">
                <label for="name" class="block text-sm font-medium text-gray-300 mb-2">
                  Nome Completo *
                </label>
                <input
                  id="name"
                  v-model="form.name"
                  type="text"
                  required
                  class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                  placeholder="Digite o nome completo"
                  :class="{ 'border-red-500': errors.name }"
                />
                <p v-if="errors.name" class="mt-1 text-sm text-red-400">{{ errors.name }}</p>
              </div>

              <!-- RG -->
              <div>
                <label for="rg" class="block text-sm font-medium text-gray-300 mb-2">
                  RG
                </label>
                <input
                  id="rg"
                  v-model="form.rg"
                  type="text"
                  class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                  placeholder="00.000.000-0"
                  :class="{ 'border-red-500': errors.rg }"
                />
                <p v-if="errors.rg" class="mt-1 text-sm text-red-400">{{ errors.rg }}</p>
              </div>

              <!-- CPF -->
              <div>
                <label for="cpf" class="block text-sm font-medium text-gray-300 mb-2">
                  CPF *
                </label>
                <input
                  id="cpf"
                  v-model="form.cpf"
                  type="text"
                  required
                  @input="watchCPF"
                  @blur="watchCPF"
                  class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                  placeholder="000.000.000-00"
                  :class="{ 
                    'border-red-500': errors.cpf,
                    'border-green-500': form.cpf && !errors.cpf && form.cpf.replace(/\D/g, '').length === 11
                  }"
                />
                <p v-if="errors.cpf" class="mt-1 text-sm text-red-400">{{ errors.cpf }}</p>
                <p v-else-if="form.cpf && !errors.cpf && form.cpf.replace(/\D/g, '').length === 11" class="mt-1 text-sm text-green-400">
                  ✓ CPF válido
                </p>
              </div>
            </div>
          </div>

          <!-- Contato -->
          <div class="space-y-4">
            <h3 class="text-xl font-semibold text-indigo-300 border-b border-indigo-500/30 pb-2">
              Informações de Contato
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Telefone Celular -->
              <div>
                <label for="cell_phone" class="block text-sm font-medium text-gray-300 mb-2">
                  Telefone Celular
                </label>
                <input
                  id="cell_phone"
                  v-model="form.cell_phone"
                  type="tel"
                  @input="watchPhone"
                  class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                  placeholder="(11) 99999-9999"
                  :class="{ 'border-red-500': errors.cell_phone }"
                />
                <p v-if="errors.cell_phone" class="mt-1 text-sm text-red-400">{{ errors.cell_phone }}</p>
              </div>

              <!-- Telefone Comercial -->
              <div>
                <label for="commercial_phone" class="block text-sm font-medium text-gray-300 mb-2">
                  Telefone Comercial
                </label>
                <input
                  id="commercial_phone"
                  v-model="form.commercial_phone"
                  type="tel"
                  @input="watchCommercialPhone"
                  class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                  placeholder="(11) 3333-3333"
                  :class="{ 'border-red-500': errors.commercial_phone }"
                />
                <p v-if="errors.commercial_phone" class="mt-1 text-sm text-red-400">{{ errors.commercial_phone }}</p>
              </div>

              <!-- WhatsApp -->
              <div>
                <label for="whatsapp" class="block text-sm font-medium text-gray-300 mb-2">
                  WhatsApp
                </label>
                <input
                  id="whatsapp"
                  v-model="form.whatsapp"
                  type="tel"
                  @input="watchWhatsApp"
                  class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                  placeholder="(11) 99999-9999"
                  :class="{ 'border-red-500': errors.whatsapp }"
                />
                <p v-if="errors.whatsapp" class="mt-1 text-sm text-red-400">{{ errors.whatsapp }}</p>
              </div>

              <!-- Email -->
              <div>
                <label for="email" class="block text-sm font-medium text-gray-300 mb-2">
                  Email *
                </label>
                <input
                  id="email"
                  v-model="form.email"
                  type="email"
                  required
                  class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                  placeholder="email@exemplo.com"
                  :class="{ 'border-red-500': errors.email }"
                />
                <p v-if="errors.email" class="mt-1 text-sm text-red-400">{{ errors.email }}</p>
              </div>
            </div>
          </div>

          <!-- Tipo de Pessoa -->
          <div class="space-y-4">
            <h3 class="text-xl font-semibold text-indigo-300 border-b border-indigo-500/30 pb-2">
              Tipo de Pessoa
            </h3>
            
            <div class="space-y-4">
              <div>
                <label for="person_type" class="block text-sm font-medium text-gray-300 mb-2">
                  Tipo de Pessoa *
                </label>
                <select
                  id="person_type"
                  v-model="form.person_type"
                  required
                  class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                  :class="{ 'border-red-500': errors.person_type }"
                >
                  <option value="fisica">Pessoa Física</option>
                  <option value="juridica">Pessoa Jurídica</option>
                </select>
                <p v-if="errors.person_type" class="mt-1 text-sm text-red-400">{{ errors.person_type }}</p>
              </div>
            </div>
          </div>

          <!-- Funções no Condomínio -->
          <div class="space-y-4">
            <h3 class="text-xl font-semibold text-indigo-300 border-b border-indigo-500/30 pb-2">
              Funções no Condomínio
            </h3>
            
            <div class="space-y-3">
              <div class="flex items-center space-x-3">
                <input
                  id="is_porter"
                  v-model="form.is_porter"
                  type="checkbox"
                  class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-600 rounded bg-gray-800"
                />
                <label for="is_porter" class="text-gray-300">
                  Porteiro
                </label>
              </div>
              
              <div class="flex items-center space-x-3">
                <input
                  id="is_janitor"
                  v-model="form.is_janitor"
                  type="checkbox"
                  class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-600 rounded bg-gray-800"
                />
                <label for="is_janitor" class="text-gray-300">
                  Zelador
                </label>
              </div>
              
              <div class="flex items-center space-x-3">
                <input
                  id="is_cleaning"
                  v-model="form.is_cleaning"
                  type="checkbox"
                  class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-600 rounded bg-gray-800"
                />
                <label for="is_cleaning" class="text-gray-300">
                  Serviços de Limpeza
                </label>
              </div>
            </div>
          </div>

          <!-- Observações e Foto -->
          <div class="space-y-4">
            <h3 class="text-xl font-semibold text-indigo-300 border-b border-indigo-500/30 pb-2">
              Observações e Foto
            </h3>
            
            <div class="space-y-4">
              <!-- Foto -->
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  Foto do Morador
                </label>
                <div class="flex items-center space-x-4">
                  <div class="shrink-0">
                    <img v-if="photoPreview" :src="photoPreview" class="h-16 w-16 rounded-full object-cover" />
                    <div v-else class="h-16 w-16 rounded-full bg-gray-700 flex items-center justify-center">
                      <svg class="h-8 w-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                      </svg>
                    </div>
                  </div>
                  <input
                    id="photo"
                    type="file"
                    @change="handlePhotoChange"
                    accept="image/*"
                    class="block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700"
                  />
                </div>
                <p v-if="errors.photo" class="mt-1 text-sm text-red-400">{{ errors.photo }}</p>
              </div>

              <!-- Observações -->
              <div>
                <label for="notes" class="block text-sm font-medium text-gray-300 mb-2">
                  Observações
                </label>
                <textarea
                  id="notes"
                  v-model="form.notes"
                  rows="3"
                  class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                  placeholder="Informações adicionais sobre o morador"
                ></textarea>
                <p v-if="errors.notes" class="mt-1 text-sm text-red-400">{{ errors.notes }}</p>
              </div>
            </div>
          </div>

          <!-- Endereço de Correspondência -->
          <div class="space-y-4">
            <h3 class="text-xl font-semibold text-indigo-300 border-b border-indigo-500/30 pb-2">
              Endereço para Recebimento de Boletos
            </h3>
            
            <div class="space-y-2">
              <label class="flex items-center text-gray-300">
                <input
                  type="radio"
                  v-model="form.billing_address_type"
                  value="condominium"
                  class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-600"
                />
                <span class="ml-2">Usar endereço do condomínio</span>
              </label>
              <label class="flex items-center text-gray-300">
                <input
                  type="radio"
                  v-model="form.billing_address_type"
                  value="custom"
                  class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-600"
                />
                <span class="ml-2">Usar endereço personalizado</span>
              </label>
            </div>
            
            <div v-if="form.billing_address_type === 'custom'" class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
              <!-- CEP -->
              <div>
                <label for="cep" class="block text-sm font-medium text-gray-300 mb-2">CEP</label>
                <input
                  id="cep"
                  v-model="form.cep"
                  type="text"
                  @input="watchCEP"
                  class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                  placeholder="00000-000"
                />
              </div>

              <!-- Endereço -->
              <div class="md:col-span-2">
                <label for="address" class="block text-sm font-medium text-gray-300 mb-2">Endereço</label>
                <input
                  id="address"
                  v-model="form.address"
                  type="text"
                  class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                />
              </div>

              <!-- Bairro -->
              <div>
                <label for="neighborhood" class="block text-sm font-medium text-gray-300 mb-2">Bairro</label>
                <input
                  id="neighborhood"
                  v-model="form.neighborhood"
                  type="text"
                  class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                />
              </div>

              <!-- Cidade -->
              <div>
                <label for="city" class="block text-sm font-medium text-gray-300 mb-2">Cidade</label>
                <input
                  id="city"
                  v-model="form.city"
                  type="text"
                  class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                />
              </div>

              <!-- Estado -->
              <div>
                <label for="state" class="block text-sm font-medium text-gray-300 mb-2">Estado</label>
                <input
                  id="state"
                  v-model="form.state"
                  type="text"
                  class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                />
              </div>
            </div>
          </div>

          <!-- Situação e Unidade -->
          <div class="space-y-4">
            <h3 class="text-xl font-semibold text-indigo-300 border-b border-indigo-500/30 pb-2">
              Situação e Unidade
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Classificação -->
              <div>
                <label for="classification" class="block text-sm font-medium text-gray-300 mb-2">
                  Classificação *
                </label>
                <select
                  id="classification"
                  v-model="form.classification"
                  required
                  class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                  :class="{ 'border-red-500': errors.classification }"
                >
                  <option value="">Selecione uma classificação</option>
                  <option value="proprietario">Proprietário</option>
                  <option value="morador">Morador</option>
                  <option value="inquilino">Inquilino</option>
                </select>
                <p class="mt-2 text-sm text-gray-400">
                  <strong>Proprietário:</strong> Dono do imóvel (pode ou não morar).<br/>
                  <strong>Morador:</strong> Reside no imóvel (não é dono nem inquilino).<br/>
                  <strong>Inquilino:</strong> Aluga o imóvel.
                </p>
                <p v-if="errors.classification" class="mt-1 text-sm text-red-400">{{ errors.classification }}</p>
              </div>

              <!-- Status Ativo -->
              <div class="flex items-center pt-8">
                <input
                  id="active"
                  v-model="form.active"
                  type="checkbox"
                  class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-600 rounded bg-gray-800"
                />
                <label for="active" class="ml-2 text-gray-300">
                  Morador Ativo
                </label>
              </div>

              <!-- Unidade de Residência -->
              <div class="md:col-span-2">
                <label for="apartment_id" class="block text-sm font-medium text-gray-300 mb-2">
                  Unidade de Residência
                </label>
                <select
                  id="apartment_id"
                  v-model="form.apartment_id"
                  class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                  :class="{ 'border-red-500': errors.apartment_id }"
                >
                  <option value="">Nenhuma (não reside no condomínio)</option>
                  <option v-for="apartment in apartments" :key="apartment.id" :value="apartment.id">
                    {{ formatApartmentDescription(apartment) }}
                  </option>
                </select>
                <p class="mt-1 text-xs text-gray-500">Selecione a unidade onde este morador reside, se aplicável.</p>
                <p v-if="errors.apartment_id" class="mt-1 text-sm text-red-400">{{ errors.apartment_id }}</p>
              </div>

              <!-- Unidades do Proprietário -->
              <div v-if="form.classification === 'proprietario'" class="md:col-span-2">
                <label for="owned_apartments" class="block text-sm font-medium text-gray-300 mb-2">
                  Unidades Possuídas
                  <span class="text-xs text-gray-400 ml-1">(selecione uma ou mais)</span>
                </label>
                <div>
                  <VueMultiselect
                    id="owned_apartments"
                    v-model="form.owned_apartment_ids"
                    :options="availableApartments"
                    :multiple="true"
                    :close-on-select="false"
                    placeholder="Digite para pesquisar unidades..."
                    label="number"
                    track-by="id"
                    selectLabel="Pressione enter para selecionar"
                    deselectLabel="Pressione enter para remover"
                    selectedLabel="Selecionado"
                  >
                    <template #option="{ option }">
                      <span>{{ formatApartmentDescription(option) }}</span>
                    </template>
                    <template #noResult>
                      <span>Nenhuma unidade encontrada.</span>
                    </template>
                  </VueMultiselect>
                </div>
                
                <!-- Cards das unidades possuídas -->
                <div v-if="form.owned_apartment_ids && form.owned_apartment_ids.length > 0" class="mt-4 space-y-3">
                    <h4 class="text-sm font-medium text-gray-400">Unidades Selecionadas:</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                      <div 
                        v-for="apartment in form.owned_apartment_ids" 
                        :key="apartment.id" 
                        class="bg-gray-800/50 border border-gray-700 rounded-lg p-3 flex items-center justify-between"
                      >
                        <span class="text-sm text-white">{{ formatApartmentDescription(apartment) }}</span>
                        <button 
                          @click="removeOwnedApartment(apartment)"
                          type="button"
                          class="p-1 rounded-full text-gray-400 hover:bg-red-500/20 hover:text-red-400 transition-colors"
                          title="Desvincular unidade"
                        >
                          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                          </svg>
                        </button>
                      </div>
                    </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Condomínio -->
          <div v-if="!isCondominiumAutoDetermined" class="space-y-4">
            <h3 class="text-xl font-semibold text-indigo-300 border-b border-indigo-500/30 pb-2">
              Condomínio
            </h3>
            
            <div>
              <label for="condominium_id" class="block text-sm font-medium text-gray-300 mb-2">
                Condomínio *
              </label>
              <select
                id="condominium_id"
                v-model="form.condominium_id"
                required
                class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                :class="{ 'border-red-500': errors.condominium_id }"
                :disabled="loading"
              >
                <option value="">Selecione um condomínio</option>
                <option v-for="condominium in condominiums" :key="condominium.id" :value="condominium.id">
                  {{ condominium.name }}
                </option>
              </select>
              <p v-if="errors.condominium_id" class="mt-1 text-sm text-red-400">{{ errors.condominium_id }}</p>
            </div>
          </div>

          <!-- Informação do Condomínio Atual -->
          <div v-if="isCondominiumAutoDetermined" class="space-y-4">
            <h3 class="text-xl font-semibold text-indigo-300 border-b border-indigo-500/30 pb-2">
              Condomínio
            </h3>
            
            <div class="bg-indigo-500/10 border border-indigo-500/30 rounded-lg p-4">
              <div class="flex items-center space-x-2">
                <svg class="h-5 w-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span class="text-indigo-300 font-medium">Condomínio:</span>
                <span class="text-white font-semibold">{{ selectedCondominiumName }}</span>
              </div>
              <p class="text-sm text-gray-400 mt-2">
                O condomínio foi determinado automaticamente com base no seu contexto atual.
              </p>
            </div>
          </div>

          <!-- Botões -->
          <div class="flex justify-end space-x-4 pt-6">
            <NeonButton @click="goBack" type="button" variant="secondary">
              Cancelar
            </NeonButton>
            <NeonButton type="submit" :loading="loading">
              <svg v-if="!loading" class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              {{ isEditing ? 'Atualizar Morador' : 'Criar Morador' }}
            </NeonButton>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import AuthenticatedLayout from '../components/layouts/AuthenticatedLayout.vue';
import NeonButton from '../components/NeonButton.vue';
import VueMultiselect from 'vue-multiselect';
import api from '../utils/api';
import { formatApartmentDescription } from '../utils/formatters.js';
import { formatCPF, formatPhone, formatCEP } from '../utils/validators.js';
import { useCondominiumFilter } from '../composables/useCondominiumFilter.js';

export default {
  name: 'ResidentFormPage',
  components: {
    AuthenticatedLayout,
    NeonButton,
    VueMultiselect
  },
  setup() {
    const route = useRoute();
    const router = useRouter();
    const loading = ref(false);
    const errors = ref({});
    const photoPreview = ref(null);
    const condominiums = ref([]);
    const apartments = ref([]);
    const loadingApartments = ref(false);

    const residentId = computed(() => route.params.id);
    const isEditing = computed(() => !!residentId.value);

    // Usar o composable para gerenciar condomínio
    const {
      selectedCondominiumId,
      selectedCondominiumName,
      isCondominiumAutoDetermined,
      shouldLoadCondominiums,
      getCurrentCondominiumId,
      getCurrentCondominiumName
    } = useCondominiumFilter();

    // Computed property para as unidades disponíveis no multiselect
    const availableApartments = computed(() => {
      if (!apartments.value) {
        return [];
      }
      return apartments.value;
    });

    const form = reactive({
      name: '',
      rg: '',
      cpf: '',
      cell_phone: '',
      commercial_phone: '',
      whatsapp: '',
      email: '',
      person_type: 'fisica',
      is_porter: false,
      is_janitor: false,
      is_cleaning: false,
      notes: '',
      photo: null,
      billing_address_type: 'condominium',
      cep: '',
      address: '',
      neighborhood: '',
      city: '',
      state: '',
      classification: '',
      active: true,
      apartment_id: '',
      condominium_id: getCurrentCondominiumId() || '',
      owned_apartment_ids: [],
    });

    const loadCondominiums = async () => {
      // Se o condomínio já foi determinado automaticamente, não carregar a lista
      if (!shouldLoadCondominiums()) {
        console.log('Condomínio determinado automaticamente, pulando carregamento da lista');
        return;
      }
      
      try {
        const { data } = await api.get('/condominiums');
        condominiums.value = data;
      } catch (error) {
        console.error("Erro ao carregar condomínios:", error);
      }
    };

    const loadApartments = async (condominiumId) => {
      if (!condominiumId) {
        apartments.value = [];
        return;
      }
      loadingApartments.value = true;
      try {
        const { data } = await api.get(`/apartments?condominium_id=${condominiumId}`);
        apartments.value = data;
      } catch (error) {
        console.error(`Erro ao carregar apartamentos para o condomínio ${condominiumId}:`, error);
        apartments.value = [];
      } finally {
        loadingApartments.value = false;
      }
    };

    const fetchResident = async (id) => {
      loading.value = true;
      try {
        const { data } = await api.get(`/residents/${id}`);
        form.name = data.name;
        form.rg = data.rg;
        form.cpf = formatCPF(data.cpf);
        form.cell_phone = formatPhone(data.cell_phone);
        form.commercial_phone = formatPhone(data.commercial_phone);
        form.whatsapp = formatPhone(data.whatsapp);
        form.email = data.email;
        form.person_type = data.person_type;
        form.is_porter = data.is_porter;
        form.is_janitor = data.is_janitor;
        form.is_cleaning = data.is_cleaning;
        form.notes = data.notes;
        photoPreview.value = data.photo_url || data.photo;
        form.billing_address_type = data.billing_address_type;
        form.cep = formatCEP(data.cep);
        form.address = data.address;
        form.neighborhood = data.neighborhood;
        form.city = data.city;
        form.state = data.state;
        form.classification = data.raw_classification || data.classification;
        form.active = data.active;
        form.apartment_id = data.apartment_id;
        form.condominium_id = data.condominium_id;

        if (data.condominium_id) {
          await loadApartments(data.condominium_id);
        }
        
        if (data.owned_apartments && Array.isArray(data.owned_apartments)) {
            form.owned_apartment_ids = apartments.value.filter(apt => 
                data.owned_apartments.some(ownedApt => ownedApt.id === apt.id)
            );
        }

      } catch (error) {
        console.error("Erro ao carregar dados do morador:", error);
        errors.value.general = "Não foi possível carregar os dados do morador.";
      } finally {
        loading.value = false;
      }
    };
    
    watch(() => form.condominium_id, (newId) => {
        loadApartments(newId);
        if(!isEditing.value) {
            form.apartment_id = '';
            form.owned_apartment_ids = [];
        }
    });

    // Watch para mudanças no condomínio selecionado no localStorage
    watch(selectedCondominiumId, (newId) => {
        if (newId && !isEditing.value) {
            form.condominium_id = newId;
            loadApartments(newId);
        }
    });

    onMounted(async () => {
      // Só carregar condomínios se não houver um determinado automaticamente
      if (shouldLoadCondominiums()) {
        await loadCondominiums();
      }
      
      if (isEditing.value) {
        await fetchResident(residentId.value);
      } else {
        // Se não estiver editando e não houver condomínio selecionado, definir o padrão
        if (!form.condominium_id && getCurrentCondominiumId()) {
          form.condominium_id = getCurrentCondominiumId();
        }
      }
    });

    const handlePhotoChange = (event) => {
      const file = event.target.files[0];
      if (file) {
        form.photo = file;
        const reader = new FileReader();
        reader.onload = (e) => {
          photoPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    };

    const removeOwnedApartment = (apartmentToRemove) => {
        console.log('=== REMOVENDO APARTAMENTO ===');
        console.log('Apartamento a remover:', apartmentToRemove);
        console.log('Array antes da remoção:', form.owned_apartment_ids);
        
        form.owned_apartment_ids = form.owned_apartment_ids.filter(
            apartment => apartment.id !== apartmentToRemove.id
        );
        
        console.log('Array após a remoção:', form.owned_apartment_ids);
        console.log('=== FIM REMOÇÃO ===');
    };

    const handleSubmit = async () => {
      console.log('=== INÍCIO DO HANDLE SUBMIT ===');
      console.log('Form original:', JSON.stringify(form, null, 2));
      console.log('Is editing:', isEditing.value);
      console.log('Resident ID:', residentId.value);
      
      loading.value = true;
      errors.value = {};

      const formData = new FormData();
      
      // Limpar máscaras antes de enviar
      const cleanedForm = { ...form };
      console.log('Form após cópia:', JSON.stringify(cleanedForm, null, 2));
      
      cleanedForm.cpf = cleanedForm.cpf.replace(/\D/g, '');
      cleanedForm.cell_phone = cleanedForm.cell_phone.replace(/\D/g, '');
      cleanedForm.commercial_phone = cleanedForm.commercial_phone.replace(/\D/g, '');
      cleanedForm.whatsapp = cleanedForm.whatsapp.replace(/\D/g, '');
      cleanedForm.cep = cleanedForm.cep.replace(/\D/g, '');
      
      console.log('Form após limpeza de máscaras:', JSON.stringify(cleanedForm, null, 2));
      
      // Converter strings booleanas para booleanos reais
      cleanedForm.is_porter = cleanedForm.is_porter ? 1 : 0;
      cleanedForm.is_janitor = cleanedForm.is_janitor ? 1 : 0;
      cleanedForm.is_cleaning = cleanedForm.is_cleaning ? 1 : 0;
      cleanedForm.active = cleanedForm.active ? 1 : 0;
      
      console.log('Form após conversão de booleanos:', JSON.stringify(cleanedForm, null, 2));
      
      // Tratar owned_apartment_ids corretamente
      console.log('Classification:', cleanedForm.classification);
      console.log('Owned apartment IDs antes do tratamento:', cleanedForm.owned_apartment_ids);
      console.log('Tipo de owned_apartment_ids:', typeof cleanedForm.owned_apartment_ids);
      console.log('É array?', Array.isArray(cleanedForm.owned_apartment_ids));
      
      if (cleanedForm.classification === 'proprietario' && Array.isArray(cleanedForm.owned_apartment_ids)) {
        // Se é proprietário e tem apartamentos selecionados, enviar os IDs
        if (cleanedForm.owned_apartment_ids.length > 0) {
          cleanedForm.owned_apartment_ids = cleanedForm.owned_apartment_ids.map(apt => apt.id);
          console.log('Owned apartment IDs após mapeamento:', cleanedForm.owned_apartment_ids);
        } else {
          // Se é proprietário mas não tem apartamentos, enviar array vazio
          cleanedForm.owned_apartment_ids = [];
          console.log('Owned apartment IDs definido como array vazio');
        }
      } else {
        // Se não é proprietário, não enviar o campo
        delete cleanedForm.owned_apartment_ids;
        console.log('Owned apartment IDs removido (não é proprietário)');
      }

      console.log('Form final antes do FormData:', JSON.stringify(cleanedForm, null, 2));

      // Adicionar campos ao FormData
      for (const key in cleanedForm) {
          console.log(`Processando campo: ${key} = ${cleanedForm[key]} (tipo: ${typeof cleanedForm[key]})`);
          
          // Não enviar condominium_id se for determinado automaticamente
          if (key === 'condominium_id' && isCondominiumAutoDetermined.value) {
              console.log('Condominium_id não enviado (determinado automaticamente)');
              continue;
          }
          
          if (key === 'owned_apartment_ids' && Array.isArray(cleanedForm[key])) {
              if (cleanedForm[key].length > 0) {
                  cleanedForm[key].forEach(id => {
                      formData.append('owned_apartment_ids[]', id);
                      console.log(`Adicionado ao FormData: owned_apartment_ids[] = ${id}`);
                  });
              } else {
                  // Enviar array vazio explicitamente
                  formData.append('owned_apartment_ids', '');
                  console.log('Adicionado ao FormData: owned_apartment_ids = "" (array vazio)');
              }
          } else if (cleanedForm[key] !== null && cleanedForm[key] !== undefined) {
              formData.append(key, cleanedForm[key]);
              console.log(`Adicionado ao FormData: ${key} = ${cleanedForm[key]}`);
          } else {
              console.log(`Campo ${key} ignorado (null/undefined)`);
          }
      }

      if (isEditing.value) {
        formData.append('_method', 'PUT');
        console.log('Adicionado _method = PUT');
      }

      // Log do FormData
      console.log('=== FORMDATA CONTENTS ===');
      for (let [key, value] of formData.entries()) {
          console.log(`${key}: ${value}`);
      }
      console.log('=== FIM FORMDATA ===');

      try {
        const url = isEditing.value ? `/residents/${residentId.value}` : '/residents';
        console.log('URL da requisição:', url);
        
        const response = await api.post(url, formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });
        
        console.log('Resposta de sucesso:', response.data);
        router.push('/residents');
      } catch (error) {
        console.error('=== ERRO NA REQUISIÇÃO ===');
        console.error('Error object:', error);
        console.error('Response status:', error.response?.status);
        console.error('Response data:', error.response?.data);
        console.error('Response headers:', error.response?.headers);
        
        if (error.response && error.response.data && error.response.data.errors) {
          errors.value = error.response.data.errors;
          console.error('Erros de validação:', error.response.data.errors);
        } else {
          errors.value.general = "Ocorreu um erro inesperado.";
          console.error('Erro geral:', error);
        }
      } finally {
        loading.value = false;
        console.log('=== FIM DO HANDLE SUBMIT ===');
      }
    };
    
    const goBack = () => router.back();

    // Watchers para formatação
    const watchCPF = (event) => form.cpf = formatCPF(event.target.value);
    const watchPhone = (event) => form.cell_phone = formatPhone(event.target.value);
    const watchCommercialPhone = (event) => form.commercial_phone = formatPhone(event.target.value);
    const watchWhatsApp = (event) => form.whatsapp = formatPhone(event.target.value);
    const watchCEP = (event) => form.cep = formatCEP(event.target.value);

    return {
      form,
      loading,
      errors,
      isEditing,
      photoPreview,
      condominiums,
      apartments,
      loadingApartments,
      goBack,
      handleSubmit,
      handlePhotoChange,
      watchCPF,
      watchPhone,
      watchCommercialPhone,
      watchWhatsApp,
      watchCEP,
      formatApartmentDescription,
      removeOwnedApartment,
      availableApartments,
      selectedCondominiumId,
      selectedCondominiumName,
      isCondominiumAutoDetermined,
    };
  }
};
</script>

<style src="vue-multiselect/dist/vue-multiselect.css"></style>
<style>
/* Custom styles for Vue-Multiselect to match the dark theme */
.multiselect__tags {
  @apply bg-gray-800 border-gray-700 rounded-lg text-white placeholder-gray-500;
}
.multiselect__input {
  @apply bg-gray-800 text-white placeholder-gray-500;
}
.multiselect__content-wrapper {
  @apply bg-gray-800 border-gray-700;
}
.multiselect__option--highlight {
  @apply bg-indigo-500;
}
.multiselect__option--selected {
  @apply bg-indigo-600;
}

/* Esconder completamente os badges/tags do multiselect */
.multiselect__tag {
  display: none !important;
}
.multiselect__tag-icon {
  display: none !important;
}
.multiselect__tag-icon:hover {
  display: none !important;
}

/* Ajustar o input para ocupar todo o espaço disponível */
.multiselect__input {
  @apply bg-gray-800 text-white placeholder-gray-500;
  /* width: 100% !important; */ /* Removido para evitar conflito com a lógica do componente */
}

/* Controlar altura do multiselect sem bloquear o dropdown */
.multiselect {
  min-height: 42px !important;
}

.multiselect__tags {
  @apply bg-gray-800 border-gray-700 rounded-lg text-white placeholder-gray-500;
  min-height: 42px !important;
  /* Removido overflow: hidden para permitir que o dropdown apareça */
  display: flex !important;
  align-items: center !important;
  overflow: hidden !important; /* Adicionado de volta para conter o input */
}

/* Garantir que o dropdown seja visível e bem posicionado */
.multiselect__content-wrapper {
  @apply bg-gray-800 border-gray-700;
  max-width: 100% !important;
  z-index: 50 !important;
  position: absolute !important;
  top: 100% !important;
  left: 0 !important;
  right: 0 !important;
  max-height: 200px !important;
  overflow-y: auto !important;
}

.multiselect__option {
  white-space: nowrap !important;
  overflow: hidden !important;
  text-overflow: ellipsis !important;
  padding: 8px 12px !important;
  cursor: pointer !important;
}

.multiselect__option:hover {
  @apply bg-indigo-500;
}

/* Garantir que o multiselect tenha posição relativa para o dropdown funcionar */
.multiselect {
  position: relative !important;
}
</style> 