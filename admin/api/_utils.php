<?php

// Retorna uma resposta JSON e encerra o script.
function responder($dados, $status = 200)
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Caminho da pasta onde os JSON ficam salvos.
function pastaDados()
{
    return dirname(__DIR__) . '/data';
}

// Lê um arquivo JSON. Se ele ainda não existir, devolve o valor padrão.
function lerJson($arquivo, $padrao = [])
{
    if (!file_exists($arquivo)) {
        return $padrao;
    }

    $conteudo = file_get_contents($arquivo);
    $dados = json_decode($conteudo, true);

    return is_array($dados) ? $dados : $padrao;
}

// Salva o JSON usando bloqueio de arquivo para evitar duas escritas ao mesmo tempo.
function salvarJson($arquivo, $dados)
{
    $pasta = dirname($arquivo);

    if (!is_dir($pasta)) {
        mkdir($pasta, 0775, true);
    }

    $json = json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

    $handle = fopen($arquivo, 'c+');

    if (!$handle) {
        return false;
    }

    flock($handle, LOCK_EX);
    ftruncate($handle, 0);
    rewind($handle);
    fwrite($handle, $json);
    fflush($handle);
    flock($handle, LOCK_UN);
    fclose($handle);

    return true;
}

// Lê o corpo JSON enviado por JavaScript ou por um sensor.
function lerEntrada()
{
    $conteudo = file_get_contents('php://input');
    $dados = json_decode($conteudo, true);

    return is_array($dados) ? $dados : [];
}

// Arquivo com os caixas atuais.
function arquivoCaixas()
{
    return pastaDados() . '/caixas.json';
}

// O relatório em andamento usa um arquivo separado para cada dia.
function arquivoDia($data = null)
{
    $data = $data ?: date('Y-m-d');
    return pastaDados() . '/dias/' . $data . '.json';
}

// O relatório fechado também fica separado por dia.
function arquivoRelatorio($data = null)
{
    $data = $data ?: date('Y-m-d');
    return pastaDados() . '/relatorios/' . $data . '.json';
}

// Calcula as médias sem precisar guardar todas as leituras do dia.
function resumirDia($dia)
{
    $leituras = (int)($dia['totalLeituras'] ?? 0);

    if ($leituras === 0) {
        return [
            'data' => $dia['data'] ?? date('Y-m-d'),
            'totalLeituras' => 0,
            'mediaPessoas' => 0,
            'mediaProdutos' => 0,
            'mediaTempo' => 0,
            'maiorFila' => 0,
            'maiorTempo' => 0
        ];
    }

    return [
        'data' => $dia['data'] ?? date('Y-m-d'),
        'totalLeituras' => $leituras,
        'mediaPessoas' => round(($dia['somaPessoas'] ?? 0) / $leituras, 1),
        'mediaProdutos' => round(($dia['somaProdutos'] ?? 0) / $leituras, 1),
        'mediaTempo' => round(($dia['somaTempo'] ?? 0) / $leituras, 1),
        'maiorFila' => (int)($dia['maiorFila'] ?? 0),
        'maiorTempo' => (int)($dia['maiorTempo'] ?? 0)
    ];
}
