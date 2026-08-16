# Teste rápido sem sensor

Depois de subir o projeto em um servidor PHP, você pode testar uma leitura usando `curl`.

Exemplo:

```bash
curl -X POST http://localhost/filacerta-admin/api/atualizar-fila.php \
  -H "Content-Type: application/json" \
  -d "{\"caixaId\":1,\"pessoas\":3,\"produtos\":22,\"tempoEstimado\":4}"
```

Primeiro cadastre o Caixa 01 pelo painel.

Depois envie o comando acima. O painel deverá mostrar os novos valores na próxima atualização.
