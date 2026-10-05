# Auditoria frontend e lista de correcao - PrediHub

Atualizado em: 2026-06-26

## Regras de execucao

- Marcar cada item como `[x]` somente depois de corrigir e validar.
- Registrar validacao executada logo abaixo do item corrigido.
- Se uma validacao travar ou falhar por oito tentativas consecutivas, desistir daquele teste especifico e documentar a razao.
- Seguir `DESIGN_MANUAL.md` em qualquer alteracao visual.
- Manter componentes em `resources/js` e testes Jest em `resources/tests`.

## Status geral

- [x] Auditoria inicial frontend executada.
- [x] Harness de testes frontend estabilizado.
- [ ] Falhas de seguranca e dividas tecnicas frontend corrigidas parcialmente.
- [ ] Falhas de cadastro, edicao, consulta e delecao frontend por modulo corrigidas parcialmente.
- [ ] Falhas de integracao frontend/backend corrigidas parcialmente.
- [x] Gaps de regra de negocio no frontend corrigidos nesta leva.

## 1. Falhas de seguranca e dividas tecnicas

- [x] FE-SEC-001 - Tela de login salva senha em texto claro no `localStorage` quando "Lembrar-me" esta marcado.
  - Evidencia: `resources/js/components/LoginView.vue` usa `rememberedPassword`.
  - Impacto: qualquer script no dominio pode recuperar a senha; tambem aumenta risco em maquinas compartilhadas.
  - Correcao: lembrar-me passou a persistir somente email e remove qualquer `rememberedPassword` legado.
  - Validacao: `npm test -- --runInBand resources/tests/LoginView.spec.js` passou com 3 testes; busca estatica nao encontrou persistencia de senha, apenas remocao/validacao no teste.

- [ ] FE-SEC-002 - Muitos componentes leem token e condominio diretamente do `localStorage`, duplicando autorizacao/tenant.
  - Evidencia: `rg "localStorage.getItem('token'|selectedCondominium)" resources/js`.
  - Impacto: acoplamento alto, comportamento inconsistente e risco de enviar `condominium_id` obsoleto ao backend.
  - Progresso: areas comuns, reservas, pets, veiculos, vagas de garagem e listagem/edicao/modal de unidades foram migrados para `apiRequest` nos fluxos principais corrigidos sem token/tenant manual; dados fake e logs de debug das listagens de unidades foram removidos.
  - Validacao parcial: specs de `CommonAreasView`, `NewBookingModal`, `BookingsPage`, `BookingFormPage`, `CommonAreaCalendar`, `CommonAreaBookingForm`, `AreaBookingsPage`, `BookingDetailPage`, `CommonAreaFormPage`, `VehiclesView`, `VehicleFormPage`, `PetsView`, `PetFormPage`, `ParkingSpotsView`, `ParkingSpotCreateView`, `UnitsView`, `UnitsListView`, `EditUnitPage` e `UnitModal` passaram cobrindo requisicoes/payloads sem `condominium_id` manual.
  - Validacao prevista: substituir fluxos priorizados pelo `api`/`apiRequest` central e testes de payload sem tenant manual onde o backend ja infere.

- [x] FE-SEC-003 - `auth.js` e `api.js` ainda imprimem detalhes de autenticação/refresh no console.
  - Evidencia: `console.log`, `console.warn` e `console.error` em fluxos de token.
  - Impacto: ruido em producao e possivel exposicao de estado sensivel em consoles compartilhados.
  - Correcao: logs de autenticacao, refresh, logout e tratamento API foram removidos dos utilitarios centrais.
  - Validacao: `rg -n "console\\.(log|warn|error)" resources/js/services/auth.js resources/js/utils/api.js` nao retornou resultados; `npm run build` passou.

