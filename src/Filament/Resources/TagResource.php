<?php

namespace LaraZeus\Sky\Filament\Resources;

use Closure;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use LaraZeus\Sky\Filament\Resources\TagResource\Pages;
use LaraZeus\Sky\Models\Tag;
use LaraZeus\Sky\SkyPlugin;
use Livewire\Component as Livewire;

class TagResource extends SkyResource
{
    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?int $navigationSort = 5;

    public static function getModel(): string
    {
        return SkyPlugin::get()->getModel('Tag');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label(__('Tag.Name'))
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, $state) {
                                $set('slug', Str::slug($state));
                            }),
                        TextInput::make('slug')
                            ->rules([
                                function (?Tag $record, Livewire $livewire): Closure {
                                    return static function (string $attribute, $value, Closure $fail) use (
                                        $record,
                                        $livewire
                                    ) {
                                        if (Tag::query()
                                            ->where('id', '!=', $record?->id)
                                            // @phpstan-ignore-next-line
                                            ->where("slug->{$livewire->activeLocale}", $value)
                                            ->exists()
                                        ) {
                                            $fail('The :attribute is already exist.');
                                        }
                                    };
                                },
                            ])
                            /*->unique(
                                //column: 'slug.en',
                                ignorable: fn (?Model $record): ?Model => $record,
                                modifyRuleUsing: function (Unique $rule) {
                                    return $rule
                                        ->where('slug->en', '111')
                                        ;
                                }
                            )*/
                            ->required()
                            ->maxLength(255),
                        Select::make('type')
                            ->columnSpan(2)
                            ->options(SkyPlugin::get()->getTagTypes()),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->toggleable()->searchable()->sortable(),
                TextColumn::make('type')->toggleable()->searchable()->sortable(),
                TextColumn::make('slug')->toggleable()->searchable()->sortable(),
                TextColumn::make('items_count')
                    ->toggleable()
                    ->getStateUsing(
                        fn (Tag $record): int => method_exists($record, $record->type)
                            ? $record->{$record->type}()->count()
                            : 0
                    ),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options(SkyPlugin::get()->getTagTypes())
                    ->label(__('type')),
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make('edit'),
                    DeleteAction::make('delete'),
                ]),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTags::route('/'),
            'create' => Pages\CreateTag::route('/create'),
            'edit' => Pages\EditTag::route('/{record}/edit'),
        ];
    }

    public static function getLabel(): string
    {
        return __('Tag');
    }

    public static function getPluralLabel(): string
    {
        return __('Tags');
    }

    public static function getNavigationLabel(): string
    {
        return __('Tags');
    }
}
