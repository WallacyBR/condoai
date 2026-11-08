<template>
  <div>
    <!-- Page Header -->
    <div class="mb-6">
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-foreground">Dashboard</h1>
          <p class="text-muted-foreground mt-1">Visão geral do seu condomínio</p>
        </div>
        <div class="flex space-x-3">
          <ActionButton
            variant="secondary"
            icon="download"
            text="Exportar"
          />
          <ActionButton
            variant="primary"
            icon="plus"
            text="Novo Registro"
          />
        </div>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <StatsCard
        title="Total de Apartamentos"
        value="156"
        change="+2.1% este mês"
        change-direction="up"
        :icon="BuildingIcon"
        color="primary"
      />
      
      <StatsCard
        title="Moradores Ativos"
        value="423"
        change="+5.4% este mês"
        change-direction="up"
        :icon="UsersIcon"
        color="accent"
      />
      
      <StatsCard
        title="Taxa de Ocupação"
        value="94.2%"
        change="+1.2% este mês"
        change-direction="up"
        :icon="ChartIcon"
        color="secondary"
      />
      
      <StatsCard
        title="Receita Mensal"
        value="R$ 45,2K"
        change="-2.3% este mês"
        change-direction="down"
        :icon="MoneyIcon"
        color="primary"
      />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Recent Activity -->
      <div class="lg:col-span-2">
        <div class="bg-card rounded-xl p-6 shadow-sm border border-border">
          <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold text-foreground">Atividades Recentes</h3>
            <button class="text-sm text-primary hover:text-primary/80 transition-colors">
              Ver todas
            </button>
          </div>
          <div class="space-y-4">
            <div 
              v-for="activity in recentActivities" 
              :key="activity.id"
              class="flex items-start space-x-4 p-4 rounded-lg hover:bg-muted/50 transition-colors"
            >
              <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0" :class="activity.iconBg">
                <component :is="activity.icon" class="w-4 h-4" :class="activity.iconColor" />
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-foreground">
                  {{ activity.title }}
                </p>
                <p class="text-sm text-muted-foreground">
                  {{ activity.description }}
                </p>
                <p class="text-xs text-muted-foreground mt-1">{{ activity.time }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="space-y-6">
        <div class="bg-card rounded-xl p-6 shadow-sm border border-border">
          <h3 class="text-lg font-semibold text-foreground mb-4">Ações Rápidas</h3>
          <div class="space-y-3">
            <router-link
              v-for="action in quickActions"
              :key="action.name"
              :to="{ name: action.route }"
              class="flex items-center p-3 rounded-lg hover:bg-muted transition-colors"
            >
              <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3" :class="action.iconBg">
                <component :is="action.icon" class="w-4 h-4" :class="action.iconColor" />
              </div>
              <span class="text-sm font-medium text-foreground">{{ action.label }}</span>
            </router-link>
          </div>
        </div>

        <!-- Summary Stats -->
        <div class="bg-card rounded-xl p-6 shadow-sm border border-border">
          <h3 class="text-lg font-semibold text-foreground mb-4">Resumo</h3>
          <div class="space-y-4">
            <div class="flex justify-between items-center">
              <span class="text-sm text-muted-foreground">Apartamentos Ocupados</span>
              <span class="text-sm font-medium text-foreground">147/156</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-sm text-muted-foreground">Pagamentos em Dia</span>
              <span class="text-sm font-medium text-foreground">89%</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-sm text-muted-foreground">Manutenções Pendentes</span>
              <span class="text-sm font-medium text-foreground">3</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-sm text-muted-foreground">Novos Registros (Mês)</span>
              <span class="text-sm font-medium text-foreground">12</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

// Icons (usando SVG inline como componentes)
const BuildingIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
    </svg>
  `
}

const UsersIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
    </svg>
  `
}

const ChartIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
    </svg>
  `
}

const MoneyIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
    </svg>
  `
}

const UserIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
    </svg>
  `
}

const PlusIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
    </svg>
  `
}

// Dados mockados
const recentActivities = ref([
  {
    id: 1,
    title: 'Novo morador cadastrado',
    description: 'João Silva foi adicionado ao apartamento 142',
    time: '2 horas atrás',
    icon: UserIcon,
    iconBg: 'bg-primary/10',
    iconColor: 'text-primary'
  },
  {
    id: 2,
    title: 'Pagamento recebido',
    description: 'Condomínio de dezembro - Apartamento 85',
    time: '4 horas atrás',
    icon: MoneyIcon,
    iconBg: 'bg-accent/10',
    iconColor: 'text-accent'
  },
  {
    id: 3,
    title: 'Manutenção agendada',
    description: 'Limpeza da caixa d\'água programada para amanhã',
    time: '6 horas atrás',
    icon: PlusIcon,
    iconBg: 'bg-secondary/10',
    iconColor: 'text-secondary'
  }
])

const quickActions = ref([
  {
    name: 'add-resident',
    label: 'Adicionar Morador',
    route: 'residents.create',
    icon: UserIcon,
    iconBg: 'bg-primary/10',
    iconColor: 'text-primary'
  },
  {
    name: 'add-apartment',
    label: 'Novo Apartamento',
    route: 'apartments.create',
    icon: BuildingIcon,
    iconBg: 'bg-accent/10',
    iconColor: 'text-accent'
  },
  {
    name: 'add-tower',
    label: 'Nova Torre',
    route: 'towers.create',
    icon: BuildingIcon,
    iconBg: 'bg-secondary/10',
    iconColor: 'text-secondary'
  },
  {
    name: 'add-condominium',
    label: 'Novo Condomínio',
    route: 'condominiums.create',
    icon: BuildingIcon,
    iconBg: 'bg-primary/10',
    iconColor: 'text-primary'
  }
])
</script>
