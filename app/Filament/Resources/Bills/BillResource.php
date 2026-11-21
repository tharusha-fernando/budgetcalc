<?php

namespace App\Filament\Resources\Bills;

use App\Filament\Resources\Bills\Pages\CreateBill;
use App\Filament\Resources\Bills\Pages\EditBill;
use App\Filament\Resources\Bills\Pages\ListBills;
use App\Filament\Resources\Bills\Pages\ViewBill;
use App\Filament\Resources\Bills\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\Bills\Schemas\BillForm;
use App\Filament\Resources\Bills\Schemas\BillInfolist;
use App\Filament\Resources\Bills\Tables\BillsTable;
use App\Filament\Resources\Bills\Widgets\ProjectBudgetOverview;
use App\Models\Bill;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class BillResource extends Resource
{
    protected static ?string $model = Bill::class;

    protected static ?string $recordTitleAttribute = 'vendor';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Expenses';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return BillForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BillInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BillsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            ProjectBudgetOverview::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBills::route('/'),
            'create' => CreateBill::route('/create'),
            'view' => ViewBill::route('/{record}'),
            'edit' => EditBill::route('/{record}/edit'),
        ];
    }
}
