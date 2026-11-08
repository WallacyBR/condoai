# Glass Scrollbar - Sistema Reutilizável

## 📋 Visão Geral

O sistema Glass Scrollbar é uma solução completa e reutilizável para barras de rolagem com design glass UI que pode ser aplicada em qualquer parte do sistema.

## 🚀 Como Usar

### 1. Importar o CSS Global

Certifique-se de que o arquivo `resources/css/glass-scrollbar.css` está sendo importado no seu arquivo principal de CSS ou no `app.js`:

```javascript
// Em resources/js/app.js ou similar
import '../css/glass-scrollbar.css';
```

### 2. Usando Classes CSS Diretamente

#### Classe Base
```html
<div class="glass-scrollbar max-h-60 overflow-y-auto">
  <!-- Conteúdo com rolagem -->
</div>
```

#### Variantes de Cor
```html
<!-- Cores disponíveis: cyan, green, blue, purple, orange, pink -->
<div class="glass-scrollbar glass-scrollbar-cyan max-h-60 overflow-y-auto">
  <!-- Conteúdo -->
</div>

<div class="glass-scrollbar glass-scrollbar-green max-h-60 overflow-y-auto">
  <!-- Conteúdo -->
</div>

<div class="glass-scrollbar glass-scrollbar-blue max-h-60 overflow-y-auto">
  <!-- Conteúdo -->
</div>
```

#### Tamanhos
```html
<!-- Tamanhos: sm (6px), md (10px), lg (14px), xl (18px) -->
<div class="glass-scrollbar glass-scrollbar-sm max-h-60 overflow-y-auto">
  <!-- Barra fina -->
</div>

<div class="glass-scrollbar glass-scrollbar-lg max-h-60 overflow-y-auto">
  <!-- Barra grossa -->
</div>
```

#### Efeitos Adicionais
```html
<!-- Com efeito de fade -->
<div class="glass-scrollbar glass-scrollbar-fade max-h-60 overflow-y-auto relative">
  <!-- Conteúdo -->
</div>

<!-- Com animação de itens -->
<div class="glass-scrollbar glass-scrollbar-items max-h-60 overflow-y-auto">
  <ul>
    <li>Item 1</li>
    <li>Item 2</li>
  </ul>
</div>
```

### 3. Usando o Componente Vue

#### Importar o Componente
```javascript
import GlassScrollContainer from './components/GlassScrollContainer.vue';

export default {
  components: {
    GlassScrollContainer
  }
}
```

#### Uso Básico
```vue
<template>
  <GlassScrollContainer>
    <ul>
      <li v-for="item in items" :key="item.id">
        {{ item.name }}
      </li>
    </ul>
  </GlassScrollContainer>
</template>
```

#### Com Configurações
```vue
<template>
  <GlassScrollContainer 
    color="cyan"
    size="lg"
    max-height="80"
    show-fade
    animate-items
    extra-class="my-custom-class"
  >
    <ul>
      <li v-for="item in items" :key="item.id">
        {{ item.name }}
      </li>
    </ul>
  </GlassScrollContainer>
</template>
```

## 🎨 Propriedades do Componente

| Propriedade | Tipo | Padrão | Descrição |
|-------------|------|--------|-----------|
| `color` | String | `'default'` | Cor da barra: `default`, `cyan`, `green`, `blue`, `purple`, `orange`, `pink` |
| `size` | String | `'md'` | Tamanho da barra: `sm`, `md`, `lg`, `xl` |
| `maxHeight` | String | `'60'` | Altura máxima em rem ou px |
| `showFade` | Boolean | `false` | Mostrar efeito de fade no final |
| `animateItems` | Boolean | `false` | Animar itens da lista no hover |
| `paddingRight` | String | `'2'` | Padding direito para a barra |
| `extraClass` | String | `''` | Classes CSS adicionais |

## 🎯 Exemplos Práticos

### Lista de Usuários
```vue
<template>
  <GlassScrollContainer color="purple" show-fade animate-items>
    <div class="space-y-2">
      <div 
        v-for="user in users" 
        :key="user.id"
        class="bg-gray-800/50 p-3 rounded-lg flex items-center space-x-3"
      >
        <img :src="user.avatar" class="w-8 h-8 rounded-full" />
        <span>{{ user.name }}</span>
      </div>
    </div>
  </GlassScrollContainer>
</template>
```

