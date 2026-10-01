<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            ['name' => 'Condomínios', 'description' => 'Gestão de condomínios'],
            ['name' => 'Moradores', 'description' => 'Cadastro de moradores'],
            ['name' => 'Unidades', 'description' => 'Torres, blocos e apartamentos'],
            ['name' => 'Correspondências', 'description' => 'Gestão de encomendas e correspondências'],
            ['name' => 'Ocorrências', 'description' => 'Registro e acompanhamento de ocorrências'],
            ['name' => 'Manutenção', 'description' => 'Sistema de manutenção preventiva e corretiva'],
            ['name' => 'Controle de Acesso', 'description' => 'Gestão de visitantes e autorizações'],
            ['name' => 'Classificados', 'description' => 'Anúncios e classificados do condomínio'],
            ['name' => 'Áreas Comuns', 'description' => 'Reserva e gestão de áreas comuns'],
            ['name' => 'Avisos', 'description' => 'Comunicados e notícias do condomínio'],
            ['name' => 'Relatórios', 'description' => 'Geração de relatórios'],
            ['name' => 'Financeiro', 'description' => 'Planejamento, títulos, aprovações e prestação de contas financeira'],
            ['name' => 'Cotações e Compras', 'description' => 'Cotação de itens e comparação de propostas de fornecedores'],
            ['name' => 'Segurança', 'description' => 'Logs de acesso, passkeys e auditoria de autenticação'],
            ['name' => 'Configurações', 'description' => 'Ajustes do sistema'],
            ['name' => 'Usuários', 'description' => 'Gestão de usuários e perfis'],
            ['name' => 'Dashboard', 'description' => 'Painel de controle e estatísticas'],
            ['name' => 'Financeiro', 'description' => 'Gestão financeira do condomínio'],
        ];

        foreach ($modules as $module) {
            Module::firstOrCreate(['name' => $module['name']], $module);
        }

        $this->command->info('Módulos do sistema criados com sucesso!');
    }
}
