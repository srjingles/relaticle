<?php

declare(strict_types=1);

namespace App\Actions\Project;

use App\Models\Project;
use App\Models\User;
use App\Support\CustomFieldMerger;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

final readonly class UpdateProject
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $user, Project $project, array $data): Project
    {
        abort_unless($user->can('update', $project), 403);

        $attributes = Arr::only($data, [
            'name', 'slug', 'description', 'status', 'budget', 'color',
            'start_date', 'end_date', 'due_date', 'company_id',
            'account_owner_id', 'custom_fields',
        ]);

        $attributes = CustomFieldMerger::merge($project, $attributes);

        return DB::transaction(function () use ($project, $attributes): Project {
            $project->update($attributes);

            return $project->refresh()->load('customFieldValues.customField.options');
        });
    }
}
