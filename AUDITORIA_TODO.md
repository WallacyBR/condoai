# Auditoria tecnica e lista de correcao - CondoAI

Atualizado em: 2026-06-26

## Regras de execucao

- Marcar cada item como `[x]` somente depois de corrigir e validar.
- Registrar validacao executada logo abaixo do item corrigido.
- Se uma validacao travar ou falhar por oito tentativas consecutivas, desistir daquele teste especifico e documentar a razao.
- Manter novos comentarios de codigo e mensagens de commit em portugues detalhado.

## Status geral

- [x] Auditoria inicial completa do projeto.
- [ ] Falhas de seguranca e dividas tecnicas corrigidas.
- [ ] Falhas de cadastro, edicao, consulta e delecao por modulo corrigidas.
- [ ] Falhas de integracao entre modulos corrigidas.
- [ ] Gaps de regras de negocio corrigidos.
- [x] Documentacao Scribe regenerada quando houver alteracao de rotas.

## 1. Falhas de seguranca e dividas tecnicas

- [x] SEC-001 - Rota publica temporaria `POST /api/test-upload` aceita payload sem autenticacao e ecoa dados enviados.
  - Evidencia: `routes/api.php` declara a rota como teste temporario fora do grupo `auth:api`.
  - Impacto: exposicao desnecessaria de endpoint publico e comportamento de debug em producao.
  - Validacao executada: `php -l routes/api.php` passou; `rg -n "test-upload|TESTE TEMPORÁRIO|Testando rota sem autenticação" routes app resources/js` nao retornou ocorrencias.

- [x] SEC-002 - `imageUploadService` executa chamada temporaria sem autenticacao e registra informacoes excessivas no console.
  - Evidencia: `resources/js/services/imageUpload.js` chama `/test-upload` antes do upload real.
  - Impacto: ruido operacional, possivel vazamento de metadados e dependencia de rota insegura.
  - Validacao executada: busca estatica confirmou ausencia da chamada para `/test-upload`; `npm test` completo segue bloqueado sem `node_modules`.

- [x] SEC-003 - `AuthController` deixa `logout` e `refresh` como excecoes do middleware `auth:api`.
  - Evidencia: construtor usa `except => ['login', 'register', 'logout', 'refresh']`.
  - Impacto: endpoints de sessao podem executar sem usuario autenticado e gerar erro ou comportamento indefinido.
  - Validacao executada: `php -l app/Http/Controllers/API/AuthController.php` passou; busca estatica confirmou remocao da excecao antiga. Teste HTTP completo segue bloqueado sem `vendor`.

- [x] SEC-004 - `TenantMiddleware` usa o primeiro condominio como fallback fora do contexto local e mantem logs de debug por requisicao.
  - Evidencia: middleware usa `Condominium::first()` quando nao resolve host/subdominio.
  - Impacto: risco de isolamento multi-tenant quebrado e logs excessivos com contexto operacional.
  - Validacao executada: `php -l app/Http/Middleware/TenantMiddleware.php` passou; revisao estatica confirmou que o fallback silencioso foi removido e que ausencia de tenant lança 404.

- [ ] SEC-005 - Varios controllers aceitam `condominium_id` informado pelo cliente para consultar/criar/editar dados tenant.
  - Evidencia: `ResidentController`, `ApartmentController`, `BankController`, `ChartAccountController`, `CommonAreaController`, `ExpenseRequestController`, entre outros.
  - Impacto: risco de acesso ou escrita cross-tenant quando o request manipula `condominium_id`.
  - Validacao parcial executada: `PackageControllerTest` cobre encomendas cross-tenant e passou; ainda falta aplicar o mesmo padrao aos demais controllers listados.

- [x] SEC-008 - Route model binding executava antes do tenant e podia resolver modelos sem escopo de condominio.
  - Evidencia: `SubstituteBindings` esta no grupo `api`; teste de encomenda cross-tenant inicialmente retornou 200.
  - Impacto: escopos globais tenant nao protegiam implicit binding em rotas como `/api/packages/{package}`.
  - Validacao executada: prioridade de middleware ajustada em `app/Http/Kernel.php`; `PackageControllerTest::test_cannot_show_package_from_another_condominium` passou com 404.

