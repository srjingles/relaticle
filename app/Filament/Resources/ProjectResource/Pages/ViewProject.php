<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\CompanyResource;
use App\Filament\Resources\ProjectResource;
use App\Filament\Resources\ProjectResource\RelationManagers\NotesRelationManager;
use App\Filament\Resources\ProjectResource\RelationManagers\PeopleRelationManager;
use App\Models\Project;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\ColorEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Js;
use Relaticle\CustomFields\Facades\CustomFields;

final class ViewProject extends ViewRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->icon('heroicon-o-pencil-square')
                ->label(__('filament/resources/project.pages.view.actions.edit.label')),
            ActionGroup::make([
                ActionGroup::make([
                    Action::make('copyPageUrl')
                        ->label(__('filament/resources/project.pages.view.actions.copy_page_url.label'))
                        ->icon('heroicon-o-clipboard-document')
                        ->action(function (Project $record): void {
                            $jsUrl = Js::from(ProjectResource::getUrl('view', [$record]));
                            $this->js("
                            navigator.clipboard.writeText({$jsUrl}).then(() => {
                                new FilamentNotification()
                                    .title('URL copied to clipboard')
                                    .success()
                                    .send()
                            })
                        ");
                        }),
                    Action::make('copyRecordId')
                        ->label(__('filament/resources/project.pages.view.actions.copy_record_id.label'))
                        ->icon('heroicon-o-clipboard-document')
                        ->action(function (Project $record): void {
                            $jsId = Js::from((string) $record->getKey());
                            $this->js("
                            navigator.clipboard.writeText({$jsId}).then(() => {
                                new FilamentNotification()
                                    .title('Record ID copied to clipboard')
                                    .success()
                                    .send()
                            })
                        ");
                        }),
                ])->dropdown(false),
                DeleteAction::make(),
            ]),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make()->schema([
                Grid::make(3)->schema([
                    TextEntry::make('name')
                        ->label(__('filament/resources/project.pages.view.infolist.fields.name.label'))
                        ->size('xl')
                        ->grow(true),
                    TextEntry::make('status')
                        ->label(__('filament/resources/project.pages.view.infolist.fields.status.label'))
                        ->badge()
                        ->grow(false),
                    TextEntry::make('company.name')
                        ->label(__('filament/resources/project.pages.view.infolist.fields.company.label'))
                        ->color('primary')
                        ->url(fn (Project $record): ?string => $record->company
                        ? CompanyResource::getUrl('view', [$record->company])
                        : null)
                        ->grow(false),
                ]),
                Grid::make(3)->schema([
                    TextEntry::make('due_date')
                        ->label(__('filament/resources/project.pages.view.infolist.fields.due_date.label'))
                        ->icon('heroicon-o-calendar')
                        ->date(),
                    TextEntry::make('start_date')
                        ->label(__('filament/resources/project.pages.view.infolist.fields.start_date.label'))
                        ->icon('heroicon-o-calendar')
                        ->date(),
                    TextEntry::make('end_date')
                        ->label(__('filament/resources/project.pages.view.infolist.fields.end_date.label'))
                        ->icon('heroicon-o-calendar')
                        ->date(),
                    TextEntry::make('budget')
                        ->label(__('filament/resources/project.pages.view.infolist.fields.budget.label'))
                        ->icon('heroicon-o-currency-euro')
                        ->formatStateUsing(fn (?int $state): string => $state !== null
                            ? number_format($state / 100, 2).' €'
                            : '—'),
                    TextEntry::make('accountOwner.name')
                        ->label(__('filament/resources/project.pages.view.infolist.fields.account_owner.label'))
                        ->icon('heroicon-o-user'),
                    ColorEntry::make('color')
                        ->label(__('filament/resources/project.pages.view.infolist.fields.color.label')),
                ]),
                TextEntry::make('description')
                    ->label(__('filament/resources/project.pages.view.infolist.fields.description.label'))
                    ->columnSpanFull(),
                CustomFields::infolist()->forSchema($schema)->build()->columnSpanFull(),
            ])->columnSpanFull(),
            Section::make()->schema([
                Flex::make([
                    TextEntry::make('creator.name')
                        ->label(__('filament/resources/project.pages.view.infolist.fields.creator.label'))
                        ->icon('heroicon-o-user'),
                    TextEntry::make('created_at')
                        ->label(__('filament/resources/project.pages.view.infolist.fields.created_at.label'))
                        ->icon('heroicon-o-clock')
                        ->dateTime(),
                    TextEntry::make('updated_at')
                        ->label(__('filament/resources/project.pages.view.infolist.fields.updated_at.label'))
                        ->icon('heroicon-o-clock')
                        ->dateTime(),
                ]),
            ])->columnSpanFull(),
        ]);
    }

    public function getRelationManagers(): array
    {
        return [
            PeopleRelationManager::class,
            NotesRelationManager::class,
        ];
    }
}
