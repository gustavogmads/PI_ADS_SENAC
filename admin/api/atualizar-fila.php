<?php

require_once __DIR__ . '/_utils.php';

$entrada = lerEntrada();

$id = (int)($entrada['caixaId'] ?? 0);
$pessoas = max(0, (int)($entrada['pessoas'] ?? 0));
$produtos = max(0, (int)($entrada['produtos'] ?? 0));

// O sensor informa apenas pessoas e produtos.
// O próprio sistema calcula o tempo estimado.
$tempo = calcularTempoEstimado($pessoas, $produtos);

$caixas = lerJson(arquivoCaixas(), []);
$encontrado = false;
$nomeCaixa = '';

foreach ($caixas as &$caixa) {
    if ((int)$caixa['id'] === $id) {
        $caixa['pessoas'] = $pessoas;
        $caixa['produtos'] = $produtos;
        $caixa['tempoEstimado'] = $tempo;
        $caixa['atualizadoEm'] = date('c');

        $nomeCaixa = $caixa['nome'];
        $encontrado = true;
        break;
    }
}
unset($caixa);

if (!$encontrado) {
    responder(['ok' => false, 'mensagem' => 'Caixa não encontrado.'], 404);
}

salvarJson(arquivoCaixas(), $caixas);

// O arquivo do dia continua guardando apenas somas e máximos.
// Agora ele também mantém esses dados separados por caixa.
$dia = lerJson(arquivoDia(), [
    'data' => date('Y-m-d'),
    'totalLeituras' => 0,
    'somaPessoas' => 0,
    'somaProdutos' => 0,
    'somaTempo' => 0,
    'maiorFila' => 0,
    'maiorProdutos' => 0,
    'maiorTempo' => 0,
    'caixas' => []
]);

// Resultado geral do supermercado.
$dia['totalLeituras'] = (int)($dia['totalLeituras'] ?? 0) + 1;
$dia['somaPessoas'] = (int)($dia['somaPessoas'] ?? 0) + $pessoas;
$dia['somaProdutos'] = (int)($dia['somaProdutos'] ?? 0) + $produtos;
$dia['somaTempo'] = (int)($dia['somaTempo'] ?? 0) + $tempo;
$dia['maiorFila'] = max((int)($dia['maiorFila'] ?? 0), $pessoas);
$dia['maiorProdutos'] = max((int)($dia['maiorProdutos'] ?? 0), $produtos);
$dia['maiorTempo'] = max((int)($dia['maiorTempo'] ?? 0), $tempo);
$dia['ultimaLeitura'] = date('c');

if (!isset($dia['caixas']) || !is_array($dia['caixas'])) {
    $dia['caixas'] = [];
}

$chave = (string)$id;

// Cria o acumulador do caixa quando ele recebe a primeira leitura do dia.
if (!isset($dia['caixas'][$chave])) {
    $dia['caixas'][$chave] = [
        'caixaId' => $id,
        'nome' => $nomeCaixa,
        'totalLeituras' => 0,
        'somaPessoas' => 0,
        'somaProdutos' => 0,
        'somaTempo' => 0,
        'maiorFila' => 0,
        'maiorProdutos' => 0,
        'maiorTempo' => 0,
        'ultimaLeitura' => null
    ];
}

$registro = &$dia['caixas'][$chave];

// Mantém o nome atualizado caso o caixa seja renomeado.
$registro['nome'] = $nomeCaixa;
$registro['totalLeituras']++;
$registro['somaPessoas'] += $pessoas;
$registro['somaProdutos'] += $produtos;
$registro['somaTempo'] += $tempo;
$registro['maiorFila'] = max((int)$registro['maiorFila'], $pessoas);
$registro['maiorProdutos'] = max((int)$registro['maiorProdutos'], $produtos);
$registro['maiorTempo'] = max((int)$registro['maiorTempo'], $tempo);
$registro['ultimaLeitura'] = date('c');

unset($registro);

salvarJson(arquivoDia(), $dia);

responder([
    'ok' => true,
    'mensagem' => 'Fila atualizada.',
    'caixaId' => $id,
    'pessoas' => $pessoas,
    'produtos' => $produtos,
    'tempoEstimado' => $tempo
]);
