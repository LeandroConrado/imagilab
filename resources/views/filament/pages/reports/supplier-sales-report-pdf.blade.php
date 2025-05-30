<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Vendas por Fornecedor</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 14px; }
        h1 { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f5f5f5; }
    </style>
</head>
<body>
<h1>Relatório de Vendas por Fornecedor</h1>
<table>
    <thead>
    <tr>
        <th>Fornecedor</th>
        <th>Total de Pedidos</th>
        <th>Itens Vendidos</th>
        <th>Valor Total</th>
    </tr>
    </thead>
    <tbody>
    @foreach($records as $linha)
        <tr>
            <td>{{ $linha->fornecedor }}</td>
            <td>{{ $linha->total_pedidos }}</td>
            <td>{{ $linha->total_itens }}</td>
            <td>R$ {{ number_format($linha->total_vendido, 2, ',', '.') }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
