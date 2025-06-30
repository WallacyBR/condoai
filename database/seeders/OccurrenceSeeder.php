<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Condominium;
use App\Models\User;
use App\Models\Occurrence;
use App\Models\OccurrencePhoto;
use App\Models\OccurrenceComment;
use App\Enums\OccurrenceStatus;
use App\Enums\OccurrencePriority;
use Illuminate\Support\Facades\File; // For potential future use with actual placeholder images
use Illuminate\Support\Facades\Storage; // For potential future use

class OccurrenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Ensure storage/app/public/occurrence_photos exists
        $photosPath = storage_path('app/public/occurrence_photos');
        if (!File::exists($photosPath)) {
            File::makeDirectory($photosPath, 0755, true);
        }
        // Placeholder: copy a dummy image if you have one, e.g. from public/images
        // For now, we'll just use placeholder paths without actual files.
        // if (!File::exists($photosPath . '/placeholder.jpg')) {
        //     File::copy(public_path('images/condo_ai_quadrado.png'), $photosPath . '/placeholder.jpg');
        // }

        $condominiums = Condominium::all();
        if ($condominiums->isEmpty()) {
            $this->command->info('No condominiums found. Skipping OccurrenceSeeder.');
            return;
        }

        $users = User::whereNotNull('condominium_id')->get();
        if ($users->isEmpty()) {
            $this->command->info('No users with condominiums found. Skipping OccurrenceSeeder.');
            return;
        }

        $sampleOccurrences = [
            [
                'title' => 'Lâmpada queimada no corredor do Bloco A',
                'description' => 'A lâmpada principal do corredor do segundo andar do Bloco A está queimada há dois dias, dificultando a passagem à noite.',
                'priority' => OccurrencePriority::HIGH,
                'status' => OccurrenceStatus::OPEN,
                'photos' => [
                    ['photo_path' => 'occurrence_photos/placeholder_lampada_1.jpg', 'description' => 'Foto da lâmpada apagada.'],
                    ['photo_path' => 'occurrence_photos/placeholder_corredor_escuro.jpg', 'description' => 'Corredor durante a noite.'],
                ],
                'comments' => [
                    ['comment' => 'Realmente está bem escuro, quase tropecei ontem.'],
                    ['comment' => 'Já solicitei a troca para o zelador.'],
                ]
            ],
            [
                'title' => 'Vazamento na torneira da área da piscina',
                'description' => 'A torneira próxima ao vestiário da piscina está com um vazamento constante, desperdiçando água.',
                'priority' => OccurrencePriority::MEDIUM,
                'status' => OccurrenceStatus::IN_PROGRESS,
                'photos' => [
                    ['photo_path' => 'occurrence_photos/placeholder_vazamento_piscina.jpg', 'description' => 'Torneira pingando.'],
                ],
                'comments' => [
                    ['comment' => 'A equipe de manutenção já foi acionada e está verificando.'],
                ]
            ],
            [
                'title' => 'Portão da garagem com barulho estranho',
                'description' => 'O portão principal da garagem está fazendo um barulho alto ao abrir e fechar nos últimos dias.',
                'priority' => OccurrencePriority::HIGH,
                'status' => OccurrenceStatus::OPEN,
                'photos' => [],
                'comments' => [
                    ['comment' => 'Confirmado o barulho, parece ser necessário lubrificação.'],
                ]
            ],
            [
                'title' => 'Elevador social do Bloco C parado',
                'description' => 'O elevador social do Bloco C parou de funcionar esta manhã. Moradores estão usando o de serviço.',
                'priority' => OccurrencePriority::URGENT,
                'status' => OccurrenceStatus::REOPENED,
                'photos' => [],
                'comments' => [
                    ['comment' => 'Técnico já foi chamado.'],
                    ['comment' => 'O elevador voltou a funcionar, mas parou novamente.'],
                ]
            ],
            [
                'title' => 'Limpeza da quadra de esportes pendente',
                'description' => 'A quadra de esportes precisa de uma limpeza mais caprichada, há folhas e sujeira acumulada.',
                'priority' => OccurrencePriority::LOW,
                'status' => OccurrenceStatus::RESOLVED,
                'photos' => [
                     ['photo_path' => 'occurrence_photos/placeholder_quadra_limpa.jpg', 'description' => 'Quadra após a limpeza.'],
                ],
                'comments' => [
                    ['comment' => 'Limpeza realizada com sucesso.'],
                ]
            ],
        ];

        foreach ($sampleOccurrences as $occurrenceData) {
            $condominium = $condominiums->random();
            $user = $users->where('condominium_id', $condominium->id)->random();

            if (!$user) { // Fallback if no user for that specific condo
                $user = $users->random();
            }

            $occurrence = Occurrence::create([
                'condominium_id' => $condominium->id,
                'user_id' => $user->id,
                'title' => $occurrenceData['title'],
                'description' => $occurrenceData['description'],
                'priority' => $occurrenceData['priority'],
                'status' => $occurrenceData['status'],
            ]);

            foreach ($occurrenceData['photos'] as $photoData) {
                OccurrencePhoto::create([
                    'occurrence_id' => $occurrence->id,
                    'photo_path' => $photoData['photo_path'], // Placeholder path
                    'description' => $photoData['description'],
                ]);
            }

            $commentUsers = $users->where('condominium_id', $condominium->id)->count() > 1 ?
                            $users->where('condominium_id', $condominium->id)->random(min(2, $users->where('condominium_id', $condominium->id)->count())) :
                            collect([$user]);

            foreach ($occurrenceData['comments'] as $index => $commentData) {
                $commentUser = $commentUsers->get($index % $commentUsers->count()) ?: $user;
                OccurrenceComment::create([
                    'occurrence_id' => $occurrence->id,
                    'user_id' => $commentUser->id,
                    'comment' => $commentData['comment'],
                ]);
            }
        }

        $this->command->info('OccurrenceSeeder run successfully.');
    }
}
