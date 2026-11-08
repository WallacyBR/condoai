# Guia de Contribuição

Obrigado por querer contribuir com este projeto. Para manter a qualidade do código, siga as regras abaixo:

## Mensagens de commit

- Escreva **sempre em português**.
- Utilize uma linha de assunto objetiva e no imperativo.
- Se necessário, adicione um corpo explicativo após uma linha em branco.

Exemplo:

```
Implementa autenticação JWT

Adiciona middleware e testes de autenticação. Atualiza configurações.
```

## Código e estilo

- Respeite o padrão **PSR-12** para PHP.
- Evite duplicação de lógica e prefira componentes reutilizáveis.
- Comentários no código também devem estar em português.

## Testes

Execute os testes antes de enviar pull requests:

```bash
./vendor/bin/phpunit
npm test
```

Pull requests são aceitos somente com todos os testes passando.

## Variáveis de ambiente

Nunca commit credenciais reais. Use `.env.example` como referência.

## Template de commit

Para facilitar, utilize o arquivo `.github/commit_template.txt` como ponto de partida para suas mensagens de commit. Configure com:

```bash
git config commit.template .github/commit_template.txt
```
