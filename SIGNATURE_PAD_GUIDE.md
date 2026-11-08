# Guia do Componente SignaturePad

## Visão Geral

O componente `SignaturePad` utiliza a biblioteca `szimek/signature_pad` para capturar assinaturas digitais de forma intuitiva e responsiva. Ele é especialmente útil para formulários que requerem assinaturas de técnicos, moradores ou administradores.

## Instalação

A biblioteca `signature_pad` já está instalada no projeto:

```bash
npm install signature_pad
```

## Uso Básico

```vue
<template>
  <SignaturePad 
    v-model="signature"
    :width="400"
    :height="200"
    @change="onSignatureChange"
  />
</template>

<script setup>
import SignaturePad from '@/components/SignaturePad.vue';
import { ref } from 'vue';

const signature = ref(null);

const onSignatureChange = (changeData) => {
  console.log('Assinatura mudou:', changeData);
};
</script>
```

## Props

| Prop | Tipo | Padrão | Descrição |
|------|------|--------|-----------|
| `width` | Number | 400 | Largura do canvas em pixels |
| `height` | Number | 200 | Altura do canvas em pixels |
| `penColor` | String | '#00d4ff' | Cor da caneta (formato hex) |
| `backgroundColor` | String | 'rgba(0, 0, 0, 0)' | Cor de fundo do canvas |
| `minWidth` | Number | 0.5 | Largura mínima da linha |
| `maxWidth` | Number | 2.5 | Largura máxima da linha |
| `throttle` | Number | 16 | Intervalo de throttling em ms |

## Events

| Event | Payload | Descrição |
|-------|---------|-----------|
| `update:modelValue` | String \| null | Emitido quando a assinatura muda (v-model) |
| `change` | Object | Emitido com dados sobre a mudança |

### Payload do Event `change`

```javascript
{
  isEmpty: boolean,    // Se a assinatura está vazia
  dataUrl: string|null // Data URL da assinatura (se não estiver vazia)
}
```

## Métodos Expostos

O componente expõe os seguintes métodos através de `ref`:

### `clear()`
Limpa completamente a assinatura.

### `undo()`
Desfaz a última ação de desenho.

### `isEmpty()`
Retorna `true` se a assinatura estiver vazia.

### `getSignatureDataUrl(type, encoderOptions)`
Retorna a assinatura como data URL.

**Parâmetros:**
- `type` (String): Tipo MIME da imagem (padrão: 'image/png')
- `encoderOptions` (Number): Qualidade da imagem (0-1, padrão: 0.92)

### `getSignatureData()`
Retorna os dados brutos da assinatura (array de pontos).

### `fromData(data)`
Carrega uma assinatura a partir de dados existentes.

## Exemplo Completo

```vue
<template>
  <div>
    <label>Assinatura:</label>
    <SignaturePad 
      ref="signaturePadRef"
      v-model="formData.signature"
      :width="400"
      :height="200"
      pen-color="#00d4ff"
      @change="onSignatureChange"
    />
    
    <div class="controls">
      <button @click="clearSignature">Limpar</button>
      <button @click="undoSignature" :disabled="!canUndo">Desfazer</button>
      <button @click="saveSignature">Salvar</button>
    </div>
  </div>
</template>

<script setup>
import SignaturePad from '@/components/SignaturePad.vue';
import { ref, reactive } from 'vue';

const signaturePadRef = ref(null);
const canUndo = ref(false);

const formData = reactive({
  signature: null
});

const onSignatureChange = (changeData) => {
  canUndo.value = !changeData.isEmpty;
};

const clearSignature = () => {
  signaturePadRef.value?.clear();
};

const undoSignature = () => {
  signaturePadRef.value?.undo();
};

const saveSignature = () => {
  if (signaturePadRef.value && !signaturePadRef.value.isEmpty()) {
    const dataUrl = signaturePadRef.value.getSignatureDataUrl();
    console.log('Assinatura salva:', dataUrl);
  }
};
</script>
```

## Características Técnicas

### Responsividade
- Suporte a telas de alta resolução (retina)
- Configuração automática do device pixel ratio
- Funciona em dispositivos touch e desktop

### Performance
- Throttling configurável para otimizar performance
- Limpeza automática de event listeners
- Renderização otimizada do canvas

### Acessibilidade
- Suporte a navegação por teclado
- Indicadores visuais de estado
- Botões com estados disabled apropriados

## Integração com Backend

Para enviar a assinatura para o backend:

```javascript
const submitForm = async () => {
  const formData = new FormData();
  
  if (signaturePadRef.value && !signaturePadRef.value.isEmpty()) {
    const signatureDataUrl = signaturePadRef.value.getSignatureDataUrl();
    formData.append('signature', signatureDataUrl);
  }
  
  await api.post('/endpoint', formData);
};
```

## Estilização

O componente usa classes Tailwind CSS e pode ser customizado:

```css
.signature-canvas {
  @apply border border-gray-600 rounded-lg bg-gray-700/20;
}

.signature-controls button {
  @apply px-3 py-1 text-xs bg-gray-600 hover:bg-gray-500 text-white rounded;
}
```

## Troubleshooting

### Problema: Canvas não renderiza
- Verifique se o elemento canvas está montado
- Confirme se as dimensões estão definidas

### Problema: Assinatura não aparece
- Verifique se a cor da caneta não é transparente
- Confirme se o backgroundColor não está cobrindo a assinatura

### Problema: Performance lenta
- Ajuste o valor de `throttle`
- Reduza a resolução do canvas se necessário 