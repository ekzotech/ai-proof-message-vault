<?php

namespace Tests\Feature;

use App\Capsules\TransformationPreset;
use App\Models\Capsule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_sqlite_uses_the_author_and_sorting_index_for_the_dashboard(): void
    {
        $author = User::factory()->create();
        Capsule::factory()->count(24)->for($author, 'author')->create();
        $query = $author->capsules()->whereDoesntHave('publication')
            ->latest('updated_at')->orderByDesc('id')->limit(12)->select(['id', 'title'])->toBase();
        $plan = collect(DB::select('EXPLAIN QUERY PLAN '.$query->toSql(), $query->getBindings()))->pluck('detail')->join(' ');

        $this->assertStringContainsString('capsules_author_updated_id_index', $plan);
        $this->assertStringNotContainsString('TEMP B-TREE FOR ORDER BY', $plan);
    }

    public function test_the_filter_keeps_only_the_requested_capsule_state(): void
    {
        $author = User::factory()->create();
        $draft = Capsule::factory()->for($author, 'author')->create(['title' => 'Уникальный черновик']);
        $published = Capsule::factory()->for($author, 'author')->create(['title' => 'Опубликованное послание']);
        $published->publication()->create([
            'title' => $published->title,
            'opens_at' => now()->addHour(),
            'algorithm_version' => 1,
            'seed' => $published->transformation_seed,
            'preset' => TransformationPreset::Gentle,
            'segments' => [],
        ]);

        $this->withoutVite()->actingAs($author)->get(route('dashboard', ['status' => 'waiting']))
            ->assertOk()->assertSee($published->title)->assertDontSee($draft->title);
        $this->get(route('dashboard', ['status' => 'draft']))
            ->assertOk()->assertSee($draft->title)->assertDontSee($published->title);
        $this->get(route('dashboard', ['status' => 'open']))->assertOk()->assertDontSee($published->title);
    }

    public function test_the_dashboard_is_private_and_prompts_unverified_authors(): void
    {
        $this->withoutVite()->get(route('dashboard'))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->unverified()->create())
            ->get(route('dashboard'))->assertOk()->assertSee('Подтвердить почту');

        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))->assertOk()->assertDontSee('Подтвердить почту');
    }

    public function test_dashboard_paginates_only_the_authors_drafts_in_stable_order(): void
    {
        $author = User::factory()->create();
        $drafts = Capsule::factory()->count(13)->for($author, 'author')
            ->create(['updated_at' => '2026-11-10 00:00:00']);
        $foreign = Capsule::factory()->create(['title' => 'Чужой черновик']);
        $first = $drafts->first();
        $last = $drafts->last();

        $this->withoutVite()->actingAs($author)->get(route('dashboard'))
            ->assertSee($last->title)->assertSee('Черновик')->assertSee('capsule-list-meta')
            ->assertDontSee($first->title)->assertDontSee($foreign->title);

        $this->get(route('dashboard', ['page' => 2]))
            ->assertSee($first->title)->assertDontSee($last->title)->assertDontSee($foreign->title);
    }
}
