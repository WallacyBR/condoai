<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Condominium;
use App\Models\Profile;
use App\Models\MaintenanceEquipment;
use App\Models\MaintenanceSchedule;
use Carbon\Carbon;

class MaintenanceScheduleControllerTest extends TestCase
{
    use RefreshDatabase; // Usa o RefreshDatabase para resetar o banco a cada teste

    protected User $adminUser;
    protected User $responsibleUser1;
    protected User $responsibleUser2;
    protected Condominium $condominium;
    protected MaintenanceEquipment $equipment;

    protected function setUp(): void
    {
        parent::setUp();

        // Criar um perfil de administrador e de zelador/colaborador
        $adminProfile = Profile::factory()->create(['name' => 'Admin']);
        $staffProfile = Profile::factory()->create(['name' => 'Zelador']);

        // Criar um condomínio
        $this->condominium = Condominium::factory()->create();

        // Criar usuários
        $this->adminUser = User::factory()->for($this->condominium)->for($adminProfile)->create();
        $this->responsibleUser1 = User::factory()->for($this->condominium)->for($staffProfile)->create();
        $this->responsibleUser2 = User::factory()->for($this->condominium)->for($staffProfile)->create();

        // Criar um equipamento de manutenção
        $this->equipment = MaintenanceEquipment::factory()->for($this->condominium)->create();

        // Autenticar como adminUser para os testes que criam/modificam agendamentos
        $this->actingAs($this->adminUser, 'api');
    }

    public function test_can_create_maintenance_schedule_with_responsible_users()
    {
        $scheduleData = [
            'title' => 'Teste de Agendamento com Responsáveis',
            'maintenance_equipment_id' => $this->equipment->id,
            'type' => 'weekly',
            'start_date' => Carbon::today()->toDateString(),
            'is_recurring' => true,
            'specific_days_of_week' => [1, 3], // Segunda, Quarta
            'responsible_user_ids' => [$this->responsibleUser1->id, $this->responsibleUser2->id],
        ];

        $response = $this->postJson(route('maintenance.schedules.store'), $scheduleData);

        $response->assertStatus(201)
            ->assertJsonFragment(['title' => $scheduleData['title']])
            ->assertJsonCount(2, 'responsible_users'); // Verifica se os dois usuários foram associados

        $this->assertDatabaseHas('maintenance_schedules', ['title' => $scheduleData['title']]);
        $schedule = MaintenanceSchedule::where('title', $scheduleData['title'])->first();
        $this->assertCount(2, $schedule->responsibleUsers);
        $this->assertTrue($schedule->responsibleUsers->contains($this->responsibleUser1));
        $this->assertTrue($schedule->responsibleUsers->contains($this->responsibleUser2));
    }

    public function test_can_update_maintenance_schedule_with_new_responsible_users()
    {
        $schedule = MaintenanceSchedule::factory()
            ->for($this->condominium)
            ->for($this->equipment)
            ->create();

        $schedule->responsibleUsers()->attach($this->responsibleUser1->id);

        $updateData = [
            'title' => 'Agendamento Atualizado com Novos Responsáveis',
            'responsible_user_ids' => [$this->responsibleUser2->id], // Troca para apenas responsibleUser2
        ];

        $response = $this->putJson(route('maintenance.schedules.update', $schedule->id), $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => $updateData['title']])
            ->assertJsonCount(1, 'responsible_users')
            ->assertJsonPath('responsible_users.0.id', $this->responsibleUser2->id);

