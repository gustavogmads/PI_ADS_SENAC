# Simulador de Sensor

O arquivo `simular-sensor.html` permite testar o FilaCerta sem utilizar um sensor físico.

Nesta versão, o simulador não pede mais o tempo de espera.

O usuário informa apenas:

- caixa;
- quantidade de pessoas;
- quantidade de produtos.

O tempo é calculado automaticamente.

## Como acessar

Com o projeto no XAMPP e o Apache iniciado:

```text
http://localhost/filacerta-admin/simular-sensor.html
```

## Como testar

Primeiro cadastre pelo menos um caixa no painel administrativo.

Depois abra o simulador e informe, por exemplo:

```text
Caixa: Caixa 01
Pessoas: 3
Produtos: 22
```

Enquanto os valores são digitados, a tela já mostra uma previsão do tempo.

Ao clicar em **Enviar leitura**, o PHP calcula novamente o tempo e salva o resultado.

## Fórmula atual

A primeira versão utiliza uma fórmula simples:

```text
tempo = (pessoas × 0,6) + (produtos × 0,08)
```

O resultado é arredondado para cima.

Exemplo:

```text
3 pessoas
22 produtos

(3 × 0,6) + (22 × 0,08)
1,8 + 1,76
3,56

Tempo estimado: 4 minutos
```

Os valores são apenas parâmetros iniciais do protótipo.

Depois, eles podem ser ajustados usando dados reais de atendimento do supermercado.

## Onde a fórmula fica

O cálculo principal está em:

```text
api/_utils.php
```

Na função:

```text
calcularTempoEstimado()
```

O simulador possui a mesma conta apenas para mostrar uma prévia antes do envio.

O valor usado pelo sistema é sempre o calculado pelo PHP.

## O que o simulador envia

Agora o envio é mais simples:

```json
{
  "caixaId": 1,
  "pessoas": 3,
  "produtos": 22
}
```

O servidor responde com:

```json
{
  "ok": true,
  "mensagem": "Fila atualizada.",
  "caixaId": 1,
  "pessoas": 3,
  "produtos": 22,
  "tempoEstimado": 4
}
```

## Fluxo

```text
Pessoas + produtos
       ↓
Simulador
       ↓
atualizar-fila.php
       ↓
calcularTempoEstimado()
       ↓
caixas.json
       ↓
Painel e tela do cliente
```

Quando o sensor físico for integrado, ele também poderá enviar apenas a quantidade de pessoas e produtos.