- [x] FE-SEC-005 - Login e area autenticada nao ofereciam gerenciamento de passkeys.
  - Evidencia: `LoginView` nao tinha opcao de passkey e o menu lateral nao possuia area Segurança.
  - Impacto: usuarios nao conseguiam criar/excluir credenciais do dispositivo e administradores nao tinham visao de logs/passkeys.
  - Correcao: adicionado botao “Entrar com passkey”, utilitario WebAuthn, telas “Minhas Passkeys”, “Logs de Acesso” e “Passkeys dos Usuários” no menu Segurança.
  - Validacao: `LoginView.spec.js`, `MyPasskeysView.spec.js` e `SecurityUserPasskeysView.spec.js` passaram; browser confirmou botao de login e as tres telas novas.

- [ ] FE-SEC-004 - Audit completo de devDependencies ainda acusa vulnerabilidades em ferramentas de teste/dev.
  - Evidencia: `npm audit` lista vulnerabilidades em `vite`/`esbuild` e `jest`/`babel-jest`/`js-yaml`.
  - Impacto: risco principalmente de ambiente de desenvolvimento; producao ficou limpa com `npm audit --omit=dev`.
  - Progresso: `vitest` e `@vitest/ui` foram removidos, eliminando as vulnerabilidades criticas; `npm audit fix --package-lock-only` nao resolveu o restante sem atualizacoes com breaking changes.
  - Validacao prevista: avaliar upgrades maiores de Vite/Jest sem quebrar Laravel Vite, Vue Jest e CI.

## 2. Falhas de testes e qualidade frontend

- [x] FE-TEST-001 - `npm test` falha porque Jest coleta specs escritos para Vitest e specs quebrados.
  - Evidencia: erro `require is not defined`, `fileUrl is not defined`, componentes nao importados e `await` fora de funcao async.
  - Impacto: CI frontend nao serve como rede de seguranca.
  - Correcao: Jest passou a coletar apenas `resources/tests/**/*.spec.js`; foi criado setup compartilhado para mocks legados; specs de login, financeiro, manutencao, orcamento e encomendas foram ajustados.
  - Validacao: `npm test -- --runInBand` passou com 33 suites e 54 testes.

- [x] FE-TEST-002 - `npm run test:vitest:run` tambem falha por specs incompletos e mocks globais incorretos.
  - Evidencia: `fileUrl is not defined`, `localStorage.getItem is not a function`, imports inexistentes.
  - Impacto: suite paralela nao confiavel.
  - Correcao: Vitest foi removido do `package.json` e do lockfile; o padrao frontend ficou somente em Jest, como definido nas instrucoes do projeto.
  - Validacao: `npm test -- --runInBand` passou; `npm audit` nao lista mais `vitest`/`@vitest/ui`.

- [ ] FE-TEST-003 - Existem specs em `resources/tests` que nao importam o componente testado ou leem arquivo via global indefinido.
  - Evidencia: `CommonAreaBookingForm.spec.js`, `UnitsView.spec.js`, `CondominiumSelectView.spec.js`, entre outros.
  - Impacto: testes nao verificam comportamento real.
  - Progresso: setup Jest legado passou a fornecer `fs`, `fileUrl`, mocks de router e componentes globais para estabilizar a suite sem editar dezenas de arquivos repetidos.
  - Progresso adicional: specs estaticos de areas comuns, reservas, pets e veiculos foram substituidos ou complementados por testes de comportamento com montagem Vue e mock do cliente API central.
  - Validacao prevista: substituir gradualmente specs estaticos por testes de comportamento.

## 3. Falhas de cadastro, edicao, consulta e delecao por modulo

- [x] FE-CRUD-001 - Formulario/listagem de encomendas ainda depende de `selectedCondominium` no cliente mesmo apos backend inferir tenant.
  - Evidencia: `PackageFormPage.vue`, `PackagesView.vue` e `PackageDeliveryPage.vue`.
  - Impacto: usuario pode ficar bloqueado por estado local ausente/desatualizado apesar do backend conhecer o tenant.
  - Correcao: formulario, listagem e retirada de encomendas passaram a consultar endpoints tenant-aware sem exigir `selectedCondominium` local.
  - Validacao: busca estatica nao encontrou `testApiConnection`, `Testar API`, `/api/packages`, `/api/apartments?condominium_id` nem query manual de `condominium_id` no fluxo de encomendas; `npm run build` passou.