        $schedule->refresh();
        $this->assertCount(1, $schedule->responsibleUsers);
        $this->assertTrue($schedule->responsibleUsers->contains($this->responsibleUser2));
        $this->assertFalse($schedule->responsibleUsers->contains($this->responsibleUser1));
    }

    public function test_index_loads_responsible_users()
    {
        MaintenanceSchedule::factory()
            ->for($this->condominium)
            ->for($this->equipment)
            ->hasAttached($this->responsibleUser1, [], 'responsibleUsers') // Associa responsibleUser1
            ->create(['title' => 'Agendamento para Index Test']);

        $response = $this->getJson(route('maintenance.schedules.index'));

        $response->assertStatus(200)
                 ->assertJsonStructure(['data' => [['id', 'title', 'responsible_users']]])
                 ->assertJsonFragment(['name' => $this->responsibleUser1->name]); // Verifica se o nome do responsável está no JSON
    }

    public function test_show_loads_responsible_users()
    {
        $schedule = MaintenanceSchedule::factory()
            ->for($this->condominium)
            ->for($this->equipment)
            ->hasAttached($this->responsibleUser1, [], 'responsibleUsers')
            ->create();

        $response = $this->getJson(route('maintenance.schedules.show', $schedule->id));

        $response->assertStatus(200)
                 ->assertJsonStructure(['id', 'title', 'responsible_users'])
                 ->assertJsonPath('responsible_users.0.id', $this->responsibleUser1->id);
    }

    public function test_my_today_schedules_returns_correct_daily_task_for_responsible_user()
    {
        $today = Carbon::today();
        MaintenanceSchedule::factory()
            ->for($this->condominium)
            ->for($this->equipment)
            ->hasAttached($this->responsibleUser1, [], 'responsibleUsers')
            ->create([
                'title' => 'Tarefa Diária de Hoje',
                'type' => 'daily',
                'start_date' => $today->copy()->subDays(5), // Começou há 5 dias
                'end_date' => $today->copy()->addDays(5),   // Termina em 5 dias
                'is_recurring' => true,
            ]);

        // Autentica como o usuário responsável
        $this->actingAs($this->responsibleUser1, 'api');
        $response = $this->getJson(route('maintenance.schedules.myToday', ['date' => $today->toDateString()]));

        $response->assertStatus(200)
            ->assertJsonCount(1) // Espera uma tarefa
            ->assertJsonFragment(['title' => 'Tarefa Diária de Hoje']);
    }

    public function test_my_today_schedules_returns_correct_weekly_task_for_responsible_user()
    {
        // Configura para que "hoje" seja uma segunda-feira (dayOfWeek = 1)
        $today = Carbon::parse('next monday')->startOfDay();

        MaintenanceSchedule::factory()
            ->for($this->condominium)
            ->for($this->equipment)
            ->hasAttached($this->responsibleUser1, [], 'responsibleUsers')
            ->create([
                'title' => 'Tarefa Semanal de Segunda',
                'type' => 'weekly',
                'start_date' => $today->copy()->subWeeks(2),
                'is_recurring' => true,
                'specific_days_of_week' => [1], // 1 = Segunda-feira
            ]);

        // Outra tarefa que não deve aparecer
         MaintenanceSchedule::factory()
            ->for($this->condominium)
            ->for($this->equipment)
            ->hasAttached($this->responsibleUser1, [], 'responsibleUsers')
            ->create([
                'title' => 'Tarefa Semanal de Terça',
                'type' => 'weekly',
                'start_date' => $today->copy()->subWeeks(2),
                'is_recurring' => true,
                'specific_days_of_week' => [2], // 2 = Terça-feira
            ]);

        $this->actingAs($this->responsibleUser1, 'api');
        $response = $this->getJson(route('maintenance.schedules.myToday', ['date' => $today->toDateString()]));

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['title' => 'Tarefa Semanal de Segunda'])
            ->assertJsonMissing(['title' => 'Tarefa Semanal de Terça']);
    }

    public function test_my_today_schedules_returns_non_recurring_task_on_start_date_only()
    {
        $today = Carbon::today();
        MaintenanceSchedule::factory()
            ->for($this->condominium)
            ->for($this->equipment)
            ->hasAttached($this->responsibleUser1, [], 'responsibleUsers')
            ->create([
                'title' => 'Tarefa Única de Hoje',
                'type' => 'daily', // Tipo não importa tanto se is_recurring = false
                'start_date' => $today,
                'is_recurring' => false,
            ]);

        $this->actingAs($this->responsibleUser1, 'api');

        // Deve aparecer hoje
        $responseToday = $this->getJson(route('maintenance.schedules.myToday', ['date' => $today->toDateString()]));
        $responseToday->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['title' => 'Tarefa Única de Hoje']);

        // Não deve aparecer amanhã
        $tomorrow = $today->copy()->addDay();
        $responseTomorrow = $this->getJson(route('maintenance.schedules.myToday', ['date' => $tomorrow->toDateString()]));
        $responseTomorrow->assertStatus(200)->assertJsonCount(0);
    }

    public function test_my_today_schedules_does_not_return_task_if_not_responsible()
    {
        $today = Carbon::today();
        MaintenanceSchedule::factory()
            ->for($this->condominium)
            ->for($this->equipment)
            ->hasAttached($this->responsibleUser2, [], 'responsibleUsers') // Atribuído a outro usuário
            ->create([
                'title' => 'Tarefa de Outro Usuário',
                'type' => 'daily',
                'start_date' => $today,
                'is_recurring' => true,
            ]);

        // Autenticado como responsibleUser1
        $this->actingAs($this->responsibleUser1, 'api');
        $response = $this->getJson(route('maintenance.schedules.myToday', ['date' => $today->toDateString()]));

        $response->assertStatus(200)->assertJsonCount(0);
    }
}
