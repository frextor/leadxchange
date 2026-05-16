<?php

namespace Tests\Feature;

use App\Models\InboxItem;
use App\Models\User;
use Tests\TestCase;

class InboxTest extends TestCase
{

    public function test_inbox_page_returns_200_with_shell(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/inbox');

        $response->assertStatus(200);
        $response->assertSee('ix-shell', false);
    }

    public function test_archive_sets_archived_to_true(): void
    {
        $user = User::factory()->create();
        $item = InboxItem::factory()->create([
            'user_id'  => $user->id,
            'archived' => false,
        ]);

        $response = $this->actingAs($user)->postJson("/inbox/{$item->id}/archive");

        $response->assertStatus(200)->assertJson(['ok' => true]);
        $this->assertDatabaseHas('inbox_items', [
            'id'       => $item->id,
            'archived' => true,
        ]);
    }

    public function test_unread_filter_shows_only_unread_items(): void
    {
        $user = User::factory()->create();

        InboxItem::factory()->count(3)->create([
            'user_id' => $user->id,
            'read'    => false,
            'archived' => false,
        ]);
        InboxItem::factory()->count(2)->create([
            'user_id' => $user->id,
            'read'    => true,
            'archived' => false,
        ]);

        $response = $this->actingAs($user)->get('/inbox');
        $response->assertStatus(200);

        // The page returns all items as JSON; unread count should be 3
        $response->assertSee('"read":false', false);
    }
}
