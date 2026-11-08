<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AccessRequest;
use App\Models\Apartment;
use App\Models\CommonAreaBooking;
use App\Models\MaintenanceRecord;
use App\Models\Occurrence;
use App\Models\Package;
use App\Models\ParkingSpot;
use App\Models\Resident;
use App\Models\Vehicle;
use BackedEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rule;

class OperationalReportController extends Controller
{
    private const TYPES = [
        'overview',
        'residents',
        'packages',
        'occurrences',
        'parking',
        'bookings',
        'maintenance',
        'access',
    ];

    /**
     * Exibe um relatório operacional.
     *
     * Retorna indicadores, colunas e linhas do relatório operacional do condomínio atual, inferido pelo tenant/usuário autenticado.
     *
     * @queryParam type string Tipo do relatório. Valores aceitos: overview, residents, packages, occurrences, parking, bookings, maintenance, access. Example: overview
     * @queryParam start_date date Data inicial do período no formato YYYY-MM-DD. Example: 2026-05-26
     * @queryParam end_date date Data final do período no formato YYYY-MM-DD. Example: 2026-06-25
     */
    public function show(Request $request)
    {
        $context = $this->validatedContext($request);

        return response()->json($this->buildReport($context));
    }

    /**
     * Exporta um relatório operacional em CSV.
     *
     * Reutiliza os mesmos filtros do relatório em tela e entrega um arquivo CSV do condomínio atual, inferido pelo tenant/usuário autenticado.
     *
     * @queryParam type string Tipo do relatório. Valores aceitos: overview, residents, packages, occurrences, parking, bookings, maintenance, access. Example: packages
     * @queryParam start_date date Data inicial do período no formato YYYY-MM-DD. Example: 2026-05-26
     * @queryParam end_date date Data final do período no formato YYYY-MM-DD. Example: 2026-06-25
     */
    public function export(Request $request)
    {
        $context = $this->validatedContext($request);
        $report = $this->buildReport($context);
        $filename = sprintf(
            'relatorio-%s-%s-%s.csv',
            $context['type'],
            $context['start']->toDateString(),
            $context['end']->toDateString()
        );

        return Response::streamDownload(function () use ($report) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, collect($report['columns'])->pluck('label')->all(), ';');

            foreach ($report['rows'] as $row) {
                fputcsv($handle, collect($report['columns'])
                    ->map(fn (array $column) => $row[$column['key']] ?? '')
                    ->all(), ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function validatedContext(Request $request): array
    {
        $validated = $request->validate([
            'type' => ['sometimes', 'string', Rule::in(self::TYPES)],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after_or_equal:start_date'],
        ]);

        return [
            'type' => $validated['type'] ?? 'overview',
            'start' => Carbon::parse($validated['start_date'] ?? now()->subDays(30)->toDateString())->startOfDay(),
            'end' => Carbon::parse($validated['end_date'] ?? now()->toDateString())->endOfDay(),
            'condominium_id' => $this->currentCondominiumId($request),
        ];
    }

    private function buildReport(array $context): array
    {
        return match ($context['type']) {
            'residents' => $this->residentsReport($context),
            'packages' => $this->packagesReport($context),
            'occurrences' => $this->occurrencesReport($context),
            'parking' => $this->parkingReport($context),
            'bookings' => $this->bookingsReport($context),
            'maintenance' => $this->maintenanceReport($context),
            'access' => $this->accessReport($context),
            default => $this->overviewReport($context),
        };
    }

    private function overviewReport(array $context): array
    {
        $residents = Resident::where('condominium_id', $context['condominium_id']);
        $packages = $this->between(Package::where('condominium_id', $context['condominium_id']), 'created_at', $context);
        $occurrences = $this->between(Occurrence::where('condominium_id', $context['condominium_id']), 'created_at', $context);
        $parking = ParkingSpot::where('condominium_id', $context['condominium_id']);
        $bookings = $this->betweenDates(CommonAreaBooking::where('condominium_id', $context['condominium_id']), 'booking_date', $context);
        $maintenance = $this->between(MaintenanceRecord::where('condominium_id', $context['condominium_id']), 'created_at', $context);
        $access = $this->between(AccessRequest::where('condominium_id', $context['condominium_id']), 'visit_date', $context);

        $rows = [
            $this->overviewRow('Moradores', 'Moradores ativos', $residents->clone()->where('active', true)->count(), 'Cadastro atual'),
            $this->overviewRow('Unidades', 'Unidades cadastradas', Apartment::where('condominium_id', $context['condominium_id'])->count(), 'Cadastro atual'),
            $this->overviewRow('Encomendas', 'Pendentes no período', $packages->clone()->whereNull('retrieved_at')->count(), 'Criadas no período filtrado'),
            $this->overviewRow('Ocorrências', 'Abertas ou reabertas', $occurrences->clone()->whereIn('status', ['open', 'reopened'])->count(), 'Criadas no período filtrado'),
            $this->overviewRow('Vagas', 'Vagas livres', $parking->clone()->whereNull('rented_to_apartment_id')->count(), 'Cadastro atual'),
            $this->overviewRow('Reservas', 'Reservas no período', $bookings->clone()->count(), 'Data da reserva'),
            $this->overviewRow('Manutenção', 'Pendentes ou vencidas', $maintenance->clone()->whereIn('status', ['pending', 'overdue', 'reopened'])->count(), 'Registros criados no período'),
            $this->overviewRow('Acesso', 'Visitas em aberto', $access->clone()->whereNull('actual_exit_timestamp')->count(), 'Visitas do período'),
        ];

        return $this->report('overview', 'Visão Geral Operacional', $context, [
            $this->summary('Moradores ativos', $residents->clone()->where('active', true)->count(), 'blue'),
            $this->summary('Encomendas pendentes', $packages->clone()->whereNull('retrieved_at')->count(), 'orange'),
            $this->summary('Ocorrências críticas', $occurrences->clone()->where('priority', 'urgent')->count(), 'red'),
            $this->summary('Vagas livres', $parking->clone()->whereNull('rented_to_apartment_id')->count(), 'green'),
        ], [
            ['key' => 'module', 'label' => 'Módulo'],
            ['key' => 'indicator', 'label' => 'Indicador'],
            ['key' => 'value', 'label' => 'Valor'],
            ['key' => 'notes', 'label' => 'Observação'],
        ], $rows);
    }

    private function residentsReport(array $context): array
    {
        $query = Resident::with(['apartment.block', 'apartment.tower'])
            ->where('condominium_id', $context['condominium_id']);

        $rows = $query->clone()
            ->orderBy('name')
            ->limit(500)
            ->get()
            ->map(fn (Resident $resident) => [
                'name' => $resident->name,
                'classification' => $this->enumValue($resident->classification),
                'status' => $resident->active ? 'Ativo' : 'Inativo',
                'unit' => $this->formatApartment($resident->apartment),
                'phone' => $resident->cell_phone ?: $resident->commercial_phone ?: '',
                'email' => $resident->email ?: '',
            ])->all();

        return $this->report('residents', 'Moradores e Unidades', $context, [
            $this->summary('Total de moradores', $query->clone()->count(), 'blue'),
            $this->summary('Ativos', $query->clone()->where('active', true)->count(), 'green'),
            $this->summary('Inativos', $query->clone()->where('active', false)->count(), 'orange'),
            $this->summary('Novos no período', $this->between($query->clone(), 'created_at', $context)->count(), 'cyan'),
        ], [
            ['key' => 'name', 'label' => 'Nome'],
            ['key' => 'classification', 'label' => 'Classificação'],
            ['key' => 'status', 'label' => 'Status'],
            ['key' => 'unit', 'label' => 'Unidade'],
            ['key' => 'phone', 'label' => 'Telefone'],
            ['key' => 'email', 'label' => 'Email'],
        ], $rows);
    }

    private function packagesReport(array $context): array
    {
        $query = $this->between(Package::with(['apartment.block', 'apartment.tower'])
            ->where('condominium_id', $context['condominium_id']), 'created_at', $context);

        $rows = $query->clone()
            ->latest()
            ->limit(500)
            ->get()
            ->map(fn (Package $package) => [
                'unit' => $this->formatApartment($package->apartment),
                'recipient' => $package->recipient_name,
                'status' => $package->retrieved_at ? 'Retirada' : 'Pendente',
                'received_at' => $this->formatDateTime($package->created_at),
                'retrieved_at' => $this->formatDateTime($package->retrieved_at),
                'pickup_location' => $package->pickup_location ?: '',
            ])->all();

        return $this->report('packages', 'Encomendas', $context, [
            $this->summary('Total no período', $query->clone()->count(), 'blue'),
            $this->summary('Pendentes', $query->clone()->whereNull('retrieved_at')->count(), 'orange'),
            $this->summary('Retiradas', $query->clone()->whereNotNull('retrieved_at')->count(), 'green'),
            $this->summary('Pendentes há mais de 3 dias', $query->clone()->whereNull('retrieved_at')->where('created_at', '<=', now()->subDays(3))->count(), 'red'),
        ], [
            ['key' => 'unit', 'label' => 'Unidade'],
            ['key' => 'recipient', 'label' => 'Destinatário'],
            ['key' => 'status', 'label' => 'Status'],
            ['key' => 'received_at', 'label' => 'Recebida em'],
            ['key' => 'retrieved_at', 'label' => 'Retirada em'],
            ['key' => 'pickup_location', 'label' => 'Local'],
        ], $rows);
    }

    private function occurrencesReport(array $context): array
    {
        $query = $this->between(Occurrence::with('user:id,name')
            ->where('condominium_id', $context['condominium_id']), 'created_at', $context);

        $rows = $query->clone()
            ->latest()
            ->limit(500)
            ->get()
            ->map(fn (Occurrence $occurrence) => [
                'title' => $occurrence->title,
                'status' => $this->enumValue($occurrence->status),
                'priority' => $this->enumValue($occurrence->priority),
                'created_by' => $occurrence->user?->name ?: '',
                'created_at' => $this->formatDateTime($occurrence->created_at),
            ])->all();

        return $this->report('occurrences', 'Ocorrências', $context, [
            $this->summary('Total no período', $query->clone()->count(), 'blue'),
            $this->summary('Abertas/Reabertas', $query->clone()->whereIn('status', ['open', 'reopened'])->count(), 'orange'),
            $this->summary('Urgentes', $query->clone()->where('priority', 'urgent')->count(), 'red'),
            $this->summary('Resolvidas/Fechadas', $query->clone()->whereIn('status', ['resolved', 'closed'])->count(), 'green'),
        ], [
            ['key' => 'title', 'label' => 'Título'],
            ['key' => 'status', 'label' => 'Status'],
            ['key' => 'priority', 'label' => 'Prioridade'],
            ['key' => 'created_by', 'label' => 'Criado por'],
            ['key' => 'created_at', 'label' => 'Criada em'],
        ], $rows);
    }

    private function parkingReport(array $context): array
    {
        $spots = ParkingSpot::with(['apartment.block', 'apartment.tower', 'rentedToApartment.block', 'rentedToApartment.tower'])
            ->where('condominium_id', $context['condominium_id']);
        $vehicles = Vehicle::whereHas('apartment', fn (Builder $query) => $query->where('condominium_id', $context['condominium_id']));

        $rows = $spots->clone()
            ->orderBy('identifier')
            ->limit(500)
            ->get()
            ->map(fn (ParkingSpot $spot) => [
                'identifier' => $spot->identifier,
                'owner_unit' => $this->formatApartment($spot->apartment),
                'status' => $spot->rented_to_apartment_id ? 'Alugada' : 'Livre',
                'rented_to' => $this->formatApartment($spot->rentedToApartment),
            ])->all();

        return $this->report('parking', 'Vagas e Veículos', $context, [
            $this->summary('Total de vagas', $spots->clone()->count(), 'blue'),
            $this->summary('Vagas livres', $spots->clone()->whereNull('rented_to_apartment_id')->count(), 'green'),
            $this->summary('Vagas alugadas', $spots->clone()->whereNotNull('rented_to_apartment_id')->count(), 'orange'),
            $this->summary('Veículos sem vaga', $vehicles->clone()->whereNull('parking_spot_id')->count(), 'red'),
        ], [
            ['key' => 'identifier', 'label' => 'Vaga'],
            ['key' => 'owner_unit', 'label' => 'Unidade proprietária'],
            ['key' => 'status', 'label' => 'Status'],
            ['key' => 'rented_to', 'label' => 'Alugada para'],
        ], $rows);
    }

    private function bookingsReport(array $context): array
    {
        $query = $this->betweenDates(CommonAreaBooking::with(['commonArea', 'apartment.block', 'apartment.tower', 'resident'])
            ->where('condominium_id', $context['condominium_id']), 'booking_date', $context);

        $rows = $query->clone()
            ->orderByDesc('booking_date')
            ->limit(500)
            ->get()
            ->map(fn (CommonAreaBooking $booking) => [
                'area' => $booking->commonArea?->name ?: '',
                'unit' => $this->formatApartment($booking->apartment),
                'resident' => $booking->resident?->name ?: '',
                'date' => $this->formatDate($booking->booking_date),
                'status' => $booking->status,
                'amount' => $this->formatMoney($booking->total_amount ?: $booking->total_price ?: 0),
            ])->all();

        return $this->report('bookings', 'Reservas de Áreas', $context, [
            $this->summary('Reservas no período', $query->clone()->count(), 'blue'),
            $this->summary('Confirmadas', $query->clone()->where('status', 'confirmed')->count(), 'green'),
            $this->summary('Pendentes', $query->clone()->where('status', 'pending')->count(), 'orange'),
            $this->summary('Valor previsto', $this->formatMoney((float) $query->clone()->sum('total_amount')), 'cyan'),
        ], [
            ['key' => 'area', 'label' => 'Área'],
            ['key' => 'unit', 'label' => 'Unidade'],
            ['key' => 'resident', 'label' => 'Morador'],
            ['key' => 'date', 'label' => 'Data'],
            ['key' => 'status', 'label' => 'Status'],
            ['key' => 'amount', 'label' => 'Valor'],
        ], $rows);
    }

    private function maintenanceReport(array $context): array
    {
        $query = $this->between(MaintenanceRecord::with(['schedule.equipment', 'user:id,name'])
            ->where('condominium_id', $context['condominium_id']), 'created_at', $context);

        $rows = $query->clone()
            ->latest()
            ->limit(500)
            ->get()
            ->map(fn (MaintenanceRecord $record) => [
                'schedule' => $record->schedule?->title ?: '',
                'equipment' => $record->schedule?->equipment?->name ?: '',
                'status' => $record->status,
                'responsible' => $record->user?->name ?: '',
                'performed_at' => $this->formatDateTime($record->performed_at),
            ])->all();

        return $this->report('maintenance', 'Manutenção', $context, [
            $this->summary('Registros no período', $query->clone()->count(), 'blue'),
            $this->summary('Concluídos', $query->clone()->where('status', 'completed')->count(), 'green'),
            $this->summary('Pendentes', $query->clone()->where('status', 'pending')->count(), 'orange'),
            $this->summary('Vencidos/Reabertos', $query->clone()->whereIn('status', ['overdue', 'reopened'])->count(), 'red'),
        ], [
            ['key' => 'schedule', 'label' => 'Agendamento'],
            ['key' => 'equipment', 'label' => 'Equipamento'],
            ['key' => 'status', 'label' => 'Status'],
            ['key' => 'responsible', 'label' => 'Responsável'],
            ['key' => 'performed_at', 'label' => 'Realizada em'],
        ], $rows);
    }

    private function accessReport(array $context): array
    {
        $query = $this->between(AccessRequest::with(['resident', 'apartment.block', 'apartment.tower'])
            ->where('condominium_id', $context['condominium_id']), 'visit_date', $context);

        $rows = $query->clone()
            ->latest('visit_date')
            ->limit(500)
            ->get()
            ->map(fn (AccessRequest $request) => [
                'visitor' => $request->visitor_name,
                'resident' => $request->resident?->name ?: '',
                'unit' => $this->formatApartment($request->apartment),
                'status' => $request->status,
                'entry' => $this->formatDateTime($request->actual_entry_timestamp ?: $request->entry_time),
                'exit' => $this->formatDateTime($request->actual_exit_timestamp ?: $request->exit_time),
            ])->all();

        return $this->report('access', 'Controle de Acesso', $context, [
            $this->summary('Solicitações no período', $query->clone()->count(), 'blue'),
            $this->summary('Autorizadas', $query->clone()->whereIn('status', ['approved', 'authorized'])->count(), 'green'),
            $this->summary('Pendentes', $query->clone()->where('status', 'pending')->count(), 'orange'),
            $this->summary('Visitas em aberto', $query->clone()->whereNotNull('actual_entry_timestamp')->whereNull('actual_exit_timestamp')->count(), 'red'),
        ], [
            ['key' => 'visitor', 'label' => 'Visitante'],
            ['key' => 'resident', 'label' => 'Morador destino'],
            ['key' => 'unit', 'label' => 'Unidade'],
            ['key' => 'status', 'label' => 'Status'],
            ['key' => 'entry', 'label' => 'Entrada'],
            ['key' => 'exit', 'label' => 'Saída'],
        ], $rows);
    }

    private function report(string $type, string $title, array $context, array $summary, array $columns, array $rows): array
    {
        return [
            'type' => $type,
            'title' => $title,
            'start_date' => $context['start']->toDateString(),
            'end_date' => $context['end']->toDateString(),
            'generated_at' => now()->toISOString(),
            'summary' => $summary,
            'columns' => $columns,
            'rows' => $rows,
        ];
    }

    private function overviewRow(string $module, string $indicator, int|string $value, string $notes): array
    {
        return compact('module', 'indicator', 'value', 'notes');
    }

    private function summary(string $label, int|float|string $value, string $tone): array
    {
        return [
            'label' => $label,
            'value' => (string) $value,
            'tone' => $tone,
        ];
    }

    private function between(Builder $query, string $column, array $context): Builder
    {
        return $query->whereBetween($column, [$context['start'], $context['end']]);
    }

    private function betweenDates(Builder $query, string $column, array $context): Builder
    {
        return $query->whereBetween($column, [$context['start']->toDateString(), $context['end']->toDateString()]);
    }

    private function formatApartment(?Apartment $apartment): string
    {
        if (! $apartment) {
            return '';
        }

        $parts = ["Unidade {$apartment->number}"];

        if ($apartment->floor !== null) {
            $parts[] = "{$apartment->floor}º andar";
        }

        if ($apartment->block?->name) {
            $parts[] = "Bloco {$apartment->block->name}";
        }

        if ($apartment->tower?->name) {
            $parts[] = "Torre {$apartment->tower->name}";
        }

        return implode(' - ', $parts);
    }

    private function formatDateTime($value): string
    {
        return $value ? Carbon::parse($value)->format('d/m/Y H:i') : '';
    }

    private function formatDate($value): string
    {
        return $value ? Carbon::parse($value)->format('d/m/Y') : '';
    }

    private function formatMoney(float|string|null $value): string
    {
        return 'R$ '.number_format((float) $value, 2, ',', '.');
    }

    private function enumValue($value): string
    {
        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        return (string) ($value ?? '');
    }

    private function currentCondominiumId(Request $request): int
    {
        if (app()->bound('currentCondominium')) {
            return app('currentCondominium')->id;
        }

        if ($request->attributes->has('condominium')) {
            return $request->attributes->get('condominium')->id;
        }

        return (int) $request->user()->condominium_id;
    }
}
