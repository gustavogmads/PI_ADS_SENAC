const listaCaixas = document.getElementById("listaCaixas");
const areaRecomendacao = document.getElementById("recomendacaoConteudo");
const quantidadeCaixas = document.getElementById("quantidadeCaixas");
const ultimaAtualizacao = document.getElementById("ultimaAtualizacao");

// Estes limites servem apenas para a sinalização visual.
// Podem ser alterados depois sem mexer no restante da tela.
const LIMITE_VERDE = 5;
const LIMITE_AMARELO = 10;

// Busca os dados do mesmo backend utilizado pelo painel administrativo.
async function carregarFilas() {
    try {
        const resposta = await fetch("../api/dados.php", {
            cache: "no-store"
        });

        if (!resposta.ok) {
            throw new Error("Erro ao buscar os dados.");
        }

        const dados = await resposta.json();
        const caixas = Array.isArray(dados.caixas) ? dados.caixas : [];

        // O cliente só precisa ver caixas que estão abertos.
        const abertos = caixas.filter((caixa) => caixa.aberto);

        montarLista(abertos);
        montarRecomendacao(abertos);

        ultimaAtualizacao.textContent =
            `Atualizado às ${new Date().toLocaleTimeString("pt-BR", {
                hour: "2-digit",
                minute: "2-digit"
            })}`;
    } catch (erro) {
        listaCaixas.innerHTML =
            '<p class="mensagem-lista erro">Não foi possível carregar as filas.</p>';

        areaRecomendacao.innerHTML =
            '<p class="erro">Não foi possível consultar os caixas agora.</p>';

        quantidadeCaixas.textContent = "0";
        ultimaAtualizacao.textContent = "Sem conexão com os dados";
    }
}

function montarLista(caixas) {
    quantidadeCaixas.textContent = caixas.length;
    listaCaixas.innerHTML = "";

    if (caixas.length === 0) {
        listaCaixas.innerHTML =
            '<p class="mensagem-lista">Nenhum caixa aberto no momento.</p>';
        return;
    }

    // Mostra primeiro os caixas com menor tempo conhecido.
    caixas.sort((a, b) => {
        const tempoA = Number(a.tempoEstimado || 0);
        const tempoB = Number(b.tempoEstimado || 0);

        if (tempoA === 0 && tempoB > 0) return 1;
        if (tempoB === 0 && tempoA > 0) return -1;

        return tempoA - tempoB;
    });

    caixas.forEach((caixa) => {
        const tempo = Number(caixa.tempoEstimado || 0);
        const classe = classeTempo(tempo);

        const card = document.createElement("article");
        card.className = "caixa";

        card.innerHTML = `
            <div class="caixa-topo">
                <h3>
                    <span class="indicador ${classe}"></span>
                    ${escaparHTML(caixa.nome)}
                </h3>

                <span class="tempo">
                    ${tempo > 0 ? `${tempo} min` : "Aguardando"}
                </span>
            </div>

            <div class="caixa-detalhes">
                <span>${Number(caixa.pessoas || 0)} pessoas</span>
                <span>${Number(caixa.produtos || 0)} produtos</span>
            </div>
        `;

        listaCaixas.appendChild(card);
    });
}

function montarRecomendacao(caixas) {
    // Para recomendar, precisamos de pelo menos um caixa com tempo válido.
    const validos = caixas.filter((caixa) => Number(caixa.tempoEstimado || 0) > 0);

    if (validos.length === 0) {
        areaRecomendacao.innerHTML = `
            <h2>Aguardando dados</h2>
            <p>Ainda não há uma estimativa disponível para os caixas abertos.</p>
        `;
        return;
    }

    // Primeiro considera o tempo.
    // Em caso de empate, usa menos produtos e depois menos pessoas.
    validos.sort((a, b) => {
        const diferencaTempo =
            Number(a.tempoEstimado) - Number(b.tempoEstimado);

        if (diferencaTempo !== 0) {
            return diferencaTempo;
        }

        const diferencaProdutos =
            Number(a.produtos || 0) - Number(b.produtos || 0);

        if (diferencaProdutos !== 0) {
            return diferencaProdutos;
        }

        return Number(a.pessoas || 0) - Number(b.pessoas || 0);
    });

    const melhor = validos[0];

    areaRecomendacao.innerHTML = `
        <h2>${escaparHTML(melhor.nome)}</h2>

        <span class="tempo-principal">
            ${Number(melhor.tempoEstimado)} min
        </span>

        <p>Este é o caixa com a menor espera estimada neste momento.</p>

        <div class="detalhes-principal">
            <span>${Number(melhor.pessoas || 0)} pessoas na fila</span>
            <span>${Number(melhor.produtos || 0)} produtos estimados</span>
        </div>
    `;
}

function classeTempo(tempo) {
    if (!tempo) {
        return "neutro";
    }

    if (tempo <= LIMITE_VERDE) {
        return "verde";
    }

    if (tempo <= LIMITE_AMARELO) {
        return "amarelo";
    }

    return "vermelho";
}

// Evita interpretar nomes cadastrados como código HTML.
function escaparHTML(texto) {
    const div = document.createElement("div");
    div.textContent = texto;
    return div.innerHTML;
}

// Faz a primeira leitura e depois consulta novamente a cada 5 segundos.
carregarFilas();
setInterval(carregarFilas, 5000);
