<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AccessRequestController extends Controller
{
    public function index(Request $request)
    {
        // Retornar dados fictícios para evitar páginas em branco
        $data = [
            [
                'id' => 1,
                'status' => 'authorized',
                'request_timestamp' => now()->subHours(2)->toISOString(),
                'resident_response_timestamp' => now()->subHour()->toISOString(),
                'visitor' => [
                    'id' => 1,
                    'name' => 'João Silva',
                    'document' => '123.456.789-00',
                    'phone' => '(11) 99999-9999',
                    'photo_url' => null
                ],
                'resident' => [
                    'id' => 1,
                    'name' => 'Maria Santos'
                ],
                'visitor_vehicle' => null,
                'resident_observation' => 'Visitante autorizado',
                'gatehouse_observation' => 'Documentação verificada'
            ],
            [
                'id' => 2,
                'status' => 'unauthorized',
                'request_timestamp' => now()->subHours(1)->toISOString(),
                'resident_response_timestamp' => now()->subMinutes(30)->toISOString(),
                'visitor' => [
                    'id' => 2,
                    'name' => 'Pedro Costa',
                    'document' => '987.654.321-00',
                    'phone' => '(11) 88888-8888',
                    'photo_url' => null
                ],
                'resident' => [
                    'id' => 2,
                    'name' => 'Ana Lima'
                ],
                'visitor_vehicle' => [
                    'id' => 1,
                    'plate' => 'ABC-1234',
                    'model' => 'Honda Civic'
                ],
                'resident_observation' => 'Não estou em casa',
                'gatehouse_observation' => 'Morador não estava presente'
            ]
        ];

        // Filtrar por status se fornecido
        if ($request->has('status')) {
            $data = array_filter($data, function($item) use ($request) {
                return $item['status'] === $request->status;
            });
        }

        // Filtrar por exclude_status se fornecido
        if ($request->has('exclude_status')) {
            $data = array_filter($data, function($item) use ($request) {
                return $item['status'] !== $request->exclude_status;
            });
        }

        return response()->json([
            'data' => array_values($data),
            'message' => 'Dados de exemplo - Funcionalidade em desenvolvimento'
        ]);
    }

    public function store(Request $request)
    {
        return response()->json([
            'message' => 'Funcionalidade em desenvolvimento'
        ], 501);
    }

    public function listForGatehouse(Request $request)
    {
        return response()->json([
            'data' => [],
            'message' => 'Funcionalidade em desenvolvimento'
        ]);
    }

    public function listMyRequestsAsResident(Request $request)
    {
        // Dados fictícios para "Minhas Autorizações"
        $data = [
            [
                'id' => 3,
                'status' => 'pending',
                'request_timestamp' => now()->subMinutes(10)->toISOString(),
                'visitor' => [
                    'id' => 3,
                    'name' => 'Carlos Oliveira',
                    'document' => '555.666.777-88',
                    'phone' => '(11) 77777-7777',
                    'photo_url' => null
                ],
                'visitor_vehicle' => null,
                'gatehouse_observation' => 'Visitante aguardando na portaria'
            ]
        ];

        return response()->json([
            'data' => $data,
            'message' => 'Dados de exemplo - Funcionalidade em desenvolvimento'
        ]);
    }

    public function show($id)
    {
        return response()->json([
            'message' => 'Funcionalidade em desenvolvimento'
        ], 501);
    }

    public function respondToRequest(Request $request, $id)
    {
        return response()->json([
            'message' => 'Funcionalidade em desenvolvimento'
        ], 501);
    }

    public function recordEntry(Request $request, $id)
    {
        return response()->json([
            'message' => 'Funcionalidade em desenvolvimento'
        ], 501);
    }

    public function recordExit(Request $request, $id)
    {
        return response()->json([
            'message' => 'Funcionalidade em desenvolvimento'
        ], 501);
    }

    public function cancelRequest(Request $request, $id)
    {
        return response()->json([
            'message' => 'Funcionalidade em desenvolvimento'
        ], 501);
    }

    public function history(Request $request)
    {
        // Dados fictícios para histórico com paginação
        $allData = [
            [
                'id' => 1,
                'status' => 'authorized',
                'request_timestamp' => now()->subDays(5)->toISOString(),
                'resident_response_timestamp' => now()->subDays(5)->addHours(1)->toISOString(),
                'entry_timestamp' => now()->subDays(5)->addHours(2)->toISOString(),
                'visitor' => [
                    'id' => 1,
                    'name' => 'João Silva',
                    'document' => '123.456.789-00',
                    'phone' => '(11) 99999-9999',
                    'photo_url' => null
                ],
                'resident' => [
                    'id' => 1,
                    'name' => 'Maria Santos'
                ],
                'visitor_vehicle' => null,
                'resident_observation' => 'Visitante autorizado',
                'gatehouse_observation' => 'Entrada registrada às 14:30'
            ],
            [
                'id' => 2,
                'status' => 'unauthorized',
                'request_timestamp' => now()->subDays(3)->toISOString(),
                'resident_response_timestamp' => now()->subDays(3)->addMinutes(30)->toISOString(),
                'visitor' => [
                    'id' => 2,
                    'name' => 'Pedro Costa',
                    'document' => '987.654.321-00',
                    'phone' => '(11) 88888-8888',
                    'photo_url' => null
                ],
                'resident' => [
                    'id' => 2,
                    'name' => 'Ana Lima'
                ],
                'visitor_vehicle' => [
                    'id' => 1,
                    'plate' => 'ABC-1234',
                    'model' => 'Honda Civic'
                ],
                'resident_observation' => 'Não estou em casa',
                'gatehouse_observation' => 'Morador não estava presente'
            ],
            [
                'id' => 3,
                'status' => 'entry_recorded',
                'request_timestamp' => now()->subDays(2)->toISOString(),
                'resident_response_timestamp' => now()->subDays(2)->addMinutes(15)->toISOString(),
                'entry_timestamp' => now()->subDays(2)->addMinutes(45)->toISOString(),
                'visitor' => [
                    'id' => 3,
                    'name' => 'Carlos Oliveira',
                    'document' => '555.666.777-88',
                    'phone' => '(11) 77777-7777',
                    'photo_url' => null
                ],
                'resident' => [
                    'id' => 3,
                    'name' => 'José Santos'
                ],
                'visitor_vehicle' => [
                    'id' => 2,
                    'plate' => 'XYZ-9876',
                    'model' => 'Toyota Corolla'
                ],
                'resident_observation' => 'Funcionário da manutenção',
                'gatehouse_observation' => 'Entrada e saída registradas'
            ],
            [
                'id' => 4,
                'status' => 'cancelled',
                'request_timestamp' => now()->subDays(1)->toISOString(),
                'visitor' => [
                    'id' => 4,
                    'name' => 'Fernanda Lima',
                    'document' => '111.222.333-44',
                    'phone' => '(11) 66666-6666',
                    'photo_url' => null
                ],
                'resident' => [
                    'id' => 4,
                    'name' => 'Roberto Silva'
                ],
                'visitor_vehicle' => null,
                'resident_observation' => null,
                'gatehouse_observation' => 'Solicitação cancelada pelo visitante'
            ],
            [
                'id' => 5,
                'status' => 'inform_not_home',
                'request_timestamp' => now()->subHours(6)->toISOString(),
                'resident_response_timestamp' => now()->subHours(5)->addMinutes(30)->toISOString(),
                'visitor' => [
                    'id' => 5,
                    'name' => 'Marcos Pereira',
                    'document' => '999.888.777-66',
                    'phone' => '(11) 55555-5555',
                    'photo_url' => null
                ],
                'resident' => [
                    'id' => 5,
                    'name' => 'Carla Mendes'
                ],
                'visitor_vehicle' => null,
                'resident_observation' => 'Saí e não consegui voltar a tempo',
                'gatehouse_observation' => 'Visitante informado sobre ausência do morador'
            ]
        ];

        // Filtrar por status se fornecido
        if ($request->has('status') && $request->status !== '') {
            $allData = array_filter($allData, function($item) use ($request) {
                return $item['status'] === $request->status;
            });
        }

        // Simular paginação
        $perPage = $request->get('per_page', 15);
        $page = $request->get('page', 1);
        $total = count($allData);
        $offset = ($page - 1) * $perPage;
        $data = array_slice($allData, $offset, $perPage);

        // Calcular metadados de paginação
        $lastPage = ceil($total / $perPage);
        
        return response()->json([
            'data' => array_values($data),
            'meta' => [
                'current_page' => (int) $page,
                'last_page' => $lastPage,
                'per_page' => (int) $perPage,
                'total' => $total,
                'from' => $total > 0 ? $offset + 1 : null,
                'to' => $total > 0 ? min($offset + $perPage, $total) : null
            ],
            'message' => 'Dados de exemplo - Histórico de solicitações'
        ]);
    }
} 