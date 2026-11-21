<?php

namespace App\Filament\Resources\Projects\RelationManagers;

use App\Filament\Forms\Components\BillAttachmentsUpload;
use App\Filament\Resources\Bills\BillResource;
use App\Models\Bill;
use App\Models\Project;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class BillsRelationManager extends RelationManager
{
    protected static string $relationship = 'bills';

    protected static ?string $title = 'Bills';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        if (! $ownerRecord instanceof Project) {
            return null;
        }

        $count = $ownerRecord->bills()->count();

        return $count > 0 ? (string) $count : null;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('vendor')
                    ->label('Vendor / store')
                    ->required()
                    ->maxLength(255),
                TextInput::make('bill_number')
                    ->label('Bill / invoice #')
                    ->maxLength(255),
                DatePicker::make('bill_date')
                    ->required()
                    ->default(now()),
                TextInput::make('stated_total')
                    ->label('Printed bill total')
                    ->numeric()
                    ->prefix('₹'),
                Textarea::make('notes')
                    ->rows(2)
                    ->columnSpanFull(),
                BillAttachmentsUpload::make(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('vendor')
            ->columns([
                TextColumn::make('vendor')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('bill_number')
                    ->label('Bill #')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('bill_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('stated_total')
                    ->label('Printed total')
                    ->money('INR')
                    ->placeholder('—'),
                TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Items'),
                TextColumn::make('attachments')
                    ->label('Files')
                    ->formatStateUsing(fn (?array $state): string => (string) count($state ?? []))
                    ->alignEnd(),
                TextColumn::make('chargeable_total')
                    ->label('Chargeable')
                    ->money('INR')
                    ->state(fn (Bill $record): float => $record->chargeableTotal()),
            ])
            ->defaultSort('bill_date', 'desc')
            ->headerActions([
                CreateAction::make()
                    ->label('Add bill'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn (Bill $record): string => BillResource::getUrl('view', ['record' => $record])),
                EditAction::make()
                    ->url(fn (Bill $record): string => BillResource::getUrl('edit', ['record' => $record])),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
