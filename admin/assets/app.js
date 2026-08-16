const API = "api";

const listaCaixas = document.getElementById("listaCaixas");
const formCaixa = document.getElementById("formCaixa");
const caixaId = document.getElementById("caixaId");
const caixaNome = document.getElementById("caixaNome");
const caixaAberto = document.getElementById("caixaAberto");
const relatorioHoje = document.getElementById("relatorioHoje");

// Guarda a lista atual em memória para facilitar a edição.
let caixas = [];

document.getElementById("dataHoje").textContent =
    new Date().toLocaleDateString("pt-BR");

document.getElementById("btnNovoCaixa").addEventListener("click", () => {
    limparFormulario();
    formCaixa.classList.remove("escondido");
});

document.getElementById("btnCancelar").addEventListener("click", () => {
    limparFormulario();
    formCaixa.classList.add("escondido");
});

formCaixa.addEventListener("submit", async (event) => {
    event.preventDefault();

    const dados = {
        acao: caixaId.value ? "editar" : "cadastrar",
        id: caixaId.value ? Number(caixaId.value) : null,
        nome: caixaNome.value.trim(),
        aberto: caixaAberto.value === "true"
    };

    const resposta = await enviarJSON(`${API}/caixas.php`, dados);

    if (resposta.ok) {
        mostrarMensagem(resposta.mensagem);
        limparFormulario();
        formCaixa.classList.add("escondido");
        carregarPainel();
    } else {
        mostrarMensagem(resposta.mensagem || "Não foi possível salvar.");
    }
});

document.getElementById("btnFecharDia").addEventListener("click", async () => {
    const confirmou = confirm(
        "Deseja gerar o relatório final do dia? O arquivo será salvo no servidor."
    );

    if (!confirmou) return;

    const resposta = await enviarJSON(`${API}/relatorio.php`, {
        acao: "fechar"
    });

    mostrarMensagem(resposta.mensagem || "Relatório processado.");
    carregarPainel();
});

// Busca os dados principais em uma única chamada.
async function carregarPainel() {
    try {
        const resposta = await fetch(`${API}/dados.php`, {
            cache: "no-store"
        });

        const dados = await resposta.json();

        caixas = dados.caixas || [];

        montarCaixas();
        montarResumo();
        montarRelatorio(dados.relatorioHoje);

        document.getElementById("ultimaAtualizacao").textContent =
            `Atualizado às ${new Date().toLocaleTimeString("pt-BR")}`;
    } catch (erro) {
        mostrarMensagem("Não foi possível carregar os dados.");
    }
}

function montarCaixas() {
    listaCaixas.innerHTML = "";

    if (caixas.length === 0) {
        listaCaixas.innerHTML = `
            <tr>
                <td colspan="6">Nenhum caixa cadastrado.</td>
            </tr>
        `;
        return;
    }

    caixas.forEach((caixa) => {
        const linha = document.createElement("tr");

        linha.innerHTML = `
            <td>${escaparHTML(caixa.nome)}</td>
            <td>
                <span class="status ${caixa.aberto ? "aberto" : "fechado"}">
                    ${caixa.aberto ? "Aberto" : "Fechado"}
                </span>
            </td>
            <td>${caixa.pessoas ?? 0}</td>
            <td>${caixa.produtos ?? 0}</td>
            <td>${caixa.tempoEstimado ?? 0} min</td>
            <td>
                <button class="acao-link" onclick="editarCaixa(${caixa.id})">Editar</button>
                <button class="acao-link" onclick="alternarCaixa(${caixa.id})">
                    ${caixa.aberto ? "Fechar" : "Abrir"}
                </button>
                <button class="acao-link" onclick="excluirCaixa(${caixa.id})">Excluir</button>
            </td>
        `;

        listaCaixas.appendChild(linha);
    });
}

function montarResumo() {
    const abertos = caixas.filter((caixa) => caixa.aberto);
    const pessoas = abertos.reduce((total, caixa) => total + Number(caixa.pessoas || 0), 0);

    const tempos = abertos
        .map((caixa) => Number(caixa.tempoEstimado || 0))
        .filter((tempo) => tempo > 0);

    const menor = tempos.length ? Math.min(...tempos) : null;

    document.getElementById("totalCaixas").textContent = caixas.length;
    document.getElementById("caixasAbertos").textContent = abertos.length;
    document.getElementById("totalPessoas").textContent = pessoas;
    document.getElementById("menorEspera").textContent =
        menor !== null ? `${menor} min` : "--";
}

function montarRelatorio(relatorio) {
    if (!relatorio || !relatorio.totalLeituras) {
        relatorioHoje.innerHTML = "<p>Nenhum dado registrado hoje.</p>";
        return;
    }

    relatorioHoje.innerHTML = `
        <div class="item-relatorio">
            <span>Leituras recebidas</span>
            <strong>${relatorio.totalLeituras}</strong>
        </div>
        <div class="item-relatorio">
            <span>Média de pessoas por leitura</span>
            <strong>${relatorio.mediaPessoas}</strong>
        </div>
        <div class="item-relatorio">
            <span>Média de produtos por leitura</span>
            <strong>${relatorio.mediaProdutos}</strong>
        </div>
        <div class="item-relatorio">
            <span>Espera média</span>
            <strong>${relatorio.mediaTempo} min</strong>
        </div>
        <div class="item-relatorio">
            <span>Maior fila registrada</span>
            <strong>${relatorio.maiorFila} pessoas</strong>
        </div>
        <div class="item-relatorio">
            <span>Maior espera registrada</span>
            <strong>${relatorio.maiorTempo} min</strong>
        </div>
    `;
}

function editarCaixa(id) {
    const caixa = caixas.find((item) => item.id === id);

    if (!caixa) return;

    caixaId.value = caixa.id;
    caixaNome.value = caixa.nome;
    caixaAberto.value = String(caixa.aberto);

    formCaixa.classList.remove("escondido");
    caixaNome.focus();
}

async function alternarCaixa(id) {
    const resposta = await enviarJSON(`${API}/caixas.php`, {
        acao: "alternar",
        id
    });

    mostrarMensagem(resposta.mensagem);
    carregarPainel();
}

async function excluirCaixa(id) {
    const confirmou = confirm("Deseja excluir este caixa?");

    if (!confirmou) return;

    const resposta = await enviarJSON(`${API}/caixas.php`, {
        acao: "excluir",
        id
    });

    mostrarMensagem(resposta.mensagem);
    carregarPainel();
}

function limparFormulario() {
    caixaId.value = "";
    caixaNome.value = "";
    caixaAberto.value = "true";
}

async function enviarJSON(url, dados) {
    try {
        const resposta = await fetch(url, {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(dados)
        });

        return await resposta.json();
    } catch (erro) {
        return {
            ok: false,
            mensagem: "Erro de comunicação com o servidor."
        };
    }
}

function mostrarMensagem(texto) {
    const mensagem = document.getElementById("mensagem");

    mensagem.textContent = texto;
    mensagem.classList.remove("escondido");

    clearTimeout(window.timerMensagem);

    window.timerMensagem = setTimeout(() => {
        mensagem.classList.add("escondido");
    }, 3000);
}

// Evita que nomes cadastrados sejam interpretados como HTML.
function escaparHTML(texto) {
    const div = document.createElement("div");
    div.textContent = texto;
    return div.innerHTML;
}

// Atualiza os dados sem precisar recarregar a página.
carregarPainel();
setInterval(carregarPainel, 10000);