### Tabela de Dados
```vue
<template>
  <GlassScrollContainer color="blue" size="sm">
    <table class="w-full">
      <thead>
        <tr>
          <th>Nome</th>
          <th>Email</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="user in users" :key="user.id">
          <td>{{ user.name }}</td>
          <td>{{ user.email }}</td>
          <td>{{ user.status }}</td>
        </tr>
      </tbody>
    </table>
  </GlassScrollContainer>
</template>
```

### Chat/Mensagens
```vue
<template>
  <GlassScrollContainer color="green" show-fade>
    <div class="space-y-4">
      <div 
        v-for="message in messages" 
        :key="message.id"
        class="bg-gray-800/50 p-4 rounded-lg"
      >
        <div class="font-semibold">{{ message.author }}</div>
        <div>{{ message.content }}</div>
        <div class="text-xs text-gray-400">{{ message.time }}</div>
      </div>
    </div>
  </GlassScrollContainer>
</template>
```

## 🔧 Personalização Avançada

### Criando Variantes Customizadas

Para criar uma nova variante de cor, adicione ao CSS:

```css
.glass-scrollbar-custom::-webkit-scrollbar-thumb {
  background: rgba(255, 0, 0, 0.15);
  border-color: rgba(255, 0, 0, 0.25);
}

.glass-scrollbar-custom::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 0, 0, 0.25);
  border-color: rgba(255, 0, 0, 0.4);
  box-shadow: 
    0 8px 24px rgba(255, 0, 0, 0.3),
    inset 0 4px 12px rgba(255, 0, 0, 0.1),
    0 2px 8px rgba(255, 0, 0, 0.15);
}

.glass-scrollbar-custom {
  scrollbar-color: rgba(255, 0, 0, 0.15) rgba(255, 255, 255, 0.05);
}
```

### Modificando Animações

Para personalizar as animações dos itens:

```css
.glass-scrollbar-items li {
  transition: all 0.3s ease; /* Animação mais rápida */
}

.glass-scrollbar-items li:hover {
  transform: translateY(-2px) scale(1.01); /* Movimento vertical */
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}
```

## 🌐 Compatibilidade

- ✅ Chrome/Chromium
- ✅ Safari
- ✅ Firefox
- ✅ Edge
- ✅ Opera

## 📱 Responsividade

O sistema funciona perfeitamente em dispositivos móveis, mas você pode ajustar os tamanhos:

```vue
<template>
  <GlassScrollContainer 
    :size="$screen.md ? 'md' : 'sm'"
    :max-height="$screen.md ? '60' : '40'"
  >
    <!-- Conteúdo -->
  </GlassScrollContainer>
</template>
```

## 🎨 Integração com Tailwind

O sistema é totalmente compatível com Tailwind CSS e pode ser usado em conjunto:

```vue
<template>
  <GlassScrollContainer 
    color="cyan"
    extra-class="bg-gray-900 border border-gray-700 rounded-lg"
  >
    <!-- Conteúdo -->
  </GlassScrollContainer>
</template>
```

## 🔍 Debugging

Para verificar se o overflow está funcionando:

```javascript
// No componente Vue
const scrollRef = ref(null);

onMounted(() => {
  console.log('Has overflow:', scrollRef.value.hasOverflow);
  scrollRef.value.checkOverflow();
});
```

```vue
<template>
  <GlassScrollContainer ref="scrollRef" show-fade>
    <!-- Conteúdo -->
  </GlassScrollContainer>
</template>
```

## 📝 Notas Importantes

1. **Performance**: O sistema usa `ResizeObserver` para detectar mudanças de conteúdo automaticamente
2. **Acessibilidade**: Mantém a funcionalidade padrão de rolagem do navegador
3. **Fallback**: Em navegadores que não suportam `backdrop-filter`, o sistema funciona sem os efeitos glass
4. **DRY**: Segue o princípio DRY, permitindo reutilização em todo o sistema

## 🚀 Próximos Passos

- [ ] Adicionar suporte a rolagem horizontal
- [ ] Criar variantes de tema (dark/light)
- [ ] Adicionar animações de entrada/saída
- [ ] Suporte a rolagem suave programática 