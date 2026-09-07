# FilaCerta - Sistema de Gerenciamento de Filas

> **Projeto Integrador - Desenvolvimento de Sistemas Orientado a Dispositivos Móveis e Baseados na Web**
> 
> ANÁLISE E DESENVOLVIMENTO DE SISTEMAS - SENAC

---

## Sobre o Projeto

O **FilaCerta** é um sistema web desenvolvido para monitorar e gerenciar filas de caixas em supermercados e estabelecimentos comerciais. A proposta é simples: o cliente entra na loja, escaneia um QR Code e vê em tempo real qual caixa tem o menor tempo de espera - sem precisar fazer cadastro ou login.

Do lado do estabelecimento, um painel administrativo permite que o gerente acompanhe o fluxo de cada caixa, cadastre e gerencie os operadores e gere relatórios diários de desempenho.

O sistema foi construído de forma intencionalmente simples: HTML, CSS, JavaScript e PHP puro, com armazenamento em arquivos JSON - sem banco de dados SQL. Essa escolha facilita o deploy em qualquer hospedagem com PHP e elimina toda a complexidade de configuração de banco de dados e conexões.

### O sistema é composto por três partes:

- **Painel Administrativo** - para o gerente cadastrar caixas e monitorar filas em tempo real
- **Página do Cliente** - tela pensada para celular, acessada via QR Code, que mostra qual caixa tem o menor tempo de espera
- **Simulador de Sensor** - página auxiliar para simular o envio de dados de leitura, já que o projeto não utiliza sensor físico

---

## Como Funciona na Prática

1. O gerente acessa o **painel administrativo** e cadastra os caixas (com nome e status aberto/fechado)
2. Um sensor instalado no caixa (ou o simulador, durante testes) envia via HTTP o número de pessoas e a quantidade de produtos
3. O **backend calcula automaticamente o tempo estimado de espera** usando a fórmula do sistema
4. O painel atualiza automaticamente a cada **10 segundos** com esses dados
5. O cliente, ao entrar no mercado, escaneia um **QR Code** que abre a **página do cliente no celular**
6. A página mostra todos os caixas abertos, destaca o com menor espera e atualiza a cada **5 segundos**
7. Ao final do dia, o administrador pode gerar um **relatório** com as estatísticas completas de cada caixa

---

## Funcionalidades

### Painel Administrativo
- Cadastrar, editar, abrir, fechar e excluir caixas
- Cards de resumo no topo: total de caixas, caixas abertos, total de pessoas na fila e menor tempo de espera registrado
- Visualizar em tempo real por caixa: número de pessoas, quantidade de produtos e tempo estimado de espera
- Atualização automática a cada 10 segundos sem recarregar a página
- Receber leituras de sensores externos via requisição HTTP POST
- Gerar relatório do dia com um clique

### Página do Cliente (mobile)
- Interface projetada para celular
- Acessada via escaneamento de QR Code - sem cadastro ou login
- Exibe todos os caixas abertos com o tempo estimado de espera de cada um
- Destaca automaticamente o caixa recomendado (menor tempo de espera)
- Indicador de cor por tempo de espera:
  - Verde: até 5 minutos
  - Amarelo: de 6 a 10 minutos
  - Vermelho: acima de 10 minutos
- Mostra o horário da última atualização
- Atualiza os dados automaticamente a cada 5 segundos

### Critério de Recomendação de Caixa
O sistema recomenda o caixa com base em:
1. Menor `tempoEstimado`
2. Em caso de empate: menor quantidade de produtos
3. Em caso de segundo empate: menor quantidade de pessoas

### Relatório Diário
Ao fechar o dia, o sistema gera um relatório com:
- Total de leituras recebidas
- Total e média de pessoas e produtos
- Espera média, maior fila e maior tempo de espera registrados
- Resultado individual detalhado por caixa

### Simulador de Sensor
- Informe o caixa, número de pessoas e quantidade de produtos
- O sistema mostra uma prévia do tempo estimado enquanto os valores são digitados
- Ao enviar, o PHP calcula o tempo final e salva no sistema
- Facilita o desenvolvimento e os testes sem hardware