- [x] SEC-009 - Autoload PSR-4 ignorava controllers em pasta `API` com namespace `Api`.
  - Evidencia: `composer install` alertou que `NoticeController`, `OccurrenceController` e `DocumentController` nao cumpriam PSR-4.
  - Impacto: rotas poderiam quebrar em ambiente case-sensitive ou apos dump autoload otimizado.
  - Validacao executada: namespaces e imports corrigidos; `composer dump-autoload -o` passou sem warnings PSR-4.

- [x] SEC-010 - Dependencias de producao NPM tinham vulnerabilidades transitivas.
  - Evidencia: `npm audit --omit=dev` encontrou vulnerabilidades em `brace-expansion`, `glob`, `minimatch`, `picomatch`, `postcss` e `yaml`.
  - Impacto: risco em cadeia de dependencias se pacotes vulneraveis forem usados em runtime/build de producao.
  - Validacao executada: `npm audit fix` aplicado; `npm audit --omit=dev` retornou `found 0 vulnerabilities`.

- [x] SEC-011 - Sistema nao possuia gestao de passkeys, revogacao de credenciais nem logs estruturados de acesso.
  - Evidencia: login era apenas senha/JWT e nao havia tabelas `user_passkeys`, `access_logs` ou `webauthn_challenges`.
  - Impacto: menor transparencia de auditoria e ausencia de autenticacao forte por dispositivo.
  - Correcao: criado modulo Segurança com WebAuthn/passkeys opcionais, logs de acesso, revogacao tenant-aware e login por passkey mantendo JWT.
  - Validacao executada: `SecurityPasskeyTest` passou com 6 testes e 34 assertions; browser validou menu e telas `/security/my-passkeys`, `/security/access-logs` e `/security/user-passkeys`; smoke API validou login senha, listagem e opcoes de registro de passkey.

- [ ] SEC-006 - Uploads base64 validam apenas prefixo textual e extensao inferida pelo payload.
  - Evidencia: `ImageUploadController`, `PackageController` e `CommonAreaController` processam base64 diretamente.
  - Impacto: risco de arquivo invalido, extensao manipulada e consumo de memoria.
  - Validacao prevista: testes com MIME invalido, payload grande e imagem valida.

- [ ] SEC-007 - Politicas existem para financeiro/ocorrencias, mas grande parte dos controllers nao chama `authorize`.
  - Evidencia: policies cadastradas em `AuthServiceProvider`, porem controllers financeiros fazem CRUD direto.
  - Impacto: usuarios autenticados podem executar acoes alem do perfil.
  - Validacao prevista: testes por perfil/permissao.

## 2. Falhas de cadastro, edicao, consulta e delecao por modulo

- [x] CRUD-001 - `ChartAccountController` usa tipo inexistente `ChatAccount` em `show` e `destroy`.
  - Evidencia: assinatura dos metodos referencia `ChatAccount`, classe que nao existe.
  - Impacto: consulta e delecao de plano de contas por ID quebram.
  - Validacao executada: `php -l app/Http/Controllers/API/ChartAccountController.php` passou; `rg -n "ChatAccount" routes app resources/js` nao retornou ocorrencias.

- [x] CRUD-002 - Modulo financeiro usa `$request->all()` em contas a pagar/receber.
  - Evidencia: `PayableController` e `ReceivableController`.
  - Impacto: risco de mass assignment de campos inesperados e validacao fraca de integridade.
  - Correcao: contas a pagar/receber agora aceitam apenas campos validados, inferem o condominio pelo tenant e confirmam conta contabil/banco do mesmo condominio.
  - Validacao executada: `FinanceOperationalTest::test_creates_and_partially_settles_tenant_payable` passou; suite PHP completa passou com 18 testes e 208 assertions.

- [x] CRUD-003 - `TransactionController` nao implementa `show`, `update` e `destroy`, apesar de estar registrado como `apiResource`.
  - Evidencia: controller contem apenas `index` e `store`.
  - Impacto: rotas geradas para edicao/consulta/delecao podem falhar.
  - Validacao executada: resource limitado a `index` e `store`; `php artisan route:list --path=api/finance/transactions` mostra apenas GET e POST; Scribe nao pulou mais rotas por metodo inexistente.

