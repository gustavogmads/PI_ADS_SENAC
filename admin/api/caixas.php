<?php

require_once __DIR__ . '/_utils.php';

$entrada = lerEntrada();
$acao = $entrada['acao'] ?? '';
$caixas = lerJson(arquivoCaixas(), []);

if ($acao === 'cadastrar') {
    $nome = trim($entrada['nome'] ?? '');

    if ($nome === '') {
        responder(['ok' => false, 'mensagem' => 'Informe o nome do caixa.'], 400);
    }

    // O ID é simples porque o projeto não usa banco de dados.
    $ids = array_column($caixas, 'id');
    $novoId = empty($ids) ? 1 : max($ids) + 1;

    $caixas[] = [
        'id' => $novoId,
        'nome' => $nome,
        'aberto' => (bool)($entrada['aberto'] ?? true),
        'pessoas' => 0,
        'produtos' => 0,
        'tempoEstimado' => 0,
        'atualizadoEm' => null
    ];

    salvarJson(arquivoCaixas(), $caixas);

    responder(['ok' => true, 'mensagem' => 'Caixa cadastrado.']);
}

if ($acao === 'editar') {
    $id = (int)($entrada['id'] ?? 0);
    $nome = trim($entrada['nome'] ?? '');

    foreach ($caixas as &$caixa) {
        if ((int)$caixa['id'] === $id) {
            $caixa['nome'] = $nome;
            $caixa['aberto'] = (bool)($entrada['aberto'] ?? false);
            break;
        }
    }
    unset($caixa);

    salvarJson(arquivoCaixas(), $caixas);

    responder(['ok' => true, 'mensagem' => 'Caixa atualizado.']);
}

if ($acao === 'alternar') {
    $id = (int)($entrada['id'] ?? 0);

    foreach ($caixas as &$caixa) {
        if ((int)$caixa['id'] === $id) {
            $caixa['aberto'] = !$caixa['aberto'];

            // Ao fechar, zeramos a fila atual.
            if (!$caixa['aberto']) {
                $caixa['pessoas'] = 0;
                $caixa['produtos'] = 0;
                $caixa['tempoEstimado'] = 0;
            }

            break;
        }
    }
    unset($caixa);

    salvarJson(arquivoCaixas(), $caixas);

    responder(['ok' => true, 'mensagem' => 'Situação do caixa atualizada.']);
}

if ($acao === 'excluir') {
    $id = (int)($entrada['id'] ?? 0);

    $caixas = array_values(array_filter($caixas, function ($caixa) use ($id) {
        return (int)$caixa['id'] !== $id;
    }));

    salvarJson(arquivoCaixas(), $caixas);

    responder(['ok' => true, 'mensagem' => 'Caixa excluído.']);
}

responder(['ok' => false, 'mensagem' => 'Ação inválida.'], 400);
