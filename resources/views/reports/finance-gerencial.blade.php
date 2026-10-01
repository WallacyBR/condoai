<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>{{ $report['title'] }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #172033; font-size: 11px; }
        h1 { margin: 0; color: #0e7490; font-size: 20px; }
        .meta, .notice { margin: 12px 0; color: #475569; }
        .notice { border-left: 3px solid #f59e0b; background: #fffbeb; padding: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 14px; }
        th, td { border-bottom: 1px solid #cbd5e1; padding: 8px; text-align: left; }
        th { background: #e0f2fe; }
        .amount { text-align: right; }
        .total { font-weight: bold; background: #f1f5f9; }
    </style>
</head>
<body>
    <h1>{{ $report['title'] }}</h1>
    <p class="meta"><strong>Condomínio:</strong> {{ $condominiumName }}<br>
        @if(isset($report['start_date'])) <strong>Período:</strong> {{ $report['start_date'] }} a {{ $report['end_date'] }} @else <strong>Data-base:</strong> {{ $report['as_of_date'] }} @endif<br>
        <strong>Emitido em:</strong> {{ now()->format('d/m/Y H:i') }}</p>
    <p class="notice">{{ $report['disclaimer'] }}</p>

    @if(($report['kind'] ?? '') === 'gerencial_competencia')
        <table>
            <thead><tr><th>Grupo / Conta</th><th class="amount">Valor</th></tr></thead>
            <tbody>
            <tr class="total"><td>Receitas</td><td class="amount">R$ {{ number_format($report['totals']['revenues'], 2, ',', '.') }}</td></tr>
            @foreach($report['revenues'] as $group)
                <tr><td><strong>{{ $group['group'] }}</strong></td><td class="amount">R$ {{ number_format($group['amount'], 2, ',', '.') }}</td></tr>
                @foreach($group['items'] as $item)<tr><td>&nbsp;&nbsp;{{ $item['account'] }}</td><td class="amount">R$ {{ number_format($item['amount'], 2, ',', '.') }}</td></tr>@endforeach
            @endforeach
            <tr class="total"><td>Despesas</td><td class="amount">R$ {{ number_format($report['totals']['expenses'], 2, ',', '.') }}</td></tr>
            @foreach($report['expenses'] as $group)
                <tr><td><strong>{{ $group['group'] }}</strong></td><td class="amount">R$ {{ number_format($group['amount'], 2, ',', '.') }}</td></tr>
                @foreach($group['items'] as $item)<tr><td>&nbsp;&nbsp;{{ $item['account'] }}</td><td class="amount">R$ {{ number_format($item['amount'], 2, ',', '.') }}</td></tr>@endforeach
            @endforeach
            <tr class="total"><td>Resultado gerencial</td><td class="amount">R$ {{ number_format($report['totals']['result'], 2, ',', '.') }}</td></tr>
            </tbody>
        </table>
    @else
        <table>
            <thead><tr><th>Indicador</th><th class="amount">Valor</th></tr></thead>
            <tbody>
                <tr><td>Saldo bancário informado</td><td class="amount">R$ {{ number_format($report['totals']['reported_bank_balance'], 2, ',', '.') }}</td></tr>
                <tr><td>Entradas realizadas</td><td class="amount">R$ {{ number_format($report['totals']['received'], 2, ',', '.') }}</td></tr>
                <tr><td>Saídas realizadas</td><td class="amount">R$ {{ number_format($report['totals']['paid'], 2, ',', '.') }}</td></tr>
                <tr><td>Receitas em aberto</td><td class="amount">R$ {{ number_format($report['totals']['receivables_open'], 2, ',', '.') }}</td></tr>
                <tr><td>Despesas em aberto</td><td class="amount">R$ {{ number_format($report['totals']['payables_open'], 2, ',', '.') }}</td></tr>
                <tr class="total"><td>Saldo projetado</td><td class="amount">R$ {{ number_format($report['totals']['projected_balance'], 2, ',', '.') }}</td></tr>
            </tbody>
        </table>
        <table><thead><tr><th>Banco</th><th>Conta</th><th class="amount">Saldo informado</th></tr></thead><tbody>
        @foreach($report['banks'] as $bank)<tr><td>{{ $bank['name'] }}</td><td>{{ $bank['account'] }}</td><td class="amount">R$ {{ number_format($bank['balance'], 2, ',', '.') }}</td></tr>@endforeach
        </tbody></table>
    @endif
</body>
</html>
