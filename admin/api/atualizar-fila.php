<?php

require_once __DIR__ . '/_utils.php';

$entrada = lerEntrada();

$id = (int)($entrada['caixaId'] ?? 0);
$pessoas = max(0, (int)($entrada['pessoas'] ?? 0));
$produtos = max(0, (int)($entrada['produtos'] ?? 0));

// Nesta primeira versão, o sensor também pode informar o tempo.
// Depois podemos trocar isso por uma fórmula calculada pelo sistema.
$tempo = max(0, (int)($entrada['tempoEstimado'] ?? 0));

$caixas = lerJson(arquivoCaixas(), []);
$encontrado = false;

foreach ($caixas as &$caixa) {
    if ((int)$caixa['id'] === $id) {
        $caixa['pessoas'] = $pessoas;
        $caixa['produtos'] = $produtos;
        $caixa['tempoEstimado'] = $tempo;
        $caixa['atualizadoEm'] = date('c');
        $encontrado = true;
        break;
    }
}
unset($caixa);

if (!$encontrado) {
    responder(['ok' => false, 'mensagem' => 'Caixa não encontrado.'], 404);
}

salvarJson(arquivoCaixas(), $caixas);

// O arquivo do dia guarda apenas somas e máximos.
// Isso evita armazenar milhares de registros de sensores.
$dia = lerJson(arquivoDia(), [
    'data' => date('Y-m-d'),
    'totalLeituras' => 0,
    'somaPessoas' => 0,
    'somaProdutos' => 0,
    'somaTempo' => 0,
    'maiorFila' => 0,
    'maiorTempo' => 0
]);

$dia['totalLeituras']++;
$dia['somaPessoas'] += $pessoas;
$dia['somaProdutos'] += $produtos;
$dia['somaTempo'] += $tempo;
$dia['maiorFila'] = max((int)$dia['maiorFila'], $pessoas);
$dia['maiorTempo'] = max((int)$dia['maiorTempo'], $tempo);
$dia['ultimaLeitura'] = date('c');

salvarJson(arquivoDia(), $dia);

responder([
    'ok' => true,
    'mensagem' => 'Fila atualizada.',
    'caixaId' => $id
]);
