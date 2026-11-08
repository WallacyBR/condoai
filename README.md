<p align="center">
  <img src="public/images/predihub_logo_light.png" alt="PrediHub" width="360">
</p>

<h1 align="center">PrediHub</h1>

<p align="center">Plataforma de código aberto para gestão e operação de condomínios.</p>

<p align="center">
  <a href="https://laravel.com"><img src="https://img.shields.io/badge/Laravel-10-FF2D20?logo=laravel&logoColor=white" alt="Laravel 10"></a>
  <a href="https://vuejs.org"><img src="https://img.shields.io/badge/Vue.js-3-4FC08D?logo=vuedotjs&logoColor=white" alt="Vue 3"></a>
  <a href="https://www.php.net"><img src="https://img.shields.io/badge/PHP-%3E%3D8.1-777BB4?logo=php&logoColor=white" alt="PHP 8.1 ou superior"></a>
  <a href="LICENSE"><img src="https://img.shields.io/badge/licen%C3%A7a-MIT-blue.svg" alt="Licença MIT"></a>
</p>

O PrediHub reúne ferramentas para apoiar a administração e a operação de condomínios em uma aplicação web. O repositório contém o backend Laravel, a interface Vue e um aplicativo Flutter em desenvolvimento no diretório [`mobile/`](mobile/README.md).

> **Antes de usar em produção:** avalie a segurança, o isolamento entre condomínios, as integrações, os backups e a adequação às necessidades da sua operação. A presença de uma funcionalidade no repositório não substitui sua validação no ambiente de destino.

## Funcionalidades

O código reúne módulos para:

- Condomínios, unidades, moradores, usuários, perfis e permissões.
- Portaria, visitantes, autorizações de acesso e encomendas.
- Veículos, pets, vagas, áreas comuns e reservas.
- Ocorrências, comunicados, documentos e classificados.
- Manutenção, equipamentos, cronogramas e registros.
- Finanças, contas a pagar e a receber, fluxo de caixa e relatórios.
- API REST com autenticação JWT e documentação gerada pelo Laravel Scribe.

A interface web utiliza Vue 3 e possui recursos de PWA. As capacidades de cada módulo devem ser verificadas conforme a versão e a configuração em uso.

## Tecnologias

| Camada | Tecnologias |
| --- | --- |
| Backend | PHP 8.1+, Laravel 10, Eloquent, JWT Auth |
| Frontend | Vue 3, Vite, Vue Router, Pinia, Tailwind CSS |
| Banco | MySQL para a aplicação; SQLite em memória nos testes configurados |
| Aplicativo móvel | Flutter; consulte o [README do aplicativo](mobile/README.md) |
| Testes e qualidade | PHPUnit, Jest, Laravel Pint |

## Começar localmente

### Pré-requisitos

- PHP 8.1 ou superior, com as extensões exigidas pelas dependências e pelo driver MySQL.
- Composer 2, Node.js 18 ou superior e npm.
- MySQL 8 ou compatível e um banco vazio para desenvolvimento.

### Instalação

```bash
git clone https://github.com/WallacyBR/condoai.git
cd condoai
composer install
npm ci
cp .env.example .env
```

Crie um banco local e configure no `.env` as variáveis `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` e `DB_PASSWORD`. Confira também `APP_URL` e as demais opções de `.env.example`. Não compartilhe nem versione o `.env`.

Gere as chaves e inicialize a base **local de demonstração**:

```bash
php artisan key:generate
php artisan jwt:secret
php artisan storage:link
php artisan migrate --seed
```

Inicie a aplicação em dois terminais:

```bash
php artisan serve
```

```bash
npm run dev
```

Acesse [http://127.0.0.1:8000](http://127.0.0.1:8000). O Vite serve os arquivos da interface durante o desenvolvimento.

### Dados de demonstração

Os seeders incluem dados fictícios e contas com senhas conhecidas. A conta `teste@teste.com`, com senha `teste`, destina-se exclusivamente ao ambiente local. **Não execute os seeders de demonstração nem exponha essas contas em produção ou em um ambiente público.**

`php artisan migrate:fresh --seed` apaga as tabelas antes de recriá-las; utilize-o apenas em uma base local descartável.

## API e contexto de condomínio

Com a aplicação em execução, a documentação gerada pelo Scribe está disponível em `/docs`. Rotas protegidas utilizam JWT; obtenha um token em `POST /api/auth/login` e envie-o no cabeçalho `Authorization: Bearer <token>`.

O projeto possui resolução de contexto de condomínio no backend. Configuração, permissões e isolamento devem ser verificados para cada ambiente. Para alterar rotas e atualizar a documentação gerada, execute:

```bash
php artisan scribe:generate
```

## Testes e qualidade

```bash
# Backend
php artisan test --display-warnings

# Frontend
npm test -- --runInBand

# Estilo PHP e build
./vendor/bin/pint --test
npm run build
```

Os testes de backend estão configurados para SQLite em memória; consulte `phpunit.xml` e `.env.testing.example`. Para contribuir, inclua testes proporcionais à alteração e informe eventuais verificações não realizadas.

## Implantação e segurança

Antes de implantar, revise as configurações do ambiente e o processo de deploy. Em particular:

- Configure `APP_ENV=production` e `APP_DEBUG=false`; gere chaves exclusivas e mantenha segredos fora do repositório.
- Faça backup e valide as migrações antes de aplicá-las. Não execute `migrate:fresh` em bases com dados a preservar.
- Use `php artisan migrate --force` para aplicar migrações em produção após a revisão. Não carregue dados de demonstração.
- Avalie autenticação, autorização, isolamento de dados, privacidade e recuperação antes de usar dados reais.
- Revise o workflow de deploy e seus segredos antes de habilitá-lo ou adaptá-lo à sua infraestrutura. A configuração versionada não substitui um plano de implantação.

Para relatar uma vulnerabilidade, prefira o relato privado de vulnerabilidades na aba **Security** do GitHub, se disponível. Não inclua segredos ou dados pessoais em issues públicas.

## Estrutura do repositório

```text
app/                 Backend, controladores, modelos e serviços
database/            Migrações e seeders
resources/js/        Interface Vue
resources/tests/     Testes da interface
routes/              Rotas web e da API
tests/               Testes PHP
mobile/              Aplicativo Flutter
```

## Contribuir

Contribuições são bem-vindas. Leia o [guia de contribuição](.github/CONTRIBUTING.md) e o [manual de design](DESIGN_MANUAL.md). Descreva o problema e o comportamento esperado, mantenha as alterações focadas e relate os testes executados. Para temas de segurança, use o canal privado indicado acima.

## Licença

Distribuído sob a [licença MIT](LICENSE). Direitos sobre marcas e elementos visuais devem ser avaliados separadamente quando aplicável.
