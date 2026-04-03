<?php

namespace Alhosam\FilamentFormBuilder\Filament\Resources\Forms;

use Alhosam\FilamentFormBuilder\Filament\Resources\Forms\Pages\CreateForm;
use Alhosam\FilamentFormBuilder\Filament\Resources\Forms\Pages\EditForm;
use Alhosam\FilamentFormBuilder\Filament\Resources\Forms\Pages\ListForms;
use Alhosam\FilamentFormBuilder\Filament\Resources\Forms\Pages\ManageFormWorkspace;
use Alhosam\FilamentFormBuilder\Models\Form;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class FormResource extends Resource
{
    protected static ?string $model = Form::class;

    protected static ?string $slug = 'form-builder/forms';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return (string) config('filament-form-builder.navigation_group', 'Forms');
    }

    public static function getNavigationLabel(): string
    {
        return 'Forms';
    }

    public static function getModelLabel(): string
    {
        return 'Form';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Forms';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Basic information')
                ->schema([
                    TextInput::make('name.ar')
                        ->label('Name (AR)')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, $set, $get): void {
                            if (blank($get('slug'))) {
                                $set('slug', Str::slug((string) $state));
                            }
                        }),
                    TextInput::make('name.en')
                        ->label('Name (EN)')
                        ->maxLength(255),
                    TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),
                    TextInput::make('description.ar')
                        ->label('Description (AR)'),
                    TextInput::make('description.en')
                        ->label('Description (EN)'),
                    Select::make('status')
                        ->label('Status')
                        ->options([
                            'draft' => 'Draft',
                            'published' => 'Published',
                            'archived' => 'Archived',
                        ])
                        ->default(config('filament-form-builder.default_status', 'draft'))
                        ->required(),
                    Toggle::make('is_active')
                        ->label('Active')
                        ->default(true),
                    TextInput::make('submit_label.ar')
                        ->label('Submit label (AR)'),
                    TextInput::make('submit_label.en')
                        ->label('Submit label (EN)'),
                ])
                ->columns(2),
            Section::make('Availability')
                ->schema([
                    Toggle::make('availability.requires_auth')
                        ->label('Require authenticated user')
                        ->default(false),
                    Toggle::make('availability.accept_guest_submissions')
                        ->label('Allow guest submissions')
                        ->default(true),
                    DateTimePicker::make('availability.available_from')
                        ->label('Available from'),
                    DateTimePicker::make('availability.available_until')
                        ->label('Available until'),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->formatStateUsing(fn ($state, Form $record): string => $record->displayName())
                    ->searchable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->badge()
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
                TextColumn::make('fields_count')
                    ->counts('fields')
                    ->label('Fields')
                    ->badge(),
                TextColumn::make('submissions_count')
                    ->counts('submissions')
                    ->label('Submissions')
                    ->badge(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->recordActions([
                Action::make('workspace')
                    ->label('Workspace')
                    ->url(fn (Form $record): string => static::getUrl('workspace', ['record' => $record])),
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListForms::route('/'),
            'create' => CreateForm::route('/create'),
            'edit' => EditForm::route('/{record}/edit'),
            'workspace' => ManageFormWorkspace::route('/{record}/workspace'),
        ];
    }
}
