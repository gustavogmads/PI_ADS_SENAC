<?php

require_once __DIR__ . '/_utils.php';

$entrada = lerEntrada();
$acao = $entrada['acao'] ?? '';

if ($acao !== 'fechar') {
    responder(['ok' => false, 'mensagem' => 'Ação inválida.'], 400);
}

$dia = lerJson(arquivoDia(), []);

if (empty($dia) || empty($dia['totalLeituras'])) {
    responder([
        'ok' => false,
        'mensagem' => 'Ainda não existem leituras para gerar o relatório.'
    ], 400);
}

$relatorio = resumirDia($dia);

// A data de fechamento fica no início para facilitar a leitura do arquivo.
$relatorioFinal = [
    'data' => $relatorio['data'],
    'fechadoEm' => date('c'),
    'geral' => $relatorio['geral'],
    'caixas' => $relatorio['caixas']
];

if (!salvarJson(arquivoRelatorio(), $relatorioFinal)) {
    responder([
        'ok' => false,
        'mensagem' => 'Não foi possível salvar o relatório.'
    ], 500);
}

responder([
    'ok' => true,
    'mensagem' => 'Relatório do dia gerado.',
    'relatorio' => $relatorioFinal
]);
