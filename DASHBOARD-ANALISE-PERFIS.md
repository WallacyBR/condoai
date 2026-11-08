# Dashboard de Análise - Perfis e Privilégios

## Visão Geral

O Dashboard de Análise de Perfis e Privilégios é uma ferramenta completa para visualização e análise de dados relacionados ao sistema de permissões do CondoAI. Fornece insights detalhados sobre distribuição de usuários, uso de módulos, privilégios concedidos e análise de segurança.

## Funcionalidades Implementadas

### 1. Estatísticas Gerais
- **Total de Perfis**: Quantidade total de perfis cadastrados
- **Total de Usuários**: Quantidade total de usuários no sistema
- **Módulos do Sistema**: Quantidade de módulos disponíveis
- **Perfis Ativos**: Perfis que possuem usuários vinculados
- **Perfis Não Utilizados**: Perfis sem usuários vinculados

### 2. Gráficos Interativos

#### Gráfico de Usuários por Perfil (Doughnut)
- Visualiza a distribuição de usuários entre os diferentes perfis
- Cores diferenciadas para cada perfil
- Informações adicionais: total de usuários, perfil mais popular
- Clique interativo para detalhes

#### Gráfico de Uso dos Módulos (Barras)
- Mostra quantos perfis têm acesso a cada módulo
- Ordenado por popularidade
- Tooltip com percentual de uso
- Rotação de labels para melhor legibilidade

#### Gráfico de Distribuição de Privilégios (Barras)
- Visualiza a quantidade de cada tipo de permissão (Visualizar, Criar, Editar, Excluir)
- Cores específicas para cada tipo de privilégio
- Estatísticas de total e distribuição

#### Gráfico de Escopos de Acesso (Pizza)
- Mostra a distribuição entre escopos: Próprios registros, Registros do condomínio, Todos os registros
- Análise de segurança baseada na distribuição de escopos

### 3. Análise de Segurança

#### Indicadores de Risco
- **Perfis de Alto Privilégio**: Identifica perfis com muitos privilégios
- **Usuários Administrativos**: Conta usuários com perfis administrativos (SysOp, Administradora)
- **Acesso Crítico**: Perfis com acesso a módulos críticos

#### Nível de Segurança
- Classificação automática: Excelente, Bom, Moderado, Ruim, Crítico
- Baseado na análise de riscos combinados

#### Recomendações Inteligentes
- **Análise Automática**: Sistema identifica possíveis problemas de segurança
- **Sugestões Personalizadas**: Recomendações específicas baseadas nos dados
- **Classificação por Prioridade**: Crítico, Aviso, Sucesso

#### Tendências de Segurança
- Melhorias identificadas
- Riscos detectados

### 4. Tabelas Detalhadas

#### Detalhamento por Perfil
- Lista todos os perfis com quantidade de usuários
- Percentual de participação de cada perfil
- Ordenação por popularidade

#### Uso dos Módulos
- Tabela com todos os módulos do sistema
- Quantidade de perfis com acesso
- Barra de progresso visual do percentual de uso

### 5. Recursos Avançados

#### Exportação de Dados
- Exportação completa em formato JSON
- Inclui dados brutos e análises computadas
- Timestamp da geração

#### Gráficos Interativos
- Exportação individual de cada gráfico em PNG
- Modo tela cheia para visualização ampliada
- Tooltips informativos
- Clique em elementos para ações específicas

## Estrutura Técnica

### Backend (Laravel)

#### Controller: `ProfileAnalyticsDashboardController`
```php
Métodos implementados:
- getDashboardData(): Retorna todos os dados para o dashboard
- getGeneralStats(): Estatísticas gerais do sistema
- getUsersByProfile(): Distribuição de usuários por perfil
- getModulesUsage(): Análise de uso dos módulos
- getPrivilegesDistribution(): Distribuição de privilégios
- getScopeAnalysis(): Análise de escopos de acesso
```

#### Rota API
```
GET /api/profile-analytics/dashboard
```

### Frontend (Vue.js)

#### Componentes Principais

**ProfileAnalyticsDashboardPage.vue**
- Página principal do dashboard
- Gerenciamento de estado e carregamento de dados
- Coordenação entre componentes

**ChartCard.vue**
- Componente reutilizável para gráficos
- Suporte a múltiplos tipos de gráfico (bar, doughnut, pie)
- Funcionalidades: exportação, tela cheia, loading states
- Integração com Chart.js

**SecurityAnalysisCard.vue**
- Análise especializada de segurança
- Cálculos de risco automatizados
- Recomendações inteligentes
- Visualizações de tendências

### Dependências
- **Chart.js 4.5.0**: Biblioteca para gráficos
- **Vue 3.5.16**: Framework frontend
- **Tailwind CSS**: Estilização

## Navegação

### Acesso ao Dashboard
1. **Via Gerenciamento de Perfis**: Botão "Dashboard" na página principal de perfis
2. **URL Direta**: `/perfis/dashboard`
3. **Rota Nomeada**: `profiles.dashboard`

## Análises Fornecidas

### 1. Análise de Distribuição
- Como os usuários estão distribuídos entre os perfis
- Identificação de perfis subutilizados
- Balanceamento de carga de usuários

### 2. Análise de Módulos
- Módulos mais e menos utilizados
- Oportunidades de otimização
- Identificação de funcionalidades negligenciadas

### 3. Análise de Privilégios
- Distribuição de tipos de permissão
- Identificação de padrões de acesso
- Análise de complexidade de permissões

### 4. Análise de Segurança
- Identificação de riscos de segurança
- Recomendações para melhorias
- Monitoramento de tendências

## Benefícios

### Para Administradores
- **Visão Completa**: Entendimento global do sistema de permissões
- **Tomada de Decisão**: Dados para otimização de perfis e privilégios
- **Segurança**: Identificação proativa de riscos

### Para Gestores
- **Relatórios Visuais**: Gráficos e métricas fáceis de entender
- **Exportação**: Capacidade de compartilhar dados
- **Tendências**: Acompanhamento de evolução do sistema

### Para o Sistema
- **Otimização**: Identificação de perfis desnecessários
- **Compliance**: Auditoria de permissões
- **Manutenção**: Dados para limpeza e organização

## Expansões Futuras

### Funcionalidades Planejadas
1. **Filtros Temporais**: Análise de tendências ao longo do tempo
2. **Comparações**: Comparação entre períodos diferentes
3. **Alertas**: Notificações automáticas para anomalias
4. **Drill-down**: Análise detalhada por perfil específico
5. **Auditoria**: Log de mudanças de permissões
6. **API de Métricas**: Endpoints para integração externa

### Melhorias Técnicas
1. **Cache**: Implementação de cache para melhor performance
2. **Lazy Loading**: Carregamento sob demanda de dados
3. **Tempo Real**: Atualizações em tempo real via WebSockets
4. **Responsividade**: Otimização para dispositivos móveis

## Segurança e Privacidade

### Controle de Acesso
- Dashboard protegido por autenticação
- Mesmas permissões do sistema de perfis
- Dados sensíveis tratados adequadamente

### Auditoria
- Logs de acesso ao dashboard
- Rastreamento de exportações
- Monitoramento de uso

---

**Desenvolvimento**: Sistema completo implementado seguindo padrões do CondoAI
**Manutenção**: Código modular e reutilizável para fácil manutenção
**Escalabilidade**: Arquitetura preparada para expansões futuras 