- [ ] CRUD-004 - `CommonAreaController` consulta reserva por ID sem escopo tenant em `showBooking`.
  - Evidencia: `CommonAreaBooking::find($id)`.
  - Impacto: uma reserva de outro condominio pode ser consultada se o ID for conhecido.
  - Validacao prevista: teste cross-tenant de reserva.

- [x] CRUD-005 - `PackageController` consulta/edita encomendas por ID sem escopo tenant global no model.
  - Evidencia: `Package` nao possui `CondominiumScope`; `show` usa `findOrFail`.
  - Impacto: consulta e alteracao cross-tenant de encomendas.
  - Validacao executada: `Package` recebeu `CondominiumScope`, `show` usa route model binding e update valida apartamento do tenant atual; `PackageControllerTest` cobre consulta e update cross-tenant.

- [x] CRUD-008 - Cadastro de encomendas atual nao preenchia colunas obrigatorias legadas da tabela `packages`.
  - Evidencia: migration original exige `recipient_name`, `recipient_apartment` e `sender`, mas o formulario atual envia apenas unidade, local de retirada e observacao.
  - Impacto: cadastro poderia falhar por campos obrigatorios ausentes.
  - Validacao executada: controller preenche defaults derivados da unidade; `PackageControllerTest::test_can_create_package_with_current_form_fields` passou.

- [x] CRUD-009 - Ambiente de teste backend dependia de MySQL local e falhava antes de validar funcionalidades.
  - Evidencia: `php artisan test` falhou com acesso negado ao usuario `forge` em `condoai_testing`.
  - Impacto: CI/local nao conseguiam validar regressao sem banco externo configurado.
  - Validacao executada: `phpunit.xml` usa SQLite em memoria; migration de apartamentos ganhou compatibilidade SQLite; `php artisan test --display-warnings` passou.

- [ ] CRUD-006 - `ApartmentController::removeResident` valida `resident_id` sem garantir mesmo condominio do apartamento.
  - Evidencia: `Rule::exists('residents', 'id')` sem filtro tenant.
  - Impacto: operacao pode tentar remover vinculo com morador de outro condominio.
  - Validacao prevista: teste de remocao cross-tenant.

- [ ] CRUD-007 - `CondominiumController` permite listar, mostrar, editar e excluir todos os condominios para qualquer usuario autenticado.
  - Evidencia: `index()` retorna `Condominium::all()` e demais metodos nao autorizam perfil.
  - Impacto: alto risco administrativo e de privacidade.
  - Validacao prevista: testes de permissao administrativa.

## 3. Falhas de integracao entre modulos

- [ ] INT-001 - Financeiro permite `chart_account_id`, `bank_id` e `condominium_id` sem validar que todos pertencem ao mesmo condominio.
  - Evidencia: validacoes usam apenas `exists`.
  - Impacto: contas, bancos, transacoes e fluxo de caixa podem cruzar dados entre condominios.
  - Progresso: títulos, orçamento, solicitações, liquidações, dashboard, relatórios, cotações e a listagem/criação de lançamentos passaram a inferir o tenant; contas e bancos são validados no mesmo condomínio. O CRUD legado direto de fluxo de caixa ainda precisa ser aposentado/migrado integralmente.
  - Validacao parcial executada: `FinanceOperationalTest` cobre criacao/liquidacao e rotas tenant-aware; suite PHP completa passou.

- [ ] INT-002 - Encomendas dependem de `Apartment`, mas update de `apartment_id` nao atualiza/valida `condominium_id` de forma consistente.
  - Evidencia: `PackageController::update` valida apartamento por existencia simples.
  - Impacto: encomenda pode ficar vinculada a apartamento de outro condominio.
  - Validacao executada: corrigido junto com `CRUD-005`; `PackageControllerTest::test_cannot_move_package_to_apartment_from_another_condominium` passou.

- [ ] INT-003 - Reservas de areas comuns dependem de area, apartamento e morador, mas os endpoints ainda exigem/aceitam `condominium_id` do cliente.
  - Evidencia: `CommonAreaController::allBookings` exige query `condominium_id`.
  - Impacto: frontend precisa informar tenant manualmente e pode consultar tenant errado.
  - Validacao prevista: endpoint deve inferir tenant atual.

