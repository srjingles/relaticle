<?php

declare(strict_types=1);

use App\Enums\ProjectStatus;
use App\Filament\Resources\ProjectResource;
use App\Filament\Resources\ProjectResource\Pages\ListProjects;
use App\Filament\Resources\ProjectResource\Pages\ViewProject;
use App\Models\Company;
use App\Models\Project;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;

mutates(ProjectResource::class);

beforeEach(function () {
    $this->user = User::factory()->withTeam()->create();
    $this->actingAs($this->user);
    $this->team = $this->user->currentTeam;
    Filament::setTenant($this->team);
});

it('can render the index page', function (): void {
    livewire(ListProjects::class)
        ->assertOk();
});

it('can render the view page', function (): void {
    $record = Project::factory()->recycle([$this->user, $this->team])->create();

    livewire(ViewProject::class, ['record' => $record->getKey()])
        ->assertOk();
});

it('can render `:dataset` column', function (string $column): void {
    livewire(ListProjects::class)
        ->assertCanRenderTableColumn($column);
})->with(['name', 'company.name', 'status', 'due_date', 'created_at', 'updated_at']);

it('cannot render `:dataset` column by default', function (string $column): void {
    livewire(ListProjects::class)
        ->assertCanNotRenderTableColumn($column);
})->with(['deleted_at', 'color']);

it('has `:dataset` column', function (string $column): void {
    livewire(ListProjects::class)
        ->assertTableColumnExists($column);
})->with(['name', 'company.name', 'status', 'due_date', 'accountOwner.name', 'creator.name', 'deleted_at', 'created_at', 'updated_at']);

it('can sort `:dataset` column', function (string $column): void {
    $records = Project::factory(3)->recycle([$this->user, $this->team])->create();

    $sortingKey = data_get($records->first(), $column) instanceof BackedEnum
        ? fn (Model $record) => data_get($record, $column)->value
        : $column;

    livewire(ListProjects::class)
        ->sortTable($column)
        ->assertCanSeeTableRecords($records->sortBy($sortingKey), inOrder: true)
        ->sortTable($column, 'desc')
        ->assertCanSeeTableRecords($records->sortByDesc($sortingKey), inOrder: true);
})->with(['name', 'created_at', 'updated_at']);

it('can search by name', function (): void {
    $records = Project::factory(3)->recycle([$this->user, $this->team])->create();
    $search = $records->first()->name;

    $visibleRecords = $records->filter(fn (Model $record) => $record->name === $search);

    livewire(ListProjects::class)
        ->searchTable($search)
        ->assertCanSeeTableRecords($visibleRecords)
        ->assertCountTableRecords($visibleRecords->count());
});

it('cannot display trashed records by default', function (): void {
    $records = Project::factory()->count(4)->recycle([$this->user, $this->team])->create();
    $trashedRecords = Project::factory()->trashed()->count(6)->recycle([$this->user, $this->team])->create();

    livewire(ListProjects::class)
        ->assertCanSeeTableRecords($records)
        ->assertCanNotSeeTableRecords($trashedRecords)
        ->assertCountTableRecords(4);
});

it('can paginate records', function (): void {
    $records = Project::factory(20)->recycle([$this->user, $this->team])->create();

    $sortedRecords = Project::query()
        ->whereIn('id', $records->pluck('id'))
        ->orderBy('created_at', 'desc')
        ->get();

    livewire(ListProjects::class)
        ->assertCanSeeTableRecords($sortedRecords->take(10), inOrder: true)
        ->call('gotoPage', 2)
        ->assertCanSeeTableRecords($sortedRecords->skip(10)->take(10), inOrder: true);
});

it('can bulk delete records', function (): void {
    $records = Project::factory(5)->recycle([$this->user, $this->team])->create();

    livewire(ListProjects::class)
        ->assertCanSeeTableRecords($records)
        ->selectTableRecords($records)
        ->callAction([['name' => 'delete', 'context' => ['table' => true, 'bulk' => true]]])
        ->assertNotified()
        ->assertCanNotSeeTableRecords($records);

    $this->assertSoftDeleted($records);
});

it('can create a project', function (): void {
    $company = Company::factory()->recycle([$this->user, $this->team])->create();

    livewire(ListProjects::class)
        ->callAction('create', data: [
            'name' => 'Website Redesign',
            'company_id' => $company->id,
            'status' => ProjectStatus::Active->value,
        ])
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas(Project::class, [
        'name' => 'Website Redesign',
        'team_id' => $this->team->id,
        'company_id' => $company->id,
    ]);
});

it('can edit a project', function (): void {
    $record = Project::factory()->recycle([$this->user, $this->team])->create();

    livewire(ListProjects::class)
        ->callAction(TestAction::make('edit')->table($record), data: [
            'name' => 'Updated Project',
        ])
        ->assertHasNoActionErrors();

    expect($record->refresh()->name)->toBe('Updated Project');
});

it('can delete a project', function (): void {
    $record = Project::factory()->recycle([$this->user, $this->team])->create();

    livewire(ListProjects::class)
        ->callAction(TestAction::make('delete')->table($record));

    $this->assertSoftDeleted($record);
});

it('validates name is required on create', function (): void {
    livewire(ListProjects::class)
        ->callAction('create', data: [
            'name' => null,
        ])
        ->assertHasActionErrors(['name' => 'required']);
});

it('has `:dataset` filter', function (string $filter): void {
    livewire(ListProjects::class)
        ->assertTableFilterExists($filter);
})->with(['status', 'creation_source', 'trashed']);

it('sets creator_id and team_id when creating a project', function (): void {
    livewire(ListProjects::class)
        ->callAction('create', data: [
            'name' => 'Observer Test Project',
            'status' => ProjectStatus::Planning->value,
        ])
        ->assertHasNoActionErrors();

    $project = Project::query()->where('name', 'Observer Test Project')->first();

    expect($project->creator_id)->toBe($this->user->id)
        ->and($project->team_id)->toBe($this->team->id);
});

it('generates slug from name on create', function (): void {
    livewire(ListProjects::class)
        ->callAction('create', data: [
            'name' => 'My New Project',
            'status' => ProjectStatus::Planning->value,
        ])
        ->assertHasNoActionErrors();

    $project = Project::query()->where('name', 'My New Project')->first();

    expect($project->slug)->toBe('my-new-project');
});

it('authorizes team member to view and update own team project', function (): void {
    $record = Project::factory()->recycle([$this->user, $this->team])->create();

    expect($this->user->can('view', $record))->toBeTrue()
        ->and($this->user->can('update', $record))->toBeTrue()
        ->and($this->user->can('delete', $record))->toBeTrue();
});

it('denies non-team-member from viewing another team project', function (): void {
    $otherUser = User::factory()->withTeam()->create();
    $otherTeam = $otherUser->currentTeam;

    $this->actingAs($otherUser);
    $record = Project::factory()->for($otherTeam)->create();
    $this->actingAs($this->user);

    expect($this->user->can('view', $record))->toBeFalse()
        ->and($this->user->can('update', $record))->toBeFalse()
        ->and($this->user->can('delete', $record))->toBeFalse();
});
