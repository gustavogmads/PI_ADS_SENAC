<?php

require_once __DIR__ . '/_utils.php';

$caixas = lerJson(arquivoCaixas(), []);
$dia = lerJson(arquivoDia(), []);

responder([
    'ok' => true,
    'caixas' => $caixas,
    'relatorioHoje' => resumirDia($dia)
]);