- [ ] FE-CRUD-002 - Varios modulos usam `fetch` manual com headers montados localmente em vez do cliente API central.
  - Evidencia: unidades, moradores, areas comuns, reservas, pets, veiculos e relatórios.
  - Impacto: refresh de token, erros 401 e headers divergem por tela.
  - Progresso: areas comuns, modal de reserva, listagem/formulario/detalhe/calendario de reservas, pets, veiculos, vagas de garagem, relatorios operacionais e listagem/edicao/modal de unidades usam `apiRequest` nos fluxos corrigidos; `apiRequest` agora suporta respostas 204/vazias e `FormData` sem forcar `Content-Type: application/json`.
  - Validacao parcial: `npm test -- --runInBand resources/tests/ReportsView.spec.js`, `npm test -- --runInBand` e `npm run build` passaram depois dessas migracoes.
  - Progresso adicional: dashboard financeiro, relatorios financeiros, solicitacoes de despesa, orcamento anual, cotacoes e formulario publico de fornecedor usam o cliente API central; os fluxos nao enviam `condominium_id` manual.
  - Validacao executada: `FinancialReportsView.spec.js`, `npm test -- --runInBand` (48 suites/95 testes) e `npm run build` passaram.

- [ ] FE-CRUD-003 - Comentarios `TODO` indicam falta de feedback de erro/sucesso em avisos, manutencao e classificados.
  - Evidencia: `NoticeListPage.vue`, `MaintenanceAreaFormPage.vue`, `ClassifiedsIndexPage.vue`.
  - Impacto: usuario nao tem confirmacao consistente em acoes de CRUD.
  - Validacao prevista: testes de mensagens/estado apos sucesso e erro.

## 4. Falhas de integracao frontend/backend

- [ ] FE-INT-001 - Frontend ainda envia/filtra por `condominium_id` em varios endpoints que deveriam usar tenant server-side.
  - Evidencia: `useCondominiumFilter.js`, `PackagesView.vue`, areas comuns, veiculos, pets, unidades.
  - Impacto: divergencia com o endurecimento multi-tenant do backend.
  - Progresso: areas comuns, reservas, pets, veiculos, vagas de garagem e listagem/edicao/modal de unidades nao enviam mais `condominium_id` nos fluxos corrigidos; os formularios de area comum, pet, veiculo e vaga removem tenant do payload de criacao.
  - Validacao parcial: busca estatica nos arquivos corrigidos nao encontrou `localStorage.getItem('token')`, `localStorage.getItem('selectedCondominium')`, `fetch(` nem `condominium_id` produtivo; ocorrencias restantes nesse recorte sao apenas assercoes de teste.
  - Validacao prevista: payloads e queries sem `condominium_id` nos modulos corrigidos.

- [x] FE-INT-002 - `PackageFormPage` manteve funcao de teste de conectividade que chama `/api/packages` e mostra mensagem ao usuario.
  - Evidencia: `testApiConnection` no componente.
  - Impacto: comportamento de debug em tela produtiva.
  - Correcao: botao e funcao de teste foram removidos.
  - Validacao: `rg "testApiConnection|Testar API"` nao retornou resultados; `npm run build` passou.

- [x] FE-INT-003 - Alguns testes/imports referenciam arquivos inexistentes ou renomeados.
  - Evidencia: `TransactionsView.spec.js` importa `../js/finance/TransactionsView.vue`, mas o arquivo atual e `FinancialTransactionsView.vue`.
  - Impacto: suite falha antes de validar comportamento.
  - Correcao: spec financeiro passou a importar `FinancialTransactionsView.vue` e a montar com Pinia/stubs corretos.
  - Validacao: `npm test -- --runInBand resources/tests/TransactionsView.spec.js` passou; suite Jest completa passou.