---

## Fórmula de Cálculo do Tempo de Espera

O tempo estimado (em minutos) é calculado automaticamente pelo backend:

```
tempo = (pessoas × 0,6) + (produtos × 0,08)
```

O resultado é arredondado para cima.

**Exemplo:**
```
3 pessoas + 22 produtos
(3 × 0,6) + (22 × 0,08) = 1,8 + 1,76 = 3,56 → 4 minutos
```

O cálculo fica em `api/_utils.php`, na função `calcularTempoEstimado()`. Os parâmetros podem ser ajustados futuramente com base em dados reais do estabelecimento.

---

## Tecnologias Utilizadas

| Camada | Tecnologia |
|---|---|
| Frontend | HTML5, CSS3, JavaScript puro (sem frameworks ou bibliotecas externas) |
| Backend | PHP (leitura, gravação e cálculo de dados via API) |
| Armazenamento | Arquivos JSON (sem banco de dados SQL) |
| Design visual | Desenvolvido com auxílio de inteligência artificial |
| Servidor | Qualquer hospedagem com suporte a PHP |

> **Por que JSON e não banco de dados?**
> A escolha por arquivos JSON elimina a necessidade de configurar banco de dados, fazer conexões e lidar com toda a infraestrutura do SQL. Os dados são salvos em arquivos de texto estruturados, funcionando como um banco de dados simples e suficiente para o escopo do projeto.

---

## Estrutura de Pastas

```
PI_ADS_SENAC/
└── admin/
    ├── index.html              # Painel administrativo principal
    ├── simular-sensor.html     # Simulador de sensor para testes
    ├── assets/
    │   ├── app.js              # Lógica principal do painel (atualiza a cada 10s)
    │   └── style.css           # Estilos da interface administrativa
    ├── api/
    │   ├── _utils.php          # Funções auxiliares + fórmula de cálculo do tempo
    │   ├── dados.php           # Leitura de dados gerais (usada pelo painel e cliente)
    │   ├── caixas.php          # CRUD de caixas (criar, editar, alternar, excluir)
    │   ├── atualizar-fila.php  # Recebe dados do sensor/simulador via POST
    │   └── relatorio.php       # Geração e fechamento do relatório diário
    ├── cliente/
    │   ├── index.html          # Página do cliente (acesso via QR Code, mobile)
    │   └── assets/
    │       ├── app.js          # Lógica do cliente (atualiza a cada 5s)
    │       └── style.css       # Estilos da tela do cliente
    └── data/
        ├── caixas.json         # Estado atual de cada caixa
        ├── dias/               # Acumuladores diários (um arquivo por dia)
        │   └── AAAA-MM-DD.json
        └── relatorios/         # Relatórios finais gerados por dia
            └── AAAA-MM-DD.json
```

---

## Como Rodar o Projeto

