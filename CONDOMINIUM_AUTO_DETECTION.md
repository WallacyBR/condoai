# Resolução de Tenant (Multi-tenant)

## Fonte da verdade

O tenant ativo é definido pelo header HTTP **`X-Condominium-Id`**, validado pelo `TenantMiddleware` com verificação de membership na pivot `condominium_user`.

- Usuário autenticado precisa pertencer ao condomínio informado (membership N:N) **ou** ter perfil **`SysOp`** (administrador de plataforma).
- Sem header, header inválido ou condomínio inexistente → resposta **4xx** (fail-closed).
- Cross-tenant não autorizado → **403**.
- Não há fallback silencioso para `Condominium::first()`, `users.condominium_id` (home) nem resolução por subdomínio.
- Parâmetros `?condominium_id=` **não** filtram tenant — apenas o header (via `TenantContext`) define isolamento.

## Membership

- Relação N:N: tabela pivot `condominium_user`.
- `users.condominium_id` permanece como condomínio **padrão/home** do usuário, não como gate de tenant.
- Backfill da pivot é feito a partir de `users.condominium_id` existente.

## Contexto de tenant no backend

- `TenantMiddleware` resolve e valida o tenant, registrando em `TenantContext`.
- Helper `tenant()` retorna o `Condominium` ativo ou `null`.
- `CondominiumScope` aplica filtro fail-closed: sem contexto de tenant, nenhum registro é retornado.
- Controllers usam `ResolvesTenantCondominium::tenantCondominiumId()` para writes; `condominium_id` no body é ignorado e substituído pelo tenant ativo.

## Models com CondominiumScope (M1 + M3)

Escopo direto (`condominium_id`): Resident, Tower, Block, Apartment, CommonArea, CommonAreaBooking, Package, Visitor, Ad, Occurrence, Bank, ChartAccount, Payable, Receivable, CashFlow, Transaction, Notice, AccessRequest, Pet, ProtectiveMeasure, VisitorVehicle, MaintenanceSchedule, MaintenanceArea, MaintenanceEquipment, MaintenanceRecord, etc.

Escopo via relação: **Vehicle** (`apartment`).

## Endpoints relevantes

| Endpoint | Header obrigatório | Comportamento |
|----------|-------------------|---------------|
| Rotas com middleware `tenant` | Sim (`X-Condominium-Id`) | Valida membership ou bypass SysOp |
| `GET /api/auth/me` | Não | Retorna `memberships[]` com condomínios do usuário |
| `GET /api/condominiums` | Sim | SysOp vê todos; demais usuários veem apenas memberships |

## Desenvolvimento local

Em local/staging/produção, envie sempre o header explícito:

```http
X-Condominium-Id: 1
Authorization: Bearer <token>
```

Consulte `GET /api/auth/me` para obter a lista de `memberships` disponíveis ao usuário autenticado.

## Frontend (M2)

O interceptor em `resources/js/utils/api.js` e o store `resources/js/stores/tenant.js` injetam `X-Condominium-Id` automaticamente. A função `buildUrlWithCondominiumFilter` no composable `useCondominiumFilter.js` é identidade (não adiciona query params) — o tenant vem exclusivamente do header.
