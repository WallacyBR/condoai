<?php

namespace App\Console\Commands;

use App\Models\Condominium;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SetupTestUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup:test-user {--domain= : Domínio do condomínio para criar o usuário de teste}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configura usuário de teste para o sistema';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $domain = $this->option('domain');

        if (! $domain) {
            $domain = $this->ask('Digite o domínio do condomínio (ex: meudominio.com):');
        }

        if (! $domain) {
            $this->error('Domínio é obrigatório!');

            return 1;
        }

        // Buscar ou criar condomínio
        $condominium = Condominium::where('domain', $domain)->first();

        if (! $condominium) {
            $this->warn("Condomínio com domínio '{$domain}' não encontrado.");

            if ($this->confirm('Deseja criar um condomínio de teste com este domínio?')) {
                $condominium = Condominium::create([
                    'name' => 'Condomínio Teste - '.$domain,
                    'document' => '00.000.000/0001-00',
                    'address' => 'Rua Exemplo, 123',
                    'city' => 'Cidade Exemplo',
                    'state' => 'SP',
                    'zip_code' => '00000-000',
                    'phone' => '(11) 99999-9999',
                    'email' => 'contato@'.$domain,
                    'responsible' => 'Síndico Exemplo',
                    'active' => true,
                    'domain' => $domain,
                ]);

                $this->info("Condomínio criado com ID: {$condominium->id}");
            } else {
                return 1;
            }
        }

        // Verificar se já existe um perfil
        $profileId = 1; // Assumindo que o primeiro perfil é o padrão

        // Criar usuário de teste
        $user = User::firstOrCreate(
            ['email' => 'teste@teste.com'],
            [
                'name' => 'Usuário Teste',
                'password' => Hash::make('teste'),
                'profile_id' => $profileId,
                'condominium_id' => $condominium->id,
            ]
        );

        if ($user->wasRecentlyCreated) {
            $this->info('Usuário de teste criado com sucesso!');
        } else {
            $this->info('Usuário de teste já existe!');
        }

        $this->info('Credenciais de teste:');
        $this->info('Email: teste@teste.com');
        $this->info('Senha: teste');
        $this->info("Condomínio: {$condominium->name} (ID: {$condominium->id})");
        $this->info("Domínio: {$condominium->domain}");

        return 0;
    }
}
