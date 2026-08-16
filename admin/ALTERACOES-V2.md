# Alterações da versão 2

A geração de relatório foi alterada para separar os dados por caixa.

## O que mudou

- o resultado geral continua sendo calculado;
- cada caixa passa a ter seu próprio acumulador diário;
- o painel mostra uma seção geral e outra com os resultados de cada caixa;
- o relatório final JSON salva `geral` e `caixas`;
- foi incluído o campo `maiorProdutos`;
- nenhuma leitura individual é armazenada.

## Arquivos alterados

```text
api/_utils.php
api/atualizar-fila.php
api/dados.php
api/relatorio.php
assets/app.js
assets/style.css
README.md
```

## Atenção para testes já feitos

Se o arquivo do dia atual foi criado pela versão anterior, os dados antigos não possuem separação por caixa.

Para testar a versão 2 do zero, apague o arquivo da data atual em:

```text
data/dias/
```

Exemplo:

```text
data/dias/2026-08-16.json
```

Se também já tiver fechado um relatório de teste, pode apagar o arquivo da mesma data em:

```text
data/relatorios/
```

Depois faça novas leituras pelo simulador.
