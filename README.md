<p align="center">
  <img src="public/images/condo_ai_horizontal.png" alt="CondoAI" width="360">
</p>

<p align="center">
  <strong>Plataforma open source para a operacao completa de condominios.</strong>
</p>

<p align="center">
  <a href="https://laravel.com"><img src="https://img.shields.io/badge/Laravel-10-FF2D20?logo=laravel&logoColor=white" alt="Laravel 10"></a>
  <a href="https://vuejs.org"><img src="https://img.shields.io/badge/Vue.js-3-4FC08D?logo=vuedotjs&logoColor=white" alt="Vue 3"></a>
  <a href="https://www.php.net"><img src="https://img.shields.io/badge/PHP-%3E%3D8.1-777BB4?logo=php&logoColor=white" alt="PHP 8.1 ou superior"></a>
  <a href="#licenca"><img src="https://img.shields.io/badge/licenca-MIT-blue.svg" alt="Licenca MIT"></a>
</p>

## Sobre o CondoAI

O CondoAI e uma aplicacao web para centralizar a rotina administrativa e operacional de condominios. Ele reune cadastro de unidades e moradores, controle de acesso, portaria, encomendas, reservas de areas, manutencao, ocorrencias, financeiro e relatorios em uma experiencia unica, responsiva e instalavel como PWA.

O projeto foi construido para cenarios multi-condominio. Cada requisicao autenticada recebe um contexto de condominio no backend, evitando que dados operacionais de um condominio sejam exibidos em outro.

## Destaques

- Gestao de condominio, torres, blocos, unidades, moradores e perfis de acesso.
- Cadastro de veiculos, pets e vagas de garagem, incluindo criacao de vagas em lote e locacao.
- Portaria com visitantes, veiculos de visitantes, autorizacoes de acesso, entrada e saida.
- Registro, acompanhamento, comentarios e fotos para ocorrencias.
- Controle de encomendas, comprovante/foto e confirmacao de retirada.
- Areas comuns, agenda, disponibilidade e reservas.
- Manutencao preventiva e corretiva: areas, equipamentos, QR Code, cronogramas, registros, fotos e assinaturas.
- Financeiro: bancos, plano de contas, lancamentos, contas a pagar/receber, fluxo de caixa, orcamento e relatorios.
- Comunicados, documentos, classificados e medidas protetivas.
- Relatorios operacionais por periodo, com exportacao CSV, e relatorios financeiros dedicados.
- API REST autenticada por JWT e documentacao interativa gerada com Laravel Scribe.
- Interface Vue 3 com Pinia, Vue Router, Tailwind CSS e suporte a PWA/offline.

## Indice

