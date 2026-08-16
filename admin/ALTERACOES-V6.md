# Alterações da versão 6

O cálculo do tempo de espera passou a ser automático.

## Antes

O simulador enviava:

```text
pessoas
produtos
tempo estimado
```

O tempo precisava ser digitado manualmente.

## Agora

O simulador envia apenas:

```text
pessoas
produtos
```

O PHP calcula o tempo antes de salvar a leitura.

## Fórmula inicial

```text
tempo = (pessoas × 0,6) + (produtos × 0,08)
```

O resultado é arredondado para cima.

Exemplo:

```text
3 pessoas + 22 produtos = 4 minutos
5 pessoas + 36 produtos = 6 minutos
8 pessoas + 54 produtos = 10 minutos
```

## Arquivos alterados

```text
api/_utils.php
api/atualizar-fila.php
simular-sensor.html
SIMULADOR.md
```

Nenhuma alteração foi necessária na tela do cliente ou no relatório.

Eles continuam utilizando o campo `tempoEstimado`, que agora é preenchido automaticamente pelo backend.
