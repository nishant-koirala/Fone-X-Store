<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\CategoryResource\Pages\CreateCategory;
use App\Filament\Resources\CategoryResource\Pages\EditCategory;
use App\Filament\Resources\CategoryResource\Pages\ListCategories;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAsAdmin();
    }

    public function test_can_render_category_list_page(): void
    {
        $categories = Category::factory()->count(3)->create();

        Livewire::test(ListCategories::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords($categories);
    }

    public function test_can_create_category_via_filament(): void
    {
        Livewire::test(CreateCategory::class)
            ->fillForm([
                'name' => 'Flagship Foldables',
                'slug' => 'flagship-foldables',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('categories', [
            'name' => 'Flagship Foldables',
            'slug' => 'flagship-foldables',
        ]);
    }

    public function test_category_creation_validates_required_fields(): void
    {
        Livewire::test(CreateCategory::class)
            ->fillForm([
                'name' => null,
                'slug' => null,
            ])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required', 'slug' => 'required']);
    }

    public function test_can_edit_category(): void
    {
        $category = Category::factory()->create(['name' => 'Old Category Name']);

        Livewire::test(EditCategory::class, ['record' => $category->getRouteKey()])
            ->fillForm([
                'name' => 'Updated Category Name',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertEquals('Updated Category Name', $category->fresh()->name);
    }

    public function test_can_delete_category(): void
    {
        $category = Category::factory()->create();

        Livewire::test(EditCategory::class, ['record' => $category->getRouteKey()])
            ->callAction('delete');

        $this->assertModelMissing($category);
    }
}