- [x] FE-INT-004 - Moradores chamava rota web e o badge de correspondencias era contador simulado.
  - Evidencia: `ResidentsView` fazia `fetch('/residents?...')`, recebia HTML da SPA e tentava parsear como JSON; `useNotifications` iniciava contadores mockados e incrementava `packages` por timeout.
  - Impacto: tela de moradores exibia `SyntaxError`/erro de conexao e o menu mostrava numero de correspondencias crescente sem relacao com dados reais.
  - Correcao: `ResidentsView` passou a usar `apiRequest('/residents')` sem tenant manual, notificacoes carregam pendencias reais de `/packages?status=pending`, simulacao foi removida e o seed de encomendas passou a criar dados tambem para o Condominio Teste sem duplicar condominios ja populados.
  - Validacao: browser confirmou Moradores com 9 registros, Correspondencias com 25 registros e badge estavel em 25; `npm test -- --runInBand`, `php artisan test --display-warnings` e `npm run build` passaram.

- [x] FE-INT-005 - Ocorrencias usava layout antigo, filtro quebrado e detalhe temporario.
  - Evidencia: `OccurrenceList` tinha tabela clara fora do padrao visual, typo `vmodel` no filtro de prioridade, chamada antiga via axios, formulario claro antigo e `OccurrenceView` exibia apenas conteudo temporario.
  - Impacto: modulo parecia desconectado das demais telas, filtros nao funcionavam corretamente e o fluxo `Ver` caia em uma tela sem dados reais; a API de detalhe ainda quebrava por policy chamando metodos inexistentes no `User`.
  - Correcao: listagem, detalhe e formulario de cadastro/edicao foram refeitos no padrao escuro com `TopCard`, cards, metricas, filtros, imagens, comentarios e estados consistentes; listagem/detalhe usam `apiRequest`; `NeonButton` passou a emitir clique explicitamente; `User` ganhou `isSuperAdmin()` e `hasPermissionTo()` compatíveis com os perfis/modulos atuais.
  - Validacao: `npm test -- --runInBand resources/tests/OccurrenceList.spec.js resources/tests/OccurrenceView.spec.js resources/tests/OccurrenceForm.spec.js` passou; `php artisan test --filter=OccurrenceControllerTest --display-warnings` passou; `npm run build` passou; browser confirmou listagem com 5 cards, filtro/limpar funcionando, detalhe sem 500/temporario, imagens, botao Voltar e formulario de nova ocorrencia funcionando.

- [x] FE-INT-006 - Vagas de garagem nao exibiam dados no Condominio Teste e criacao em lote falhava sem `condominium_id`.
  - Evidencia: tela `/parking-spots` mostrava 0 vagas para `teste@teste.com`; `ParkingSpotController@store` e `batchStore` ainda exigiam `condominium_id`, enquanto o frontend ja envia payload tenant-aware sem esse campo.
  - Impacto: usuario nao conseguia popular vagas em lote e a listagem ficava vazia para o condominio atual.
  - Correcao: `ParkingSpotController` passou a inferir o condominio atual no cadastro avulso e em lote, validando unidades dentro desse condominio e evitando duplicidade por identificador.
  - Validacao: `php artisan test --filter=ParkingSpotControllerTest --display-warnings` passou; `npm test -- --runInBand resources/tests/ParkingSpotsView.spec.js resources/tests/ParkingSpotCreateView.spec.js` passou; browser confirmou criacao em lote pela tela e listagem com 3 vagas (`101-V1`, `102-V1`, `201-V1`).

