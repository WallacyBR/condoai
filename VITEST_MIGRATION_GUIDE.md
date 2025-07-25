# Guia de Migração: Jest para Vitest

## Configuração Atual

O projeto agora está configurado com Vitest. Arquivos de configuração criados:

- `vitest.config.js` - Configuração principal do Vitest
- `resources/js/tests/setup.js` - Setup global para testes
- Scripts adicionados ao `package.json`

## Principais Diferenças

### 1. Imports
**Antes (Jest):**
```javascript
import { describe, it, expect, jest, beforeEach } from '@jest/globals'
```

**Agora (Vitest):**
```javascript
import { describe, it, expect, vi, beforeEach } from 'vitest'
```

### 2. Mocks
**Antes (Jest):**
```javascript
jest.fn()
jest.mock('module')
```

**Agora (Vitest):**
```javascript
vi.fn()
vi.mock('module')
```

### 3. Arquivo fs
**Antes (Jest):**
```javascript
import fs from 'fs'
const content = fs.readFileSync(fileUrl, 'utf8')
```

**Agora (Vitest):**
```javascript
// Use imports dinâmicos ou mocks
const content = await import(fileUrl)
// Ou use o mock global configurado
const content = fs.readFileSync(fileUrl, 'utf8') // Mock global
```

## Scripts Disponíveis

- `npm run test:vitest` - Executa testes em modo watch
- `npm run test:vitest:run` - Executa testes uma vez
- `npm run test:vitest:ui` - Interface gráfica para testes
- `npm run test:vitest:coverage` - Executa testes com cobertura

## Exemplo de Teste Corrigido

```javascript
import { describe, it, expect, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import MyComponent from '@/components/MyComponent.vue'

describe('MyComponent', () => {
  it('deve renderizar corretamente', () => {
    const wrapper = mount(MyComponent)
    expect(wrapper.exists()).toBe(true)
  })
})
```

## Próximos Passos

1. Corrigir imports nos testes existentes
2. Substituir `jest` por `vi`
3. Corrigir problemas de `fs` usando imports dinâmicos
4. Atualizar caminhos de importação dos componentes
5. Configurar mocks adequados para Vue Router e outros plugins

## Problemas Comuns

### Erro: "fs is not defined"
**Solução:** Use imports dinâmicos ou o mock global configurado

### Erro: "jest is not defined"
**Solução:** Substitua `jest` por `vi` nos imports

### Erro: "Cannot resolve module"
**Solução:** Verifique os aliases no `vitest.config.js` e ajuste os caminhos 