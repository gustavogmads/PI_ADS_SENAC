const listaCaixas = document.getElementById("listaCaixas");
const areaRecomendacao = document.getElementById("recomendacaoConteudo");
const quantidadeCaixas = document.getElementById("quantidadeCaixas");
const ultimaAtualizacao = document.getElementById("ultimaAtualizacao");

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

// Ordena os caixas do menor para o maior tempo.
// Em caso de empate, usa produtos e pessoas como desempate.
function ordenarCaixas(caixas) {
    return [...caixas].sort((a, b) => {
        const tempoA = Number(a.tempoEstimado || 0);
        const tempoB = Number(b.tempoEstimado || 0);

        if (tempoA === 0 && tempoB > 0) return 1;
        if (tempoB === 0 && tempoA > 0) return -1;

        if (tempoA !== tempoB) {
            return tempoA - tempoB;
        }

        const produtosA = Number(a.produtos || 0);
        const produtosB = Number(b.produtos || 0);

        if (produtosA !== produtosB) {
            return produtosA - produtosB;
        }

        return Number(a.pessoas || 0) - Number(b.pessoas || 0);
    });
}

function montarLista(caixas) {
    quantidadeCaixas.textContent = caixas.length;
    listaCaixas.innerHTML = "";

    if (caixas.length === 0) {
        listaCaixas.innerHTML =
            '<p class="mensagem-lista">Nenhum caixa aberto no momento.</p>';
        return;
    }

    const ordenados = ordenarCaixas(caixas);
    const comTempo = ordenados.filter((caixa) => Number(caixa.tempoEstimado || 0) > 0);

    ordenados.forEach((caixa) => {
        const tempo = Number(caixa.tempoEstimado || 0);
        const posicao = comTempo.findIndex((item) => item.id === caixa.id);
        const situacao = definirSituacao(posicao, comTempo.length, tempo);

        const card = document.createElement("article");
        card.className = `caixa caixa-${situacao.classe}`;

        card.innerHTML = `
            <div class="caixa-topo">
                <div>
                    <h3>${escaparHTML(caixa.nome)}</h3>
                    <span class="etiqueta ${situacao.classe}">
                        ${situacao.texto}
                    </span>
                </div>

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
    const validos = caixas.filter((caixa) => Number(caixa.tempoEstimado || 0) > 0);

    if (validos.length === 0) {
        areaRecomendacao.innerHTML = `
            <h2>Aguardando dados</h2>
            <p>Ainda não há uma estimativa disponível para os caixas abertos.</p>
        `;
        return;
    }

    const melhor = ordenarCaixas(validos)[0];

    areaRecomendacao.innerHTML = `
        <span class="etiqueta verde">Melhor opção</span>

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

// A cor agora mostra a posição do caixa em relação aos outros.
// Primeiro = verde, último = vermelho e os demais = amarelo.
function definirSituacao(posicao, quantidade, tempo) {
    if (!tempo || posicao === -1) {
        return {
            classe: "neutro",
            texto: "Aguardando dados"
        };
    }

    if (quantidade === 1 || posicao === 0) {
        return {
            classe: "verde",
            texto: "Melhor opção"
        };
    }

    if (posicao === quantidade - 1) {
        return {
            classe: "vermelho",
            texto: "Maior espera"
        };
    }

    return {
        classe: "amarelo",
        texto: "Espera intermediária"
    };
}

// Evita interpretar nomes cadastrados como código HTML.
function escaparHTML(texto) {
    const div = document.createElement("div");
    div.textContent = texto;
    return div.innerHTML;
}

carregarFilas();
setInterval(carregarFilas, 5000);