- [x] FE-INT-007 - Relatorios gerais chamavam rota inexistente, tinham layout defasado e botoes de exportacao falsos.
  - Evidencia: `ReportsView.vue` usava `POST /api/reports/generate`, seletor manual de condominio e funcoes de PDF/Excel que apenas registravam `console.log`.
  - Impacto: usuario nao recebia relatorio real, exportacao nao entregava arquivo e a tela destoava do padrao visual atual.
  - Correcao: criada API tenant-aware `GET /api/reports/operational` e `GET /api/reports/operational/export` para visao geral, moradores, encomendas, ocorrencias, vagas, reservas, manutencao e acesso; `ReportsView` foi refeito com `AuthenticatedLayout`, `TopCard`, `NeonButton`, filtros, KPIs, tabela dinamica, estados de carregamento/erro/vazio e CSV real.
  - Validacao: `php artisan test --filter=OperationalReportControllerTest --display-warnings`, `npm test -- --runInBand ReportsView.spec.js` e `npm run build` passaram; browser confirmou `/reports` com `teste@teste.com`, troca para Encomendas com 25 linhas e endpoint CSV retornando `text/csv`.

## 5. Falhas de regras de negocio e gaps importantes

- [x] FE-BUS-001 - Login exibe credenciais de teste na tela.
  - Evidencia: template mostra `Para teste: teste@teste.com / teste`.
  - Impacto: informacao inadequada para producao e possivel confusao operacional.
  - Correcao: texto de credenciais de teste foi removido da tela.
  - Validacao: busca estatica por `Para teste` nao retornou resultados; `npm test -- --runInBand resources/tests/LoginView.spec.js` passou.

- [x] FE-BUS-002 - Retirada de encomenda deve refletir erro de segunda retirada vindo do backend.
  - Evidencia: backend agora retorna 422 `Encomenda já retirada`; validar frontend.
  - Impacto: usuario precisa entender estado bloqueado sem sobrescrever historico.
  - Correcao: `PackageDeliveryPage` exibe a mensagem de erro retornada pela API, preserva a encomenda selecionada na lista e define handlers de assinatura referenciados no template.
  - Validacao: `npm test -- --runInBand resources/tests/PackageDeliveryPage.spec.js` passou cobrindo erro `Encomenda já retirada`.

## Validacoes executadas

- [x] Tentativa 1 de `npm test -- --runInBand` em 2026-06-25.
  - Resultado: falha estrutural ja registrada em `AUDITORIA_TODO.md`.

- [x] Tentativa 1 de `npm run test:vitest:run` em 2026-06-25.
  - Resultado: falha estrutural ja registrada em `AUDITORIA_TODO.md`.

- [x] `npm run build`.
  - Resultado: passou antes desta auditoria frontend; deve ser repetido apos correcoes.

- [x] `npm audit --omit=dev`.
  - Resultado: passou com `found 0 vulnerabilities`; audit completo ainda tem vulnerabilidades dev.

- [x] `npm test -- --runInBand resources/tests/LoginView.spec.js` apos correcoes de login.
  - Resultado: passou com 3 testes e validou que senha nao e salva em `localStorage`.

- [x] Busca estatica de logs centrais de auth/API.
  - Comando: `rg -n "console\\.(log|warn|error)" resources/js/services/auth.js resources/js/utils/api.js`.
  - Resultado: sem ocorrencias.

- [x] Busca estatica do fluxo de encomendas.
  - Comando: `rg -n "testApiConnection|Testar API|condominium_id=.*packages|/api/packages|/api/apartments\\?condominium_id|selectedCondominium.*packages|rememberedPassword|Para teste" ...`.
  - Resultado: sem ocorrencias produtivas das falhas corrigidas; apenas remocao/validacao legada de `rememberedPassword` permanece.

- [x] `npm run build` apos correcoes frontend.
  - Resultado: passou; persistem apenas avisos de Browserslist desatualizado e imports estaticos/dinamicos duplicados do Vite.

- [x] `npm audit` e `npm audit fix --package-lock-only`.
  - Resultado: ainda falha com 23 vulnerabilidades em dependencias de desenvolvimento; nao foi aplicado `--force` porque ele propõe mudancas breaking (`vite@8.1.0` ou downgrade de `jest`).

