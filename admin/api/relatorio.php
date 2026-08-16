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

// Guardamos também quando o relatório foi fechado.
$relatorio['fechadoEm'] = date('c');

if (!salvarJson(arquivoRelatorio(), $relatorio)) {
    responder([
        'ok' => false,
        'mensagem' => 'Não foi possível salvar o relatório.'
    ], 500);
}

responder([
    'ok' => true,
    'mensagem' => 'Relatório do dia gerado.',
    'relatorio' => $relatorio
]);
