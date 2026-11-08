# Determinação Automática de Condomínio

## Visão Geral

O sistema agora suporta determinação automática do condomínio em formulários, eliminando a necessidade de seleção manual quando o contexto já está definido.

## Como Funciona

### 1. Contexto Multi-Tenant (Subdomínio)
Quando você acessa `condominio1.seudominio.com`, o `TenantMiddleware` automaticamente define o condomínio baseado no subdomínio.

### 2. Contexto de Seleção Manual
Quando você acessa o sistema principal e seleciona um condomínio, ele fica armazenado no `localStorage`.

### 3. Determinação Automática
O sistema verifica automaticamente qual condomínio usar na seguinte ordem:
1. Condomínio fornecido explicitamente na requisição
2. Condomínio definido pelo tenant middleware
3. Condomínio na requisição (atributos)
4. Condomínio do usuário autenticado
5. Lança exceção se nenhum estiver disponível

## Implementação no Backend

### Controller (Exemplo: ResidentController)

```php
/**
 * Determina o ID do condomínio atual baseado no contexto da requisição
 * Segue o princípio DRY para reutilização em diferentes métodos
 */
private function getCurrentCondominiumId(Request $request): int
{
    // 1. Verificar se foi fornecido explicitamente na requisição
    if ($request->has('condominium_id') && $request->condominium_id) {
        return (int) $request->condominium_id;
    }
    
    // 2. Verificar se há um condomínio definido pelo tenant middleware
    if (app()->bound('currentCondominium')) {
        return app('currentCondominium')->id;
    }
    
    // 3. Verificar se há um condomínio na requisição (atributos)
    if ($request->attributes->has('condominium')) {
        return $request->attributes->get('condominium')->id;
    }
    
    // 4. Fallback: usar o condomínio do usuário autenticado
    if (auth()->check() && auth()->user()->condominium_id) {
        return auth()->user()->condominium_id;
    }
    
    // 5. Se nada estiver disponível, lançar exceção
    throw new \InvalidArgumentException('Não foi possível determinar o condomínio atual.');
}
```

### Validação

```php
$validated = $request->validate([
    // ... outros campos ...
    'condominium_id' => [
        'sometimes', // Mudado de 'required' para 'sometimes'
        'integer',
        Rule::exists('condominiums','id')->where(function ($query) {
            return $query->where('active', true);
        })
    ],
]);

// Definir o condomínio automaticamente se não foi fornecido
if (!isset($validated['condominium_id'])) {
    $validated['condominium_id'] = $this->getCurrentCondominiumId($request);
}
```

## Implementação no Frontend

### Composable (useCondominiumFilter.js)

```javascript
import { ref, computed } from 'vue';

export function useCondominiumFilter() {
  const selectedCondominiumId = ref(localStorage.getItem('selectedCondominium'));
  const selectedCondominiumName = ref(localStorage.getItem('selectedCondominiumName'));

  const isCondominiumAutoDetermined = computed(() => {
    return !!selectedCondominiumId.value;
  });

  const shouldLoadCondominiums = () => {
    return !isCondominiumAutoDetermined.value;
  };

  const getCurrentCondominiumId = () => {
    return selectedCondominiumId.value;
  };

  const getCurrentCondominiumName = () => {
    return selectedCondominiumName.value;
  };

  return {
    selectedCondominiumId,
    selectedCondominiumName,
    isCondominiumAutoDetermined,
    shouldLoadCondominiums,
    getCurrentCondominiumId,
    getCurrentCondominiumName,
  };
}
```

### Componente Vue

```vue
<template>
  <!-- Campo de condomínio (só aparece se não for determinado automaticamente) -->
  <div v-if="!isCondominiumAutoDetermined" class="space-y-4">
    <h3>Condomínio</h3>
    <select v-model="form.condominium_id" required>
      <option value="">Selecione um condomínio</option>
      <option v-for="condo in condominiums" :key="condo.id" :value="condo.id">
        {{ condo.name }}
      </option>
    </select>
  </div>

  <!-- Informação do condomínio atual (só aparece se for determinado automaticamente) -->
  <div v-if="isCondominiumAutoDetermined" class="space-y-4">
    <h3>Condomínio</h3>
    <div class="bg-indigo-500/10 border border-indigo-500/30 rounded-lg p-4">
      <span>Condomínio: {{ selectedCondominiumName }}</span>
      <p class="text-sm text-gray-400">
        O condomínio foi determinado automaticamente com base no seu contexto atual.
      </p>
    </div>
  </div>
</template>

<script>
import { useCondominiumFilter } from '../composables/useCondominiumFilter.js';

export default {
  setup() {
    const {
      selectedCondominiumId,
      selectedCondominiumName,
      isCondominiumAutoDetermined,
      shouldLoadCondominiums,
      getCurrentCondominiumId,
    } = useCondominiumFilter();

    const form = reactive({
      // ... outros campos ...
      condominium_id: getCurrentCondominiumId() || '',
    });

    const loadCondominiums = async () => {
      if (!shouldLoadCondominiums()) {
        console.log('Condomínio determinado automaticamente, pulando carregamento');
        return;
      }
      
      // Carregar lista de condomínios...
    };

    const handleSubmit = async () => {
      const formData = new FormData();
      
      // Não enviar condominium_id se for determinado automaticamente
      for (const key in cleanedForm) {
        if (key === 'condominium_id' && isCondominiumAutoDetermined.value) {
          continue;
        }
        formData.append(key, cleanedForm[key]);
      }
      
      // Enviar requisição...
    };

    onMounted(async () => {
      if (shouldLoadCondominiums()) {
        await loadCondominiums();
      }
    });

    return {
      form,
      isCondominiumAutoDetermined,
      selectedCondominiumName,
      // ... outros retornos
    };
  }
};
</script>
```

## Benefícios

1. **Melhor UX**: Usuários não precisam selecionar condomínio repetidamente
2. **Menos Erros**: Elimina possibilidade de selecionar condomínio errado
3. **Código DRY**: Reutilização de lógica em diferentes componentes
4. **Flexibilidade**: Suporta tanto multi-tenant quanto seleção manual
5. **Segurança**: Validação automática no backend

## Casos de Uso

- **Formulários de Moradores**: Não precisa selecionar condomínio
- **Formulários de Unidades**: Condomínio determinado automaticamente
- **Formulários de Áreas Comuns**: Contexto já definido
- **Relatórios**: Filtro automático por condomínio

## Migração de Formulários Existentes

1. Importar o composable `useCondominiumFilter`
2. Usar `shouldLoadCondominiums()` para decidir se carregar lista
3. Usar `isCondominiumAutoDetermined` para mostrar/esconder campo
4. Não enviar `condominium_id` quando determinado automaticamente
5. Atualizar validação no backend para aceitar `sometimes` em vez de `required` 