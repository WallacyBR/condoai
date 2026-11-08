# Guia de Troubleshooting - Câmera

## Problema: Câmera não está capturando foto

### 1. Verificar Console do Navegador

Abra o console do navegador (F12) e verifique se há erros relacionados à câmera.

### 2. Testar Câmera Básica

Acesse: `http://localhost:8000/camera-test`

Este teste isolado ajudará a identificar se o problema é:
- Permissões da câmera
- Configuração do navegador
- Hardware da câmera
- Código do componente

**Melhorias implementadas:**
- Logs detalhados em tempo real
- Indicador de status do vídeo
- Teste manual no console
- Melhor tratamento de erros

### 3. Verificações Básicas

#### 3.1 Permissões
- Verifique se o navegador tem permissão para acessar a câmera
- Clique no ícone de câmera na barra de endereços
- Certifique-se de que está "Permitido"

#### 3.2 HTTPS
- A câmera só funciona em HTTPS ou localhost
- Se estiver em produção, certifique-se de usar HTTPS

#### 3.3 Navegador
- Use Chrome, Firefox, Safari ou Edge atualizados
- Evite navegadores antigos

#### 3.4 Hardware
- Verifique se há uma câmera conectada
- Teste se a câmera funciona em outros aplicativos

### 4. Logs de Debug

Os componentes agora têm logs detalhados. Verifique no console e na interface:

**CameraTest:**
```
[CameraTest] Iniciando câmera...
[CameraTest] Solicitando acesso à câmera...
[CameraTest] Stream obtido com sucesso
[CameraTest] Vídeo carregado: 1280x720
[CameraTest] Vídeo iniciado com sucesso
[CameraTest] Câmera ativa
```

**PhotoCapture:**
```
Debug: Iniciando câmera...
Debug: video.value: encontrado
Debug: Solicitando permissão da câmera...
Debug: Stream obtido: sucesso
Debug: Vídeo carregado, dimensões: 1280x720
Debug: Vídeo iniciado com sucesso
Debug: Câmera pronta!
```

### 5. Problemas Comuns

#### 5.1 "Elemento de vídeo não encontrado"
- O modal pode não estar renderizado quando a câmera tenta iniciar
- **Solução implementada:** Aumentado o delay para 500ms e múltiplas estratégias de busca

#### 5.2 "Permissão negada"
- Usuário negou acesso à câmera
- **Solução:** Permitir acesso nas configurações do navegador

#### 5.3 "Câmera não encontrada"
- Não há câmera conectada ou está sendo usada por outro app
- **Solução:** Verificar hardware e fechar outros apps

#### 5.4 "Vídeo não está pronto"
- A câmera ainda está carregando
- **Solução implementada:** Indicador visual de "Aguardando..." até o vídeo estar pronto

#### 5.5 "Botão de captura não funciona"
- **Solução implementada:** Botão desabilitado até o vídeo estar pronto
- Verificar se `isVideoReady` está true

### 6. Melhorias Implementadas

#### 6.1 Melhor Timing
```javascript
// Aumentado delay para garantir renderização do modal
await new Promise(resolve => setTimeout(resolve, 500));
```

#### 6.2 Múltiplas Estratégias de Busca
```javascript
// Busca por múltiplas estratégias
let videoElement = video.value;
if (!videoElement) {
  videoElement = document.querySelector('.camera-video');
}
if (!videoElement) {
  videoElement = document.querySelector('video');
}
```

#### 6.3 Indicador de Status
```javascript
// Novo indicador de prontidão do vídeo
const isVideoReady = ref(false);
// Botão só fica habilitado quando vídeo está pronto
:disabled="cameraLoading || cameraError || !isVideoReady"
```

#### 6.4 Logs Detalhados
```javascript
// Sistema de logs melhorado
const updateDebugInfo = (info) => {
  debugInfo.value = info;
  console.log('Debug:', info);
};
```

### 7. Teste Manual

Execute no console do navegador ou use o botão "Testar Câmera no Console":

```javascript
// Testar se a API está disponível
console.log('MediaDevices:', !!navigator.mediaDevices);
console.log('getUserMedia:', !!navigator.mediaDevices?.getUserMedia);

// Testar acesso à câmera
navigator.mediaDevices.getUserMedia({ video: true })
  .then(stream => {
    console.log('✓ Câmera funcionando!', stream);
    stream.getTracks().forEach(track => {
      console.log('Track:', track.kind, track.label);
      track.stop();
    });
  })
  .catch(error => {
    console.error('✗ Erro na câmera:', error);
  });
```

### 8. Verificações Específicas

#### 8.1 Se o stream aparece mas não captura:
1. Verificar se `isVideoReady` está true
2. Verificar dimensões do vídeo no console
3. Verificar se o canvas está sendo criado corretamente

#### 8.2 Se não salva a foto:
1. Verificar logs do upload no console
2. Verificar se o storage está configurado
3. Verificar permissões de escrita no servidor

#### 8.3 Se o modal não abre:
1. Verificar se o componente está sendo importado
2. Verificar se não há erros de JavaScript
3. Verificar se o Vite está compilando corretamente

### 9. Próximos Passos

1. **Teste em `/camera-test` primeiro**
   - Use o botão "Testar Câmera no Console"
   - Verifique os logs na interface
   - Tente capturar uma foto

2. **Se funcionar no teste:**
   - Teste no formulário de moradores
   - Verifique se há diferenças no ambiente

3. **Se não funcionar no teste:**
   - Verifique permissões do navegador
   - Teste em outro navegador
   - Verifique hardware da câmera

### 10. Comandos Úteis

```bash
# Recompilar assets
npm run build

# Verificar storage link
php artisan storage:link

# Limpar cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### 11. Contato

Se o problema persistir, forneça:
- Logs do console (completo)
- Logs da interface (se disponível)
- Navegador e versão
- Sistema operacional
- Tipo de câmera
- URL onde está testando
- Resultado do teste manual no console 