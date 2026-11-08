<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>DRE</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: right; }
        th { background: #f1f5f9; }
    </style>
</head>
<body>
<h1>Demonstração do Resultado</h1>
<p>Período: {{ $periodo[0] }} a {{ $periodo[1] }}</p>
<table>
    <tr><th>Receitas</th><td>{{ number_format($receitas, 2, ',', '.') }}</td></tr>
    <tr><th>Despesas</th><td>{{ number_format($despesas, 2, ',', '.') }}</td></tr>
    <tr><th>Resultado</th><td>{{ number_format($resultado, 2, ',', '.') }}</td></tr>
</table>
</body>
</html>
