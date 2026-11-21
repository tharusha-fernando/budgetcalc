<?php

namespace App\Filament\Resources\Projects\RelationManagers;

use App\Enums\PaymentMethod;
use App\Filament\Forms\Components\AttachmentsUpload;
use App\Models\Project;
use App\Models\RestorationPayment;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class RestorationPaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'restorationPayments';

    protected static ?string $title = 'Payments to guy';

    protected static ?string $recordTitleAttribute = 'amount';

    public function isReadOnly(): bool
    {
        return false;
    }

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        if (! $ownerRecord instanceof Project) {
            return null;
        }

        $count = $ownerRecord->restorationPayments()->count();

        return $count > 0 ? (string) $count : null;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->prefix('₹')
                    ->minValue(0.01),
                Select::make('method')
                    ->options(PaymentMethod::class)
                    ->default(PaymentMethod::Cash)
                    ->required(),
                DatePicker::make('paid_on')
                    ->label('Paid on')
                    ->required()
                    ->default(now()),
                Textarea::make('notes')
                    ->rows(2)
                    ->columnSpanFull(),
                AttachmentsUpload::make(directory: 'payments/attachments')
                    ->helperText('Payment proof photos or PDFs. On your phone, tap browse → Take Photo (or gallery). Multiple files OK.'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('amount')
            ->columns([
                TextColumn::make('paid_on')
                    ->label('Paid on')
                    ->date()
                    ->sortable(),
                TextColumn::make('amount')
                    ->money('INR')
                    ->sortable()
                    ->summarize(
                        Sum::make()
                            ->money('INR')
                            ->label('Total paid'),
                    ),
                TextColumn::make('method')
                    ->badge(),
                TextColumn::make('notes')
                    ->placeholder('—')
                    ->wrap()
                    ->limit(40),
                TextColumn::make('attachments_count')
                    ->label('Files')
                    ->state(fn (RestorationPayment $record): int => count($record->attachments ?? []))
                    ->alignEnd(),
            ])
            ->defaultSort('paid_on', 'desc')
            ->filters([
                SelectFilter::make('method')
                    ->options(PaymentMethod::class),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Record payment')
                    ->modalHeading('Record payment to restoration guy'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