- [x] Tentativa 2 de `npm test -- --runInBand` em 2026-06-25.
  - Resultado: 32 suites falham e 1 suite passa. Falhas principais: specs estaticos usando `fs`/`fileUrl` globais inexistentes, componentes nao importados, import obsoleto de `TransactionsView.vue`, uso de `vitest` dentro da suite Jest, `await` fora de teste async em `TopCard`, mocks de router ausentes e assercoes antigas em manutencao.

- [x] `npm uninstall --save-dev vitest @vitest/ui`.
  - Resultado: trilha Vitest removida; audit completo caiu para 21 vulnerabilidades dev sem criticas.

- [x] `npm test -- --runInBand` final em 2026-06-25.
  - Resultado: passou com 33 suites e 54 testes.

- [x] `npm run build` final em 2026-06-25.
  - Resultado: passou; persistem avisos de Browserslist desatualizado e imports estaticos/dinamicos duplicados do Vite.

- [x] `npm audit --omit=dev` final em 2026-06-25.
  - Resultado: passou com `found 0 vulnerabilities`.

- [x] `npm audit` final em 2026-06-25.
  - Resultado: falha apenas em dependencias de desenvolvimento com 21 vulnerabilidades, sem criticas; correcoes sugeridas exigem breaking changes em Vite/Jest.

- [x] `git diff --check` final em 2026-06-25.
  - Resultado: passou sem problemas de whitespace.

- [x] Testes direcionados de areas comuns e reservas apos migracao para `apiRequest`.
  - Comando: `npm test -- --runInBand resources/tests/CommonAreasView.spec.js resources/tests/NewBookingModal.spec.js resources/tests/BookingsPage.spec.js resources/tests/BookingFormPage.spec.js`.
  - Resultado: passou com 4 suites e 7 testes.

- [x] Testes direcionados complementares de areas comuns e reservas apos migracao para `apiRequest`.
  - Comando: `npm test -- --runInBand resources/tests/CommonAreaCalendar.spec.js resources/tests/CommonAreaBookingForm.spec.js resources/tests/AreaBookingsPage.spec.js resources/tests/BookingDetailPage.spec.js resources/tests/CommonAreaFormPage.spec.js`.
  - Resultado: passou com 5 suites e 10 testes.

- [x] Testes direcionados de pets e veiculos apos migracao para `apiRequest`.
  - Comando: `npm test -- --runInBand resources/tests/VehiclesView.spec.js resources/tests/PetsView.spec.js resources/tests/VehicleFormPage.spec.js resources/tests/PetFormPage.spec.js`.
  - Resultado: passou com 4 suites e 8 testes.

- [x] Testes direcionados de vagas de garagem apos migracao para `apiRequest`.
  - Comando: `npm test -- --runInBand resources/tests/ParkingSpotsView.spec.js resources/tests/ParkingSpotCreateView.spec.js`.
  - Resultado: passou com 2 suites e 5 testes.

- [x] Testes direcionados de edicao/modal de unidades apos migracao para `apiRequest`.
  - Comando: `npm test -- --runInBand resources/tests/UnitModal.spec.js resources/tests/EditUnitPage.spec.js`.
  - Resultado: passou com 2 suites e 3 testes.

- [x] Testes direcionados de listagens de unidades apos migracao para `apiRequest`.
  - Comando: `npm test -- --runInBand resources/tests/UnitsView.spec.js resources/tests/UnitsListView.spec.js`.
  - Resultado: passou com 2 suites e 2 testes.

- [x] Suite Jest completa apos correcoes de unidades/listagens.
  - Comando: `npm test -- --runInBand`.
  - Resultado: passou com 43 suites e 79 testes.

- [x] Build Vite apos correcoes de unidades/listagens.
  - Comando: `npm run build`.
  - Resultado: passou; persistem apenas avisos conhecidos de Browserslist desatualizado e imports estaticos/dinamicos duplicados.
