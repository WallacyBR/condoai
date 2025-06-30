<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Condominium;
use App\Models\Occurrence;
use App\Models\OccurrencePhoto;
use App\Models\OccurrenceComment;
use App\Enums\OccurrenceStatus;
use App\Enums\OccurrencePriority;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

class OccurrenceApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected Condominium $condominium;

    protected function setUp(): void
    {
        parent::setUp();
        $this->condominium = Condominium::factory()->create();
        $this->user = User::factory()->for($this->condominium)->create();
        Sanctum::actingAs($this->user, ['*']); // Assume default abilities
    }

    public function test_can_get_all_occurrences()
    {
        Occurrence::factory()->count(3)->for($this->condominium)->for($this->user)->create();

        $response = $this->getJson(route('occurrences.index'));

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_create_occurrence_without_photos()
    {
        $data = [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'priority' => OccurrencePriority::MEDIUM->value,
        ];

        $response = $this->postJson(route('occurrences.store'), $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['title' => $data['title']]);
        $this->assertDatabaseHas('occurrences', ['title' => $data['title'], 'condominium_id' => $this->condominium->id]);
    }

    public function test_can_create_occurrence_with_photos()
    {
        Storage::fake('public');

        $data = [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'priority' => OccurrencePriority::HIGH->value,
            'photos' => [
                UploadedFile::fake()->image('photo1.jpg'),
                UploadedFile::fake()->image('photo2.png'),
            ],
            'photo_descriptions' => [
                'Description for photo 1',
                'Description for photo 2',
            ]
        ];

        $response = $this->postJson(route('occurrences.store'), $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['title' => $data['title']]);

        $occurrence = Occurrence::first();
        $this->assertCount(2, $occurrence->photos);
        $this->assertEquals('Description for photo 1', $occurrence->photos[0]->description);

        // Assert photos were stored
        foreach ($occurrence->photos as $photo) {
            Storage::disk('public')->assertExists($photo->photo_path);
        }
    }

    public function test_can_get_single_occurrence()
    {
        $occurrence = Occurrence::factory()->for($this->condominium)->for($this->user)->create();

        $response = $this->getJson(route('occurrences.show', $occurrence));

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => $occurrence->title]);
    }

    public function test_can_update_occurrence()
    {
        $occurrence = Occurrence::factory()->for($this->condominium)->for($this->user)->create();
        $newData = [
            'title' => 'Updated Title',
            'status' => OccurrenceStatus::IN_PROGRESS->value,
            'priority' => OccurrencePriority::URGENT->value,
        ];

        $response = $this->putJson(route('occurrences.update', $occurrence), $newData);

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Updated Title', 'status' => OccurrenceStatus::IN_PROGRESS->value]);
        $this->assertDatabaseHas('occurrences', ['id' => $occurrence->id, 'title' => 'Updated Title']);
    }

    public function test_can_delete_occurrence_with_photos()
    {
        Storage::fake('public');
        $occurrence = Occurrence::factory()->for($this->condominium)->for($this->user)->create();
        $photo = $occurrence->photos()->create([
            'photo_path' => UploadedFile::fake()->image('test.jpg')->store('occurrence_photos', 'public'),
        ]);
        Storage::disk('public')->assertExists($photo->photo_path);

        $response = $this->deleteJson(route('occurrences.destroy', $occurrence));

        $response->assertStatus(204);
        $this->assertSoftDeleted('occurrences', ['id' => $occurrence->id]);
        $this->assertDatabaseMissing('occurrence_photos', ['id' => $photo->id]); // Assuming hard delete for photos on occurrence soft delete
        Storage::disk('public')->assertMissing($photo->photo_path);
    }

    public function test_can_add_photo_to_occurrence()
    {
        Storage::fake('public');
        $occurrence = Occurrence::factory()->for($this->condominium)->for($this->user)->create();

        $response = $this->postJson(route('occurrences.addPhoto', $occurrence), [
            'photo' => UploadedFile::fake()->image('new_photo.jpg'),
            'description' => 'New photo description'
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['description' => 'New photo description']);
        $this->assertCount(1, $occurrence->refresh()->photos);
        Storage::disk('public')->assertExists($occurrence->photos->first()->photo_path);
    }

    public function test_can_delete_photo_from_occurrence()
    {
        Storage::fake('public');
        $occurrence = Occurrence::factory()->for($this->condominium)->for($this->user)->create();
        $photo = $occurrence->photos()->create([
            'photo_path' => UploadedFile::fake()->image('to_delete.jpg')->store('occurrence_photos', 'public'),
        ]);
        Storage::disk('public')->assertExists($photo->photo_path);

        $response = $this->deleteJson(route('occurrences.deletePhoto', ['occurrence' => $occurrence, 'photo' => $photo]));

        $response->assertStatus(204);
        $this->assertCount(0, $occurrence->refresh()->photos);
        Storage::disk('public')->assertMissing($photo->photo_path);
    }

    public function test_can_add_comment_to_occurrence()
    {
        $occurrence = Occurrence::factory()->for($this->condominium)->for($this->user)->create();
        $commentData = ['comment' => 'This is a test comment.'];

        $response = $this->postJson(route('occurrences.addComment', $occurrence), $commentData);

        $response->assertStatus(201)
            ->assertJsonFragment($commentData);
        $this->assertCount(1, $occurrence->refresh()->comments);
        $this->assertEquals($this->user->id, $occurrence->comments->first()->user_id);
    }

    public function test_can_delete_comment_from_occurrence()
    {
        $occurrence = Occurrence::factory()->for($this->condominium)->for($this->user)->create();
        $comment = $occurrence->comments()->create([
            'comment' => 'To be deleted',
            'user_id' => $this->user->id,
        ]);

        // Acting as the user who created the comment
        Sanctum::actingAs($this->user);
        $response = $this->deleteJson(route('occurrences.deleteComment', ['occurrence' => $occurrence, 'comment' => $comment]));

        $response->assertStatus(204);
        $this->assertCount(0, $occurrence->refresh()->comments);
    }

    public function test_user_cannot_view_occurrence_from_another_condominium()
    {
        $otherCondominium = Condominium::factory()->create();
        $occurrenceInOtherCondo = Occurrence::factory()->for($otherCondominium)->create();

        $response = $this->getJson(route('occurrences.show', $occurrenceInOtherCondo));

        // This will depend on how OccurrencePolicy is implemented.
        // If CondominiumScope is correctly applied and policy checks condominium_id.
        // If show() method uses findOrFail or similar, it might return 404 or 403 if policy forbids.
        // Let's assume it's a 403 due to policy after model is found.
        // Or 404 if the global scope prevents finding it altogether for this user.
        // Given CondominiumScope, it should be a 404.
        $response->assertStatus(404); // Or 403 if policy is checked after model retrieval without scope
    }

    public function test_validation_fails_for_creating_occurrence_with_invalid_data()
    {
        $response = $this->postJson(route('occurrences.store'), [
            'title' => '', // Invalid: title is required
            'priority' => 'invalid_priority_value', // Invalid: not in enum
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'priority']);
    }
}
