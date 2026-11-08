# Manual de Design - CondoAI

## Guia de Interface e Experiência do Usuário (SaaS Moderno & Sóbrio)

### 📋 Índice
1. [Filosofia de Design](#filosofia-de-design)
2. [Suporte a Temas: Claro e Escuro](#suporte-a-temas-claro-e-escuro)
3. [Sistema de Cores & Design Tokens](#sistema-de-cores--design-tokens)
4. [Tipografia & Hierarquia](#tipografia--hierarquia)
5. [Padrões de Espaçamento e Bordas](#padrões-de-espaçamento-e-bordas)
6. [Componentes Padrão da Interface](#componentes-padrão-da-interface)
   - [AppTopBar & Barra de Navegação](#apptopbar--barra-de-navegação)
   - [Menu Lateral (Sidebar)](#menu-lateral-sidebar)
   - [PageHeader & TopCard](#pageheader--topcard)
   - [AppButton & Ações](#appbutton--ações)
   - [StatsCard & Métricas](#statscard--métricas)
   - [Formulários & Inputs](#formulários--inputs)
   - [Tabelas de Dados](#tabelas-de-dados)
   - [Badges de Status](#badges-de-status)
   - [EmptyState & Loading](#emptystate--loading)
   - [Modais](#modais)
7. [Diretrizes de Acessibilidade & Responsividade](#diretrizes-de-acessibilidade--responsividade)

---

## 🎨 Filosofia de Design

O CondoAI adota um design visual **SaaS moderno, corporativo e sóbrio** (inspirado em referências como Tailwind UI, Linear e Stripe). 

### Princípios Fundamentais:
1. **Sobriedade e Profissionalismo**: Eliminação de efeitos retrô, reflexos de vidro excessivos, bordas fluorescentes de 2px e degradês neon chamativos (ex: ciano para rosa/magenta). O foco é no conteúdo e nos dados operacionais do condomínio.
2. **Consistência Dual (Modo Claro & Escuro)**: Todas as telas, componentes, inputs e tabelas devem funcionar com legibilidade impecável tanto no tema claro quanto no escuro. Nunca use cores de fundo ou texto fixas sem as classes variantes `dark:`.
3. **Consistência de Raios de Borda**:
   - `rounded-lg` (8px): Para botões, inputs, selects e abas.
   - `rounded-2xl` (16px): Para cards de métricas, painéis, modais e containers de seção (arredondamento moderno e suave, evitando cantos agudos/quadrados sem cair no exagero de bordas circulares).
   - `rounded-full`: Exclusivamente para avatares, badges de status e toggles/switches circulares.
4. **Sem Efeitos Exagerados de Hover**: Nunca utilize `hover:scale-105` ou transformações espalhafatosas em cards. Utilize transições suaves de cor de borda (`transition-colors hover:border-slate-300 dark:hover:border-slate-700`).

---

## 🌗 Suporte a Temas: Claro e Escuro

O sistema gerencia o tema através do utilitário `useTheme.js` (`light`, `dark` e `system`), aplicando a classe `.dark` no elemento `<html>`.

### Convenções de Camadas:
| Camada | Modo Claro | Modo Escuro |
| :--- | :--- | :--- |
| **Fundo da Aplicação** | `bg-slate-100` | `dark:bg-slate-950` |
| **Cards & Containers** | `bg-white` | `dark:bg-slate-900` |
| **Bordas Estruturais** | `border-slate-200` | `dark:border-slate-800` |
| **Fundo Secundário/Inputs** | `bg-slate-50` / `bg-white` | `dark:bg-slate-800` / `dark:bg-slate-800/60` |
| **Texto Primário** | `text-slate-900` | `dark:text-slate-100` |
| **Texto Secundário** | `text-slate-500` | `dark:text-slate-400` |
| **Texto Desabilitado** | `text-slate-400` | `dark:text-slate-500` |

---

## 🎯 Sistema de Cores & Design Tokens

### Cores Primárias (Ações & Identidade):
- **Brand / Indigo**: Utilizada para botões primários, destaques e links de ação.
  - Claro: `bg-indigo-600 hover:bg-indigo-700 text-white`
  - Escuro: `dark:bg-indigo-600 dark:hover:bg-indigo-500 text-white`
  - Badges: `bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-800/50`

### Cores Semânticas:
- **Sucesso (Verde / Emerald)**:
  - Badge: `bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/50`
  - Ícone: `text-emerald-600 dark:text-emerald-400`
- **Aviso / Pendente (Âmbar / Amarelo)**:
  - Badge: `bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800/50`
  - Ícone: `text-amber-600 dark:text-amber-400`
- **Perigo / Destrutivo (Rosa / Rose)**:
  - Botão Danger: `bg-rose-600 hover:bg-rose-700 text-white`
  - Badge: `bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800/50`
  - Alerta: `bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800/50 text-rose-700 dark:text-rose-300`
- **Neutro / Inativo (Slate)**:
  - Badge: `bg-slate-100 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700`

---

## 🖋️ Tipografia & Hierarquia

A tipografia do sistema baseia-se na fonte do sistema (Inter / Sans-serif nativa):

- **Títulos de Página (H1)**: `text-xl font-bold text-slate-900 dark:text-slate-100`
- **Títulos de Seção / Cards (H2/H3)**: `text-base font-semibold text-slate-900 dark:text-slate-100`
- **Subtítulos & Textos de Apoio**: `text-xs text-slate-500 dark:text-slate-400`
- **Labels de Formulário**: `text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300`
- **Conteúdo de Tabelas / Textos Gerais**: `text-sm text-slate-700 dark:text-slate-300`
- **Valores Numéricos em Destaque**: `text-2xl font-bold text-slate-900 dark:text-slate-100`

---

## 🧱 Componentes Padrão da Interface

### 1. AppTopBar & Barra de Navegação
O topo do aplicativo (`AppTopBar.vue`) concentra:
- **Breadcrumbs**: Navegação estrutural clara da localização atual.
- **Badge do Condomínio**: Identificador sóbrio do condomínio ativo.
- **Seletor de Tema**: Alternador rápido entre Claro, Escuro e Automático (Sistema).
- **Notificações**: Popover limpo com lista de avisos recentes.
- **Menu do Usuário**: Acesso rápido a perfil, configurações e logout.

### 2. Menu Lateral (Sidebar)
- Fundo: `bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800`.
- Botão de colapso: Integrado perfeitamente na parte inferior da barra lateral (nunca flutuando solto na tela).
- Item selecionado: Fundo sutil `bg-indigo-50 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300 border-l-2 border-indigo-600 font-semibold`.

### 3. PageHeader & TopCard
Utilizado no topo das telas para identificação e ações primárias:
```html
<PageHeader
  title="Moradores"
  subtitle="Gerencie os moradores, inquilinos e proprietários do condomínio"
>
  <template #actions>
    <AppButton variant="primary" @click="openModal">
      Novo Morador
    </AppButton>
  </template>
</PageHeader>
```

### 4. AppButton & Ações
O componente `AppButton.vue` padroniza todos os botões do sistema:
- `variant="primary"`: Fundo índigo sólido para a ação principal.
- `variant="secondary"`: Fundo neutro com borda suave para ações secundárias.
- `variant="outline"`: Fundo transparente com borda para ações neutras.
- `variant="ghost"`: Sem borda nem fundo fixo, com hover suave para ações contextuais.
- `variant="danger"`: Fundo vermelho sólido para exclusões e cancelamentos críticos.
- `size="sm"` (px-3 py-1.5 text-xs) e `size="md"` (px-4 py-2.5 text-sm).

### 5. StatsCard & Métricas
Substitui os antigos "rainbow cards" com gradientes fluorescentes por cartões sóbrios e legíveis:
```html
<StatsCard
  label="Total de Moradores"
  :value="totalResidents"
  variant="default"
  :icon="UserIcon"
/>
```

### 6. Formulários & Inputs
Todos os campos seguem o mesmo padrão:
```html
<input
  type="text"
  class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
/>
```

### 7. Tabelas de Dados
- Container: `bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden`.
- Cabeçalho: `bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider`.
- Linhas: `hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors text-sm`.

### 8. EmptyState & Loading
- Para telas ou listas vazias, utilize o componente neutro `EmptyState.vue` com ilustração/SVG sutil, título explicativo e botão de ação sugerida.
- Indicadores de carregamento: Spinners limpos com borda fina `border-2 border-slate-300 border-t-indigo-600 dark:border-slate-700 dark:border-t-indigo-400`.

### 9. Modais
- Fundo do overlay: `fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4`.
- Caixa do modal: `bg-white dark:bg-slate-900 rounded-xl shadow-xl border border-slate-200 dark:border-slate-800 p-6`.

---

## 📱 Diretrizes de Acessibilidade & Responsividade

1. **Responsividade Mobile-First**:
   - Menus com suporte a navegação recolhível em telas menores que 1024px (`lg`).
   - Tabelas com barra horizontal de rolagem em telas estreitas (`overflow-x-auto`).
   - Botões de ação com alinhamento e empilhamento flexível (`flex-col sm:flex-row`).
2. **Contraste de Cores**:
   - Todo texto deve atingir a relação mínima de contraste de 4.5:1 (WCAG AA).
   - Não use tons de cinza claro sobre fundos brancos nem cinzas escuros sobre fundos escuros.
3. **Foco e Teclado**:
   - Elementos interativos devem fornecer anel de foco visível com `focus:ring-2 focus:ring-indigo-500 focus:outline-none`.
