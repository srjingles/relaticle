<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\CreationSource;
use App\Enums\ProjectStatus;
use App\Filament\Resources\ProjectResource\Pages\ListProjects;
use App\Filament\Resources\ProjectResource\Pages\ViewProject;
use App\Filament\Resources\ProjectResource\RelationManagers\NotesRelationManager;
use App\Filament\Resources\ProjectResource\RelationManagers\PeopleRelationManager;
use App\Models\Project;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Override;
use Relaticle\CustomFields\Facades\CustomFields;

final class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = null;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-briefcase';

    protected static ?int $navigationSort = 4;

    protected static string|\UnitEnum|null $navigationGroup = null;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                Grid::make(2)->schema([
                    TextInput::make('name')
                        ->label(__('filament/resources/project.fields.name.label'))
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->columnSpan(1),
                    TextInput::make('slug')
                        ->label(__('filament/resources/project.fields.slug.label'))
                        ->maxLength(255)
                        ->columnSpan(1),
                    Select::make('company_id')
                        ->label(__('filament/resources/project.fields.company_id.label'))
                        ->relationship('company', 'name')
                        ->nullable()
                        ->preload()
                        ->searchable()
                        ->columnSpan(1),
                    Select::make('account_owner_id')
                        ->label(__('filament/resources/project.fields.account_owner_id.label'))
                        ->relationship('accountOwner', 'name')
                        ->nullable()
                        ->preload()
                        ->searchable()
                        ->columnSpan(1),
                    Select::make('status')
                        ->label(__('filament/resources/project.fields.status.label'))
                        ->options(ProjectStatus::class)
                        ->required()
                        ->columnSpan(1),
                    TextInput::make('budget')
                        ->label(__('filament/resources/project.fields.budget.label'))
                        ->numeric()
                        ->minValue(0)
                        ->suffix(__('cents'))
                        ->nullable()
                        ->columnSpan(1),
                    ColorPicker::make('color')
                        ->label(__('filament/resources/project.fields.color.label'))
                        ->nullable()
                        ->columnSpan(1),
                    DatePicker::make('due_date')
                        ->label(__('filament/resources/project.fields.due_date.label'))
                        ->nullable()
                        ->columnSpan(1),
                    DatePicker::make('start_date')
                        ->label(__('filament/resources/project.fields.start_date.label'))
                        ->nullable()
                        ->columnSpan(1),
                    DatePicker::make('end_date')
                        ->label(__('filament/resources/project.fields.end_date.label'))
                        ->nullable()
                        ->columnSpan(1),
                    Textarea::make('description')
                        ->label(__('filament/resources/project.fields.description.label'))
                        ->nullable()
                        ->columnSpanFull(),
                ]),
                CustomFields::form()->build()->columnSpanFull()->columns(1),
            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('filament/resources/project.fields.name.label'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('company.name')
                    ->label(__('filament/resources/project.fields.company.label'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->label(__('filament/resources/project.fields.status.label'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('due_date')
                    ->label(__('filament/resources/project.fields.due_date.label'))
                    ->date()
                    ->sortable()
                    ->toggleable(),
                ColorColumn::make('color')
                    ->label(__('filament/resources/project.fields.color.label'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('accountOwner.name')
                    ->label(__('filament/resources/project.fields.account_owner.label'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('creator.name')
                    ->label(__('filament/resources/project.fields.creator.label'))
                    ->sortable()
                    ->toggleable()
                    ->getStateUsing(fn (Project $record): string => $record->created_by),
                TextColumn::make('deleted_at')
                    ->label(__('filament/resources/project.fields.deleted_at.label'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('filament/resources/project.fields.created_at.label'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('filament/resources/project.fields.updated_at.label'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label(__('filament/resources/project.fields.status.label'))
                    ->options(ProjectStatus::class)
                    ->multiple(),
                SelectFilter::make('creation_source')
                    ->label(__('filament/resources/project.fields.creation_source.label'))
                    ->options(CreationSource::class)
                    ->multiple(),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    RestoreAction::make(),
                    DeleteAction::make(),
                    ForceDeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    RestoreBulkAction::make(),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            PeopleRelationManager::class,
            NotesRelationManager::class,
        ];
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListProjects::route('/'),
            'view' => ViewProject::route('/{record}'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('filament/resources/project.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament/resources/project.plural_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/resources/project.navigation_label');
    }

    public static function getNavigationGroup(): string
    {
        return __('filament/navigation.groups.workspace');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['team', 'customFieldValues.customField.options'])
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