- [Tecnologias](#tecnologias)
- [Modulos](#modulos)
- [Inicio rapido](#inicio-rapido)
- [Dados de demonstracao](#dados-de-demonstracao)
- [Uso local e multi-condominio](#uso-local-e-multi-condominio)
- [API e documentacao](#api-e-documentacao)
- [Testes e qualidade](#testes-e-qualidade)
- [Build de producao](#build-de-producao)
- [Estrutura do projeto](#estrutura-do-projeto)
- [Contribuicao](#contribuicao)
- [Seguranca](#seguranca)
- [Licenca](#licenca)

## Tecnologias

| Camada | Tecnologias |
| --- | --- |
| Backend | PHP 8.1+, Laravel 10, Eloquent ORM, JWT Auth |
| Frontend | Vue 3, Vite, Vue Router, Pinia, Tailwind CSS |
| Banco de dados | MySQL em desenvolvimento/producao; SQLite em memoria nos testes |
| Relatorios e documentos | Laravel Scribe, Dompdf, CSV |
| Qualidade | PHPUnit, Jest, Vue Test Utils, Laravel Pint |
| Experiencia web | PWA com Workbox, cache de rotas e suporte offline |

## Modulos

| Area | Capacidades principais |
| --- | --- |
| Estrutura | Condominios, torres, blocos, unidades e vinculo de moradores |
| Pessoas e acessos | Moradores, perfis, permissoes, usuarios, visitantes e autorizacoes de entrada/saida |
| Portaria | Encomendas, retirada, comprovantes, controle de veiculos de visitantes e medidas protetivas |
| Patrimonio | Vagas, veiculos, pets, areas comuns e reservas |
| Operacao | Ocorrencias, fotos, comentarios, comunicados, documentos e classificados |
| Manutencao | Areas, equipamentos, QR Code, cronogramas, tarefas, registros, fotos e assinaturas |
| Financeiro | Bancos, plano de contas, transacoes, contas a pagar/receber, conciliacao, fluxo de caixa e orcamento |
| Inteligencia operacional | Dashboard, relatorios por periodo e exportacao CSV |

## Inicio rapido

### Pre-requisitos

- PHP 8.1 ou superior com as extensoes exigidas pelo Laravel e pelo driver MySQL.
- [Composer 2](https://getcomposer.org/).
- Node.js 18 ou superior e npm.
- MySQL 8 ou compativel para executar a aplicacao localmente.

### 1. Clone e instale as dependencias

```bash
git clone https://github.com/WallacyBR/condoai.git
cd condoai

composer install
npm ci
```

### 2. Configure o ambiente

Crie o banco de dados e copie o arquivo de exemplo:

```bash
mysql -u root -p -e "CREATE DATABASE condoai CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
cp .env.example .env
```

No arquivo `.env`, ajuste ao menos estas variaveis para o seu MySQL local:

```dotenv
APP_NAME=CondoAI
APP_URL=http://127.0.0.1:8000
APP_ENV=local
APP_DEBUG=true

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=condoai
DB_USERNAME=root
DB_PASSWORD=

TENANT_BASE_DOMAIN=localhost
```

Gere as chaves da aplicacao e do JWT, crie o link dos arquivos publicos e carregue a base demonstrativa:

```bash
php artisan key:generate
php artisan jwt:secret
php artisan storage:link
php artisan migrate --seed
```

> Para recomeçar uma base local descartando todos os dados, use `php artisan migrate:fresh --seed`.

### 3. Inicie backend e frontend

Em terminais separados:

```bash
# Terminal 1: Laravel
php artisan serve
```

```bash
# Terminal 2: Vite com recarga em desenvolvimento
npm run dev
```

Abra [http://127.0.0.1:8000](http://127.0.0.1:8000). O Vite e necessario no desenvolvimento para servir os assets e habilitar a atualizacao em tempo real.

## Dados de demonstracao

O comando `php artisan migrate --seed` cria um condominio, estrutura de unidades e dados representativos para os principais modulos. Ele e adequado para explorar os fluxos, desenvolver telas e validar integracoes sem depender de dados reais.

Use a conta abaixo somente em ambiente local:

| Campo | Valor |
| --- | --- |
| E-mail | `teste@teste.com` |
| Senha | `teste` |
| Perfil | SysOp |

As credenciais de demonstracao nunca devem ser mantidas em ambientes publicos ou de producao.

## Uso local e multi-condominio

No ambiente local, o middleware de tenancy seleciona automaticamente o primeiro condominio cadastrado. Isso deixa o desenvolvimento simples em `localhost` ou `127.0.0.1`.

Em um ambiente com dominios reais, configure `TENANT_BASE_DOMAIN` com o dominio principal e associe o campo `domain` de cada condominio ao subdominio correspondente. Por exemplo, com `TENANT_BASE_DOMAIN=exemplo.com`, o condominio cujo dominio e `sol-nascente` sera resolvido em `https://sol-nascente.exemplo.com`.

O frontend tambem guarda o condominio atual no navegador apos o login. A API, contudo, usa o contexto resolvido no servidor como fonte de verdade para os recursos protegidos.

## API e documentacao

Com a aplicacao em execucao, a documentacao interativa fica disponivel em [http://127.0.0.1:8000/docs](http://127.0.0.1:8000/docs). Ela tambem disponibiliza colecao Postman e especificacao OpenAPI pelos endpoints do Scribe.

Sempre que rotas da API forem alteradas, atualize a documentacao:

```bash
php artisan scribe:generate
```

As rotas protegidas usam JWT. Primeiro autentique-se em `POST /api/auth/login` e envie o token retornado no cabecalho:

```http
Authorization: Bearer SEU_TOKEN_JWT
Accept: application/json
```

Exemplo de login local:

```bash
curl --request POST http://127.0.0.1:8000/api/auth/login \
  --header "Accept: application/json" \
  --header "Content-Type: application/json" \
  --data '{"email":"teste@teste.com","password":"teste"}'
```

### Relatorios operacionais

O endpoint `GET /api/reports/operational` aceita os parametros `type`, `start_date` e `end_date`. Os tipos disponiveis sao:

`overview`, `residents`, `packages`, `occurrences`, `parking`, `bookings`, `maintenance` e `access`.

Para exportar o mesmo recorte em CSV, use `GET /api/reports/operational/export` com os mesmos parametros. O backend deriva o condominio do usuario/contexto autenticado; o cliente nao deve informar `condominium_id`.

## Testes e qualidade

Os testes de backend usam SQLite em memoria, conforme definido em `phpunit.xml`. Para uma instalacao nova, o arquivo de apoio `.env.testing.example` tambem pode ser copiado para `.env.testing`.

```bash
# Backend
php artisan test --display-warnings

# Frontend
npm test -- --runInBand

# Padrao de codigo PHP
./vendor/bin/pint --test

# Build de verificacao do frontend
npm run build

# Verificacao de espacos e conflitos no diff
git diff --check
```

Ao implementar uma funcionalidade, adicione ou atualize testes PHPUnit em `tests/` e testes Jest em `resources/tests/`. Mudancas de rota tambem exigem uma nova geracao do Scribe.

## Build de producao

Antes de publicar uma versao, instale dependencias de producao, gere os assets otimizados, aplique as migrations e recarregue os caches do Laravel:

```bash
composer install --no-dev --optimize-autoloader --no-interaction
npm ci
npm run build

php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Configure `APP_ENV=production`, `APP_DEBUG=false`, um `APP_KEY` e um `JWT_SECRET` exclusivos, alem das credenciais reais do banco e dos servicos integrados. Mantenha `storage/` e `bootstrap/cache/` gravaveis pelo usuario do servidor web. O script [`scripts/set-permissions.sh`](scripts/set-permissions.sh) ajuda a padronizar essas permissoes em servidores Linux.

Nao use `migrate:fresh` em producao: esse comando remove as tabelas existentes.

## Estrutura do projeto

```text
app/
  Http/Controllers/API/   # Endpoints e regras da API
  Http/Middleware/        # Autenticacao e resolucao do tenant
  Models/                 # Modelos e relacoes Eloquent
  Services/               # Servicos de dominio, inclusive financeiro
database/
  migrations/             # Evolucao do schema
  seeders/                # Dados de demonstracao e configuracao inicial
resources/
  js/                     # Aplicacao Vue, rotas, componentes, paginas e servicos
  tests/                  # Testes Jest dos componentes Vue
routes/
  api.php                 # API REST
  web.php                 # Shell da SPA e rotas web
tests/
  Feature/ e Unit/        # Testes PHPUnit
public/
  images/                 # Identidade visual publica do projeto
```

## Contribuicao

Contribuicoes sao bem-vindas. Antes de abrir uma issue ou pull request:

1. Consulte o [guia de contribuicao](.github/CONTRIBUTING.md).
2. Descreva claramente o problema, o contexto e a forma de reproducao.
3. Mantenha o escopo do pull request objetivo e inclua testes para o comportamento alterado.
4. Execute os testes backend e frontend, o build e a verificacao de estilo antes de enviar.
5. Escreva mensagens de commit e comentarios de codigo em portugues, seguindo PSR-12 no PHP.
6. Para telas novas ou alteradas, siga o [manual de design](DESIGN_MANUAL.md).

## Seguranca

Nao publique vulnerabilidades sensiveis em issues abertas. Use o recurso de Security Advisories do GitHub para relatar o problema de maneira privada, incluindo impacto, passos de reproducao e uma possivel correcao quando houver.

Nunca versione arquivos `.env`, tokens JWT, chaves de servicos, senhas ou dados pessoais de condominios. Use sempre `.env.example` como referencia de configuracao.

## Licenca

O projeto e disponibilizado sob a [licenca MIT](LICENSE).

---

Feito para tornar a gestao condominial menos fragmentada, mais rastreavel e muito mais facil de operar.
