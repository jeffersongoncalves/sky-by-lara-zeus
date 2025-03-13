<?php

namespace LaraZeus\Sky\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;

class UniqueTranslationRule implements DataAwareRule, ValidationRule
{
    /**
     * All of the data under validation.
     *
     * @var array<string, mixed>
     */
    protected array $data = [];

    protected ?string $model;

    protected bool $ignoreRecord = true;

    protected ?Model $record;

    public function __construct(?string $model, ?Model $record, bool $ignoreRecord = true)
    {
        $this->model = $model;
        $this->ignoreRecord = $ignoreRecord;
        $this->record = $record;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $locale = $this->data['activeLocale'] ?? config('app.locale');
        $model = app($this->model);
        $column = str($attribute)->explode('.')->last();

        if (
            $model::query()
                ->when($this->record !== null && $this->ignoreRecord, function ($q) use ($model) {
                    // @phpstan-ignore-next-line
                    return $q->where($model->getKeyName(), '!=', $this->record?->id);
                })
                ->where("{$column}->{$locale}", $value)
                ->exists()
        ) {
            $fail(
                __(
                    'This :attribute already exists in the selected language: :locale',
                    [
                        'attribute' => $column,
                        'locale' => strtoupper($locale),
                    ]
                )
            );
        }
    }

    /**
     * Set the data under validation.
     *
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }
}
