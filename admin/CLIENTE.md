# FilaCerta - Versão do cliente

A versão do cliente fica na pasta:

```text
cliente/
```

Ela foi feita para ser aberta pelo celular depois que o cliente escanear um QR Code.

## O que a tela mostra

A versão do cliente possui somente uma tela.

Ela apresenta:

- caixa recomendado;
- tempo estimado de espera;
- quantidade de pessoas;
- quantidade estimada de produtos;
- todos os caixas abertos;
- sinalização verde, amarela ou vermelha;
- horário da última atualização.

O cliente não precisa fazer login.

## Endereço da tela

Em uma hospedagem, o endereço será parecido com:

```text
https://seudominio.com/filacerta/cliente/
```

Esse é o endereço que deve ser colocado no QR Code.

## Teste no computador

Se o projeto estiver em:

```text
C:\xampp\htdocs\filacerta-admin\
```

abra:

```text
http://localhost/filacerta-admin/cliente/
```

## Teste pelo celular na mesma rede

`localhost` não funciona no celular porque apontaria para o próprio aparelho.

Para testar pelo celular, use o IP local do computador.

Exemplo:

```text
http://192.168.0.10/filacerta-admin/cliente/
```

O computador e o celular precisam estar na mesma rede.

## QR Code

O QR Code não guarda os dados das filas.

Ele apenas contém o endereço da tela do cliente.

Fluxo:

```text
Cliente escaneia o QR Code
        ↓
abre /cliente/
        ↓
cliente/assets/app.js
        ↓
../api/dados.php
        ↓
caixas.json
```

Por isso, quando a URL final da hospedagem estiver definida, basta gerar um QR Code apontando para:

```text
https://SEU-ENDERECO/cliente/
```

Não é necessário criar um QR Code diferente para cada caixa.

## Atualização

A página consulta os dados a cada 5 segundos.

Ela usa:

```text
../api/dados.php
```

Nenhum dado é gravado pelo cliente.

A versão do cliente é somente leitura.

## Recomendação de caixa

A recomendação considera primeiro o menor `tempoEstimado`.

Se dois caixas tiverem o mesmo tempo, o sistema usa como desempate:

1. menor quantidade de produtos;
2. menor quantidade de pessoas.

Caixas sem estimativa de tempo continuam aparecendo na lista, mas não entram na recomendação.

## Cores

Nesta versão os limites estão definidos no JavaScript:

```text
até 5 minutos: verde
de 6 a 10 minutos: amarelo
acima de 10 minutos: vermelho
```

Esses valores podem ser alterados em:

```text
cliente/assets/app.js
```

Nas constantes:

```javascript
const LIMITE_VERDE = 5;
const LIMITE_AMARELO = 10;
```
