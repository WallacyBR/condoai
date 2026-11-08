<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Notice;
use App\Models\User;
use App\Models\Condominium;

class NoticeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar um usuário e condomínio existente
        $user = User::first();
        $condominium = Condominium::first();

        if (!$user || !$condominium) {
            $this->command->warn('Usuário ou condomínio não encontrado. Execute os seeders básicos primeiro.');
            return;
        }

        // Criar alguns avisos de teste
        $notices = [
            [
                'title' => 'Reunião de Condomínio - Janeiro 2025',
                'body' => 'Prezados condôminos, informamos que será realizada reunião ordinária no dia 30 de janeiro de 2025, às 19h30, no salão de festas do condomínio. Pauta: discussão sobre taxa de condomínio, obras de manutenção e mudanças no regulamento interno.',
                'send_to_all' => true,
            ],
            [
                'title' => 'Manutenção do Elevador - Bloco A',
                'body' => 'Informamos que será realizada manutenção preventiva no elevador do Bloco A no dia 25 de janeiro, das 8h às 17h. Pedimos a compreensão dos moradores e que utilizem as escadas durante este período.',
                'send_to_all' => false,
            ],
            [
                'title' => 'Nova Regulamentação da Piscina',
                'body' => 'A partir de 1º de fevereiro, a piscina terá novos horários de funcionamento: Segunda a sexta das 6h às 22h, sábados e domingos das 7h às 20h. É obrigatório o uso de touca e o acompanhamento de menores de 14 anos por responsável.',
                'send_to_all' => true,
            ],
            [
                'title' => 'Obras no Playground',
                'body' => 'Informamos que o playground passará por reformas e melhorias de segurança. O local ficará interditado de 20 a 31 de janeiro. Contamos com a colaboração de todos.',
                'send_to_all' => true,
            ],
            [
                'title' => 'Coleta Seletiva - Novo Cronograma',
                'body' => 'A partir desta semana, a coleta seletiva será realizada às quartas-feiras pela manhã. Lembramos que materiais recicláveis devem ser separados corretamente e colocados nos locais apropriados.',
                'send_to_all' => true,
            ]
        ];

        foreach ($notices as $noticeData) {
            Notice::create([
                'condominium_id' => $condominium->id,
                'user_id' => $user->id,
                'title' => $noticeData['title'],
                'body' => $noticeData['body'],
                'send_to_all' => $noticeData['send_to_all'],
            ]);
        }

        $this->command->info('Avisos de teste criados com sucesso!');
    }
}
