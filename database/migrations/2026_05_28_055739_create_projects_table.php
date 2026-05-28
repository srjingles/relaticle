<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table): void {
            $table->ulid('id')->primary();

            $table->foreignUlid('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignUlid('creator_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignUlid('company_id')->nullable()->constrained('companies')->onDelete('set null');
            $table->foreignUlid('account_owner_id')->nullable()->constrained('users')->onDelete('set null');

            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('status', 50)->default('planning');
            $table->unsignedInteger('budget')->nullable();
            $table->string('color', 20)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('due_date')->nullable();
            $table->string('creation_source', 50);

            $table->unsignedBigInteger('order_column')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['team_id', 'deleted_at', 'creation_source', 'created_at'], 'idx_projects_team_activity');
        });
    }
};
