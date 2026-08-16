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


// Calcula o tempo estimado usando pessoas e produtos.
// Os valores são simples para facilitar os testes e podem ser ajustados depois.
function calcularTempoEstimado($pessoas, $produtos)
{
    $pessoas = max(0, (int)$pessoas);
    $produtos = max(0, (int)$produtos);

    if ($pessoas === 0 && $produtos === 0) {
        return 0;
    }

    // Cada pessoa acrescenta aproximadamente 36 segundos.
    $tempoPorPessoa = 0.6;

    // Cada produto acrescenta aproximadamente 4,8 segundos.
    $tempoPorProduto = 0.08;

    $tempo = ($pessoas * $tempoPorPessoa) + ($produtos * $tempoPorProduto);

    // Arredonda para cima para trabalhar com minutos inteiros.
    return (int)ceil($tempo);
}

// Monta os números usados no relatório.
// Pessoas e produtos são mostrados como total.
// O tempo de espera continua sendo uma média das leituras.
function resumirAcumulado($dados)
{
    $leituras = (int)($dados['totalLeituras'] ?? 0);

    if ($leituras === 0) {
        return [
            'totalLeituras' => 0,
            'totalPessoas' => 0,
            'totalProdutos' => 0,
            'mediaEspera' => 0,
            'maiorFila' => 0,
            'maiorProdutos' => 0,
            'maiorEspera' => 0
        ];
    }

    return [
        'totalLeituras' => $leituras,
        'totalPessoas' => (int)($dados['somaPessoas'] ?? 0),
        'totalProdutos' => (int)($dados['somaProdutos'] ?? 0),
        'mediaEspera' => round(($dados['somaTempo'] ?? 0) / $leituras, 1),
        'maiorFila' => (int)($dados['maiorFila'] ?? 0),
        'maiorProdutos' => (int)($dados['maiorProdutos'] ?? 0),
        'maiorEspera' => (int)($dados['maiorTempo'] ?? 0)
    ];
}

// Monta o relatório geral e os resultados separados por caixa.
function resumirDia($dia)
{
    $relatorio = [
        'data' => $dia['data'] ?? date('Y-m-d'),
        'geral' => resumirAcumulado($dia),
        'caixas' => []
    ];

    foreach (($dia['caixas'] ?? []) as $caixa) {
        $resultado = resumirAcumulado($caixa);

        $resultado['caixaId'] = (int)($caixa['caixaId'] ?? 0);
        $resultado['nome'] = $caixa['nome'] ?? 'Caixa';
        $resultado['ultimaLeitura'] = $caixa['ultimaLeitura'] ?? null;

        $relatorio['caixas'][] = $resultado;
    }

    return $relatorio;
}
