<?php

namespace LaraZeus\Sky;

use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Str;

trait Configuration
{
    protected ?array $libraryTypes = [
        'FILE' => 'File',
        'IMAGE' => 'Image',
        'VIDEO' => 'Video',
    ];

    protected ?array $tagTypes = [
        'tag' => 'Tag',
        'category' => 'Category',
        'library' => 'Library',
        'faq' => 'Faq',
    ];

    protected bool $hasPostResource = true;

    protected bool $hasPageResource = true;

    protected bool $hasFaqResource = true;

    protected bool $hasLibraryResource = true;

    protected bool $hasTagResource = true;

    protected bool $hasNavigationResource = true;

    protected array $itemTypes = [];

    protected array | Closure $extraFields = [];

    private ?array $translatedLibraryTypes = null;

    private ?array $translatedTagTypes = null;

    public function postResource(bool $condition = true): static
    {
        $this->hasPostResource = $condition;

        return $this;
    }

    public function hasPostResource(): bool
    {
        return $this->hasPostResource;
    }

    public function pageResource(bool $condition = true): static
    {
        $this->hasPageResource = $condition;

        return $this;
    }

    public function hasPageResource(): bool
    {
        return $this->hasPageResource;
    }

    public function faqResource(bool $condition = true): static
    {
        $this->hasFaqResource = $condition;

        return $this;
    }

    public function hasFaqResource(): bool
    {
        return $this->hasFaqResource;
    }

    public function libraryResource(bool $condition = true): static
    {
        $this->hasLibraryResource = $condition;

        return $this;
    }

    public function hasLibraryResource(): bool
    {
        return $this->hasLibraryResource;
    }

    public function tagResource(bool $condition = true): static
    {
        $this->hasTagResource = $condition;

        return $this;
    }

    public function hasTagResource(): bool
    {
        return $this->hasTagResource;
    }

    public function navigationResource(bool $condition = true): static
    {
        $this->hasNavigationResource = $condition;

        return $this;
    }

    public function hasNavigationResource(): bool
    {
        return $this->hasNavigationResource;
    }

    public function libraryTypes(array $types): static
    {
        $this->libraryTypes = $types;

        return $this;
    }

    public function getLibraryTypes(): ?array
    {
        if ($this->translatedLibraryTypes === null && $this->libraryTypes && function_exists('__')) {
            $this->translatedLibraryTypes = array_map('__', $this->libraryTypes);
        }

        return $this->translatedLibraryTypes ?? $this->libraryTypes;
    }

    public function tagTypes(array $types): static
    {
        $this->tagTypes = $types;

        return $this;
    }

    public function getTagTypes(): ?array
    {
        if ($this->translatedTagTypes === null && $this->tagTypes && function_exists('__')) {
            $this->translatedTagTypes = array_map('__', $this->tagTypes);
        }

        return $this->translatedTagTypes ?? $this->tagTypes;
    }

    public function itemType(string $name, array | Closure $fields, ?string $slug = null): static
    {
        $this->itemTypes[$slug ?? Str::slug($name)] = [
            'name' => $name,
            'fields' => $fields,
        ];

        return $this;
    }

    public function withExtraFields(array | Closure $schema): static
    {
        $this->extraFields = $schema;

        return $this;
    }

    public function getExtraFields(): array | Closure
    {
        return $this->extraFields;
    }

    public function getItemTypes(): array
    {
        return array_merge(
            [
                'external-link' => [
                    'name' => __('zeus-sky::filament-navigation.attributes.external-link'),
                    'fields' => [
                        TextInput::make('url')
                            ->label(__('zeus-sky::filament-navigation.attributes.url'))
                            ->required(),
                        Select::make('target')
                            ->label(__('zeus-sky::filament-navigation.attributes.target'))
                            ->options([
                                '' => __('zeus-sky::filament-navigation.select-options.same-tab'),
                                '_blank' => __('zeus-sky::filament-navigation.select-options.new-tab'),
                            ])
                            ->default('')
                            ->selectablePlaceholder(false),
                    ],
                ],
            ],
            $this->itemTypes
        );
    }
}