- [ ] INT-004 - `Vehicle` nao possui `condominium_id` nem escopo tenant direto, dependendo de `Apartment`/`ParkingSpot`.
  - Evidencia: model `Vehicle` lista apenas `apartment_id` e `parking_spot_id`.
  - Impacto: filtros e route model binding precisam sempre atravessar relacionamento para manter isolamento.
  - Validacao prevista: revisar controller e testar acesso cross-tenant.

- [x] INT-005 - Menu lateral escondia modulos do usuario de teste por permissoes vazias e condominio nao sincronizado no login local.
  - Evidencia: `teste@teste.com` tinha perfil `SysOp`, mas `profile.modules` estava vazio porque `DatabaseSeeder` rodava perfis antes dos modulos; alem disso, o login local nao retornava `current_condominium`.
  - Impacto: Moradores, Correspondencias, Ocorrencias e demais itens condominiais nao apareciam no menu lateral.
  - Validacao executada: ordem do `DatabaseSeeder` corrigida e permissoes reaplicadas; login API retorna `current_condominium`, `Moradores`, `Correspondências` e `Ocorrências`; browser validou menu visivel com esses itens; `php artisan test --display-warnings`, `npm test -- --runInBand` e `npm run build` passaram.

## 4. Falhas de regras de negocio e gaps importantes

- [ ] BUS-001 - Cadastro publico de usuario (`auth/register`) nao define perfil padrao nem exige convite/aprovacao.
  - Evidencia: cria usuario apenas com nome, email e senha.
  - Impacto: usuarios sem perfil podem entrar no sistema ou ficar em estado inconsistente.
  - Validacao prevista: teste de registro deve aplicar regra definida.

- [x] BUS-002 - Encomendas podem ser retiradas multiplas vezes com o mesmo codigo.
  - Evidencia: `PackageController::withdraw` nao verifica `retrieved_at` antes de atualizar.
  - Impacto: historico de retirada pode ser sobrescrito.
  - Validacao executada: segunda retirada retorna 422 com `Encomenda já retirada`; coberto por `PackageControllerTest::test_cannot_withdraw_package_twice`.

- [ ] BUS-003 - Reservas de areas comuns precisam validar conflitos, limites por dia/mes/ano e aprovacao.
  - Evidencia: parametros existem no model/controller, mas requerem verificacao completa no fluxo de reserva.
  - Impacto: overbooking e regras comerciais inconsistentes.
  - Validacao prevista: testes de conflito e limites.

- [x] BUS-004 - Aprovacao de solicitacao de despesa nao valida status atual nem permissao do aprovador.
  - Evidencia: `ExpenseRequestController::approve` atualiza diretamente para aprovado.
  - Impacto: reprocessamento, aprovacao indevida ou estados invalidos.
  - Correcao: workflow passou a ter rascunho, envio, aprovacao sequencial por regra de usuario/perfil, recusa justificada, bloqueio de autoaprovacao e conversao manual em despesa.
  - Validacao executada: `FinanceOperationalTest::test_expense_request_requires_other_configured_approver_then_converts_manually` passou.

- [x] FIN-001 - Financeiro operacional, relatorios gerenciais e cotacoes de fornecedores eram incompletos ou inexistentes.
  - Correcao: criadas liquidacoes parciais, previsao tenant-aware, DRE gerencial por competencia, Balancete Financeiro Gerencial em JSON/PDF, links publicos tokenizados para fornecedores, comparacao/encerramento de cotacoes e integracao manual cotacao-solicitacao-despesa.
  - Validacao executada: APIs locais retornaram Dashboard 200, DRE 200, Balancete PDF 200 `application/pdf` e Cotações 200; `FinanceOperationalTest` passou com 4 testes e 23 assertions; `php artisan scribe:generate` concluiu.

- [ ] BUS-005 - Webhooks financeiros estao dentro do grupo autenticado/tenant.
  - Evidencia: rotas `finance/webhooks/*` dentro de `auth:api, tenant`.
  - Impacto: provedores externos nao conseguem notificar sem token de usuario; assinatura de webhook tambem nao aparece validada.
  - Validacao prevista: mover para fluxo publico assinado ou documentar decisao.

