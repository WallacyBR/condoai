# PrediHub - Progressive Web App (PWA)

## Visão Geral

O PrediHub foi transformado em um Progressive Web App (PWA) completo, oferecendo uma experiência de aplicativo nativo com funcionalidades offline, notificações push e instalação no dispositivo.

## Funcionalidades PWA Implementadas

### 🚀 **Instalação como App**
- Banner de instalação automático
- Ícones adaptativos para diferentes tamanhos
- Manifesto PWA configurado
- Suporte para iOS e Android

### 📱 **Funcionalidades Offline**
- Service Worker personalizado
- Cache inteligente de recursos
- Página offline customizada
- Sincronização em background

### 🔔 **Notificações Push**
- Sistema de notificações local
- Permissões configuráveis
- Notificações específicas por evento
- Integração com eventos do condomínio

### ⚡ **Performance**
- Cache de assets estáticos
- Estratégias de cache otimizadas
- Carregamento rápido
- Atualizações automáticas

## Estrutura de Arquivos

```
predihub/
├── resources/
│   ├── js/
│   │   ├── composables/
│   │   │   └── usePWA.js              # Composable para funcionalidades PWA
│   │   ├── components/
│   │   │   ├── PWAInstallBanner.vue   # Banner de instalação
│   │   │   └── PWASettings.vue        # Configurações PWA
│   │   ├── utils/
│   │   │   └── notifications.js       # Gerenciador de notificações
│   │   └── sw.js                      # Service Worker
│   └── views/
│       └── offline.blade.php          # Página offline
├── public/
│   ├── masked-icon.svg               # Ícone mascarado
│   ├── pwa-192x192.png              # Ícone 192x192
│   ├── pwa-512x512.png              # Ícone 512x512
│   └── apple-touch-icon.png         # Ícone para iOS
├── routes/
│   ├── web.php                      # Rota offline
│   └── api.php                      # Health check
└── vite.config.js                   # Configuração PWA
```

## Configuração

### 1. Dependências Instaladas
```bash
npm install --save-dev vite-plugin-pwa workbox-window
```

### 2. Configuração do Vite
O arquivo `vite.config.js` foi configurado com:
- Plugin PWA do Vite
- Estratégias de cache personalizadas
- Manifesto PWA
- Service Worker automático

### 3. Service Worker
O Service Worker (`resources/js/sw.js`) implementa:
- Cache de recursos estáticos
- Cache de requisições da API
- Estratégias de fallback offline
- Gerenciamento de notificações push

## Funcionalidades Detalhadas

### 📋 **Banner de Instalação**
- Aparece automaticamente quando o app pode ser instalado
- Pode ser descartado pelo usuário
- Lembra-se da preferência do usuário
- Design responsivo e atrativo

### 🔧 **Configurações PWA**
- Status de instalação
- Controle de notificações
- Gerenciamento de cache
- Sincronização manual
- Informações do app

### 📨 **Sistema de Notificações**
- Notificações para novos moradores
- Alertas de encomendas
- Reservas de áreas comuns
- Vagas de estacionamento
- Manutenções urgentes
- Visitas registradas

### 🌐 **Modo Offline**
- Página offline customizada
- Cache de dados visitados
- Funcionalidades básicas disponíveis
- Sincronização automática quando online

## Como Usar

### Para Desenvolvedores

1. **Desenvolvimento**
   ```bash
   npm run dev
   ```

2. **Build para Produção**
   ```bash
   npm run build
   ```

3. **Testar PWA**
   - Use o Chrome DevTools > Application
   - Verifique o Service Worker
   - Teste o manifesto
   - Simule modo offline

### Para Usuários

1. **Instalar o App**
   - Acesse o site no Chrome/Edge
   - Clique em "Instalar" no banner
   - Ou use o menu do navegador

2. **Configurar Notificações**
   - Vá em Configurações > App
   - Ative as notificações
   - Configure preferências

3. **Usar Offline**
   - O app funciona sem internet
   - Dados básicos ficam em cache
   - Sincroniza quando online

## Eventos de Notificação

O sistema envia notificações para:

- **Novos Moradores**: `notificationManager.sendNewResidentNotification()`
- **Encomendas**: `notificationManager.sendNewPackageNotification()`
- **Reservas**: `notificationManager.sendCommonAreaBookingNotification()`
- **Estacionamento**: `notificationManager.sendParkingSpotNotification()`
- **Manutenção**: `notificationManager.sendMaintenanceNotification()`
- **Visitas**: `notificationManager.sendVisitNotification()`

## Estratégias de Cache

### Cache Estático
- CSS, JS, imagens
- Fontes e ícones
- Páginas visitadas

### Cache Dinâmico
- Requisições da API
- Dados do usuário
- Configurações

### Cache de Rede
- Google Fonts
- Recursos externos
- APIs de terceiros

## Compatibilidade

### Navegadores Suportados
- ✅ Chrome 67+
- ✅ Edge 79+
- ✅ Firefox 67+
- ✅ Safari 11.1+
- ✅ Opera 54+

### Dispositivos
- ✅ Desktop
- ✅ Tablet
- ✅ Mobile (iOS/Android)

## Monitoramento

### Health Check
Endpoint: `GET /api/health`
- Verifica conectividade
- Status do banco de dados
- Versão do app

### Logs do Service Worker
- Instalação/ativação
- Cache operations
- Notificações
- Erros

## Próximos Passos

### Melhorias Futuras
- [ ] Push notifications server-side
- [ ] Background sync avançado
- [ ] Cache inteligente
- [ ] Analytics PWA
- [ ] Testes automatizados

### Otimizações
- [ ] Lazy loading de componentes
- [ ] Preload de recursos críticos
- [ ] Compressão de assets
- [ ] CDN para recursos estáticos

## Troubleshooting

### Problemas Comuns

1. **Service Worker não registra**
   - Verifique HTTPS
   - Limpe cache do navegador
   - Verifique console para erros

2. **Notificações não funcionam**
   - Verifique permissões
   - Teste em modo incógnito
   - Verifique se é HTTPS

3. **App não instala**
   - Verifique critérios de instalação
   - Teste em diferentes navegadores
   - Verifique manifesto

### Debug
```javascript
// Verificar status do PWA
console.log('PWA Status:', navigator.serviceWorker.controller);

// Verificar cache
caches.keys().then(names => console.log('Caches:', names));

// Verificar notificações
console.log('Notification permission:', Notification.permission);
```

## Contribuição

Para contribuir com melhorias PWA:

1. Teste em diferentes dispositivos
2. Verifique performance
3. Documente mudanças
4. Atualize este README

---

**PrediHub PWA** - Transformando a gestão de condomínios em uma experiência mobile-first! 🏢📱 