### Pré-requisitos
- Servidor com **PHP** habilitado - recomendamos o [XAMPP](https://www.apachefriends.org/) (Windows/Mac/Linux)
- Permissão de escrita na pasta `data/`

### Passo a passo

**1. Clone o repositório:**
```bash
git clone https://github.com/gustavogmads/PI_ADS_SENAC.git
```

**2. Mova a pasta `admin/` para o diretório público do seu servidor:**
- No XAMPP: cole dentro de `C:/xampp/htdocs/`
- No WAMP: cole dentro de `C:/wamp64/www/`
- No Laragon: cole dentro de `C:/laragon/www/`

**3. Inicie o servidor Apache** pelo painel de controle do XAMPP (ou equivalente)

**4. (Linux/Mac) Dê permissão de escrita à pasta `data/`:**
```bash
chmod -R 775 data/
```
> No Windows com XAMPP geralmente não é necessário.

**5. Acesse o painel administrativo no navegador:**
```
http://localhost/admin/index.html
```

**6. Acesse a página do cliente (simule o acesso via QR Code):**
```
http://localhost/admin/cliente/index.html
```

**7. Para testar sem sensor físico, use o simulador:**
```
http://localhost/admin/simular-sensor.html
```

### Testando no celular (mesma rede Wi-Fi)

`localhost` não funciona no celular. Use o **IP local do seu computador** no lugar:

```
http://192.168.X.X/admin/cliente/index.html
```

Para descobrir seu IP local no Windows: abra o Prompt de Comando e digite `ipconfig`. Use o valor de "Endereço IPv4".

> O computador e o celular precisam estar na mesma rede Wi-Fi.

---

## API - Endpoint do Sensor

O sistema expõe um endpoint HTTP para receber leituras de sensores externos ou do simulador. O tempo estimado **não precisa ser enviado** - o backend calcula automaticamente.

**Requisição:**
```
POST /admin/api/atualizar-fila.php
Content-Type: application/json
```

**Corpo da requisição (JSON):**
```json
{
  "caixaId": 1,
  "pessoas": 3,
  "produtos": 22
}
```

**Resposta do servidor:**
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

| Campo | Tipo | Descrição |
|---|---|---|
| `caixaId` | inteiro | ID do caixa que enviou a leitura |
| `pessoas` | inteiro | Número de pessoas atualmente na fila |
| `produtos` | inteiro | Total de produtos aguardando processamento |

---

## Formato dos Dados Salvos

### `data/caixas.json` - Estado atual dos caixas
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

### `data/relatorios/AAAA-MM-DD.json` - Relatório diário
```json
{
  "data": "2026-08-16",
  "fechadoEm": "2026-08-16T18:00:00-03:00",
  "geral": {
    "totalLeituras": 10,
    "totalPessoas": 42,
    "totalProdutos": 285,
    "mediaEspera": 6.1,
    "maiorFila": 9,
    "maiorProdutos": 60,
    "maiorEspera": 14
  },
  "caixas": [
    {
      "caixaId": 1,
      "nome": "Caixa 01",
      "totalLeituras": 5,
      "totalPessoas": 17,
      "totalProdutos": 114,
      "mediaEspera": 4.8,
      "maiorFila": 6,
      "maiorProdutos": 42,
      "maiorEspera": 8
    }
  ]
}
```

---

## QR Code

O QR Code não armazena dados das filas. Ele contém apenas o endereço (URL) da página do cliente.

Fluxo de acesso:
```
Cliente escaneia o QR Code
        ↓
Abre /cliente/ no celular
        ↓
cliente/assets/app.js
        ↓
../api/dados.php
        ↓
caixas.json
```

Quando o endereço final da hospedagem estiver definido, basta gerar um único QR Code apontando para:
```
https://SEU-ENDERECO/cliente/
```

---

## Observação sobre Segurança

O arquivo `.htaccess` dentro da pasta `data/` bloqueia o acesso direto aos arquivos JSON em servidores Apache.

Se a hospedagem não utilizar Apache, recomenda-se mover a pasta `data/` para fora do diretório público ou criar uma regra equivalente no servidor.

---

## Histórico de Versões

| Versão | Descrição |
|---|---|
| v1 | Painel administrativo inicial com HTML, CSS, JS e PHP puro; armazenamento em JSON |
| v2 | Relatório diário separado por caixa com estatísticas individuais |
| v5 | Atualização das orientações do simulador de sensor |
| v6 | Cálculo de tempo de espera automático pelo backend; página do cliente com versão colorida |

---

## Possibilidades Futuras

- Exportação do relatório diário para planilha Excel
- Implementação da identidade visual definitiva do FilaCerta
- Autenticação simples no painel administrativo
- Integração com sensor físico real instalado nos caixas
- Ajuste da fórmula de tempo com base em dados reais do estabelecimento

---

## Integrantes

| Nome |
|---|
| Caio Leandro Vasconcelos de Carvalho |
| Gustavo Gonçalves de Mendonça |
| Izabella Albuquerque Pará Moreira de Souza |
| Julia Sena Guedes |
| Nicole Velloso da Silva Melo |
| Vithor Tozetto Ferreira |

---

*Projeto desenvolvido para a disciplina de Projeto Integrador - Análise e Desenvolvimento de Sistemas (SENAC)*

