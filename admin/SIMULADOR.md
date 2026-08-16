# Simulador de Sensor

O arquivo `simular-sensor.html` foi criado para facilitar os testes do FilaCerta enquanto os sensores físicos ainda não estão integrados ao sistema.

Ele simula o envio de uma leitura para um caixa cadastrado, utilizando o mesmo endereço que será usado futuramente pelos sensores reais.

## Onde colocar o arquivo

Coloque o arquivo `simular-sensor.html` na pasta principal do projeto.

Exemplo:

```text
filacerta-admin/
├── index.html
├── simular-sensor.html
├── assets/
├── api/
└── data/
```

Se estiver utilizando XAMPP, a pasta pode ficar em:

```text
C:\xampp\htdocs\filacerta\admin\
```

## Como acessar

Com o Apache iniciado, abra no navegador:

```text
http://localhost/filacerta/admin/simular-sensor.html
```

## Antes de utilizar

É necessário ter pelo menos um caixa cadastrado no painel administrativo.

Acesse:

```text
http://localhost/filacerta/admin/
```

Clique em **Novo caixa** e faça o cadastro.

Depois volte ao simulador.

## Como fazer uma simulação

Na tela do simulador:

1. Selecione o caixa que receberá a leitura.
2. Informe a quantidade de pessoas na fila.
3. Informe a quantidade aproximada de produtos.
4. Informe o tempo estimado de espera em minutos.
5. Clique em **Enviar leitura**.

Exemplo:

```text
Caixa: Caixa 01
Pessoas: 3
Produtos: 22
Tempo estimado: 4 minutos
```

Depois do envio, o sistema deverá informar que a leitura foi registrada.

## O que acontece quando a leitura é enviada

O simulador envia os dados para:

```text
api/atualizar-fila.php
```

O PHP atualiza o estado atual do caixa no arquivo:

```text
data/caixas.json
```

A leitura também é considerada no resumo utilizado para gerar o relatório do dia.

O painel administrativo é atualizado automaticamente, então os novos valores aparecerão sem a necessidade de alterar o JSON manualmente.

## Fluxo do simulador

```text
Simulador
    ↓
api/atualizar-fila.php
    ↓
caixas.json
    ↓
Painel administrativo
```

O sensor físico deverá utilizar esse mesmo fluxo futuramente.

A principal diferença será apenas a origem dos dados.

```text
Hoje:
Simulador → PHP → JSON

Futuramente:
Sensor físico → PHP → JSON
```

## Exemplo de teste

Cadastre o `Caixa 01` e envie:

```text
Pessoas: 5
Produtos: 36
Tempo estimado: 7
```

Ao voltar ao painel administrativo, o caixa deverá apresentar aproximadamente:

```text
Caixa 01
Aberto
5 pessoas
36 produtos
7 min
```

## Relatório do dia

Cada leitura enviada pelo simulador também atualiza os dados usados no relatório diário.

O sistema acumula informações como:

- quantidade de leituras recebidas;
- média de pessoas;
- média de produtos;
- média do tempo de espera;
- maior fila registrada;
- maior tempo de espera registrado.

Esses dados podem ser visualizados no painel administrativo.

Ao final do dia, o botão **Fechar dia e gerar relatório** cria o arquivo de relatório correspondente à data atual.

## Importante

O simulador existe apenas para testes e desenvolvimento.

Quando os sensores reais forem integrados, o arquivo poderá ser removido sem alterar o funcionamento principal do sistema, pois os sensores utilizarão o mesmo endpoint de atualização.

