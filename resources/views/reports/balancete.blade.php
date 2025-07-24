<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Balancete</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: right; }
        th { background: #f1f5f9; }
    </style>
</head>
<body>
<h1>Balancete</h1>
<p>Período: {{ $periodo[0] }} a {{ $periodo[1] }}</p>
<table>
    <tr>
        <th>Conta</th>
        <th>Total</th>
    </tr>
    @foreach($accounts as $account)
        <tr>
            <td style="text-align:left">{{ $account->name }}</td>
            <td>{{ number_format($account->transactions->sum('amount'), 2, ',', '.') }}</td>
        </tr>
    @endforeach
</table>
</body>
</html>
