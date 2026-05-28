<?php

declare(strict_types=1);

namespace App\Actions\Project;

use App\Enums\CreationSource;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

final readonly class CreateProject
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $user, array $data, CreationSource $source = CreationSource::WEB): Project
    {
        abort_unless($user->can('create', Project::class), 403);

        $attributes = Arr::only($data, [
            'name', 'description', 'status', 'budget', 'color',
            'start_date', 'end_date', 'due_date', 'company_id',
            'account_owner_id', 'custom_fields',
        ]);
        $attributes['creation_source'] = $source;

        $project = DB::transaction(fn (): Project => Project::query()->create($attributes));

        return $project->load('customFieldValues.customField.options');
    }
}
