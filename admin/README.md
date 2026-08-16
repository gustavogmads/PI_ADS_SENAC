# FilaCerta - Painel administrativo

Primeira versão do painel administrativo do FilaCerta.

## Ideia desta versão

O painel foi feito com o mínimo de estrutura possível:

- uma única tela administrativa;
- HTML, CSS e JavaScript puros;
- PHP apenas para ler e gravar dados;
- nenhum banco de dados;
- caixas atuais salvos em JSON;
- relatório diário salvo em JSON;
- sem framework e sem biblioteca externa.

## O que já funciona

- cadastrar caixa;
- editar caixa;
- abrir ou fechar caixa;
- excluir caixa;
- mostrar pessoas, produtos e tempo estimado;
- atualizar o painel automaticamente a cada 10 segundos;
- receber leituras externas por HTTP;
- acumular os dados do dia sem guardar todas as leituras;
- gerar um relatório final do dia.

## Pastas

```text
filacerta-admin/
├── index.html
├── assets/
│   ├── app.js
│   └── style.css
├── api/
│   ├── _utils.php
│   ├── dados.php
│   ├── caixas.php
│   ├── atualizar-fila.php
│   └── relatorio.php
└── data/
    ├── caixas.json
    ├── dias/
    └── relatorios/
```

## Como os dados são salvos

### `data/caixas.json`

Guarda somente o estado atual de cada caixa.

Exemplo:

```json
[
  {
    "id": 1,
    "nome": "Caixa 01",
    "aberto": true,
    "pessoas": 3,
    "produtos": 22,
    "tempoEstimado": 4,
    "atualizadoEm": "2026-08-16T10:00:00-03:00"
  }
]
```

### `data/dias/AAAA-MM-DD.json`

Guarda apenas os acumuladores usados no relatório.

Ele não registra cada leitura individual. Isso reduz bastante a quantidade de dados.

### `data/relatorios/AAAA-MM-DD.json`

É criado quando o administrador clica em **Fechar dia e gerar relatório**.

## Endpoint para o sensor

O sensor ou uma simulação pode enviar um `POST` para:

```text
api/atualizar-fila.php
```

Corpo JSON:

```json
{
  "caixaId": 1,
  "pessoas": 3,
  "produtos": 22,
  "tempoEstimado": 4
}
```

Nesta primeira versão, o tempo pode vir pronto no envio. Depois podemos fazer o próprio sistema calcular esse valor.

## Relatório diário

O relatório atual calcula:

- total de leituras;
- média de pessoas;
- média de produtos;
- média de espera;
- maior fila registrada;
- maior tempo de espera registrado.

A forma foi escolhida para evitar armazenar milhares de leituras.

## Hospedagem

É necessário um servidor com PHP.

Em uma hospedagem comum:

1. envie a pasta para o servidor;
2. confirme que o PHP está ativo;
3. dê permissão de escrita à pasta `data`;
4. abra `index.html`.

Exemplo de permissão em Linux:

```text
chmod -R 775 data
```

## Observação sobre segurança

O arquivo `.htaccess` dentro de `data` bloqueia o acesso direto aos JSON em servidores Apache.

Se a hospedagem não usar Apache, o ideal é mover a pasta `data` para fora da pasta pública ou criar uma regra equivalente no servidor.

## Próximos passos

A próxima parte natural é definir:

1. como o tempo de espera será calculado;
2. como os sensores vão identificar cada caixa;
3. autenticação simples do painel administrativo;
4. relatório visual ou exportação em PDF, se for necessário.

## Relatório por caixa

A versão 2 separa o relatório em duas partes:

- `geral`: resultado de todas as leituras recebidas no dia;
- `caixas`: resultado individual de cada caixa.

Cada caixa apresenta:

- total de leituras;
- média de pessoas;
- média de produtos;
- média do tempo de espera;
- maior fila;
- maior quantidade de produtos;
- maior tempo de espera.

Exemplo simplificado:

```json
{
  "data": "2026-08-16",
  "fechadoEm": "2026-08-16T18:00:00-03:00",
  "geral": {
    "totalLeituras": 10,
    "mediaPessoas": 4.2,
    "mediaProdutos": 28.5,
    "mediaTempo": 6.1,
    "maiorFila": 9,
    "maiorProdutos": 60,
    "maiorTempo": 14
  },
  "caixas": [
    {
      "caixaId": 1,
      "nome": "Caixa 01",
      "totalLeituras": 5,
      "mediaPessoas": 3.4,
      "mediaProdutos": 22.8,
      "mediaTempo": 4.8,
      "maiorFila": 6,
      "maiorProdutos": 42,
      "maiorTempo": 8
    }
  ]
}
```

O arquivo diário continua armazenando apenas somas e valores máximos. As leituras individuais não são salvas.
