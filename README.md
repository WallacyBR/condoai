# CondoAI

CondoAI é uma plataforma SaaS para gestão de condomínios desenvolvida em Laravel e Vue.js.
Ela permite administrar unidades, despesas, comunicações e serviços de forma centralizada.

## Instalação

1. Clone o repositório e acesse a pasta do projeto:

```bash
git clone <repo-url>
cd condoai
```

2. Instale as dependências PHP e JavaScript:

```bash
composer install
npm install
```

3. Copie o arquivo de ambiente e gere a chave da aplicação:

```bash
cp .env.example .env
php artisan key:generate
```

4. Configure o banco de dados no `.env` e ajuste `APP_URL` para o domínio principal da aplicação.

## Configuração de Subdomínios

Utilize um DNS curinga (`*.seusite.com`) apontando para o servidor. Cada condomínio poderá ser acessado por um subdomínio como `condominio1.seusite.com`.
Garanta que o servidor web encaminhe todas as requisições para o diretório `public/` do Laravel.

## Executando Testes

Para validar o backend, execute:

```bash
./vendor/bin/phpunit
```

Se existirem testes front-end com Jest, utilize:

```bash
npm test
```

## Licença

Este projeto está licenciado sob a licença MIT.