## 5. Ajustes visuais de dados demonstrativos

- [x] IMG-001 - Registros criados por seed estavam sem imagens reais nos modulos que exibem fotos.
  - Evidencia: areas comuns, pets, moradores, visitantes, encomendas, classificados e ocorrencias tinham campos vazios ou caminhos placeholder sem arquivo publico correspondente.
  - Correcao: imagens demonstrativas geradas por IA foram salvas em `storage/app/public` e o `DemoImageSeeder` passou a preencher os registros existentes e futuros seeds.
  - Observacao: veiculos receberam asset em `storage/app/public/demo-images/vehicle.png`, mas o modulo atual nao possui coluna/campo de foto para vincular ao registro.
  - Validacao executada: `php -l database/seeders/DemoImageSeeder.php` passou; `php artisan db:seed --class=DemoImageSeeder` passou; contagem validou 18/18 areas comuns, 2/2 pets, 15/15 moradores, 60/60 visitantes, 50/50 encomendas, 24/24 classificados e 15/15 ocorrencias com imagem/foto; todos os arquivos em `/storage/...` responderam `HTTP/1.1 200 OK`.

## Validacoes executadas

- [x] Tentativa 1 de `php artisan test` em 2026-06-25.
  - Resultado: falhou antes da suite por ausencia de `vendor/autoload.php`.

- [x] Tentativa 1 de `npm test -- --runInBand` em 2026-06-25.
  - Resultado: falhou antes da suite por ausencia de `node_modules/.bin/cross-env`.

- [x] Tentativa 2 de `php artisan test` em 2026-06-25.
  - Resultado: falhou porque `tests/Unit` nao existia.

- [x] Tentativa 3 de `php artisan test` em 2026-06-25.
  - Resultado: falhou por dependencia de MySQL local (`forge` sem acesso).

- [x] Tentativa 4 de `php artisan test --display-warnings` em 2026-06-25.
  - Resultado: passou com 2 testes e 9 assertions, mas com warning por ausencia de `.env`.

- [x] Tentativa 5 de `php artisan test --display-warnings` em 2026-06-25.
  - Resultado: passou limpo com 2 testes e 9 assertions apos criar `.env` local ignorado pelo Git.

- [x] Tentativa 6 de `php artisan test --display-warnings` em 2026-06-25.
  - Resultado: passou limpo com 6 testes e 20 assertions apos adicionar cobertura de encomendas.

- [x] Tentativa 2 de `npm test -- --runInBand` em 2026-06-25.
  - Resultado: falhou por harness inconsistente: specs misturam Jest/Vitest, usam `fs`/`fileUrl` globais ausentes, imports inexistentes e `await` em teste nao async.

- [x] Tentativa 1 de `npm run test:vitest:run` em 2026-06-25.
  - Resultado: falhou por problemas estruturais semelhantes nos specs frontend (`fileUrl` indefinido, componentes nao importados, `localStorage.getItem` mockado incorretamente, imports inexistentes).

- [x] `composer install`.
  - Resultado: instalou dependencias PHP e revelou warnings PSR-4 corrigidos.

- [x] `composer dump-autoload -o`.
  - Resultado: passou sem warnings PSR-4 apos corrigir namespaces `API`.

- [x] `npm install`.
  - Resultado: instalou dependencias JS e atualizou `package-lock.json`; `npm ci` anterior falhou por lockfile fora de sincronia.

- [x] `npm run build`.
  - Resultado: passou; restaram apenas warnings de chunking dinamico/estatico e Browserslist desatualizado.

- [x] `npm audit --omit=dev`.
  - Resultado: passou com `found 0 vulnerabilities`.

- [x] `php artisan route:list --path=api`.
  - Resultado: passou apos importar `AccessControlDashboardController` e corrigir PSR-4.

- [x] `php artisan scribe:generate`.
  - Resultado: documentacao gerada em `resources/views/scribe`, `public/vendor/scribe`, `storage/app/scribe` e caches `.scribe`; segunda execucao nao pulou mais as rotas inexistentes de transacoes.
