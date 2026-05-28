<?php

declare(strict_types=1);

namespace App\Actions\Project;

use App\Models\Project;
use App\Models\User;

final readonly class DeleteProject
{
    public function execute(User $user, Project $project): void
    {
        abort_unless($user->can('delete', $project), 403);

        $project->delete();
    }
}
