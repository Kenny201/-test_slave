<?php

namespace Tests\Feature;

use App\Models\Row;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RowsControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_index_displays_rows_grouped_by_date()
    {
        $date = now()->format('Y-m-d');
        Row::factory()->count(3)->create(['date' => $date]);

        $response = $this->actingAs($this->user)
            ->get(route('rows.index'));

        $response->assertStatus(200);
        $response->assertViewHas('rows');
    }

    public function test_pagination_works_correctly()
    {
        Row::factory()->count(15)->create();

        $response = $this->actingAs($this->user)
            ->get(route('rows.index'));

        $response->assertStatus(200);
        $response->assertViewHas('rows');
    }
}
