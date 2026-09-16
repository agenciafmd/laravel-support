<?php

declare(strict_types=1);

namespace Agenciafmd\Support\Providers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

final class EloquentServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMacros();
    }

    public function register(): void
    {
        //
    }

    private function loadMacros(): void
    {
        Builder::macro('toSelectOptions',
            function (string $label = 'name', string $value = 'id', bool $disabled = false): array {
                $fields = [$value, $label];
                if ($disabled) {
                    $fields[] = 'is_active';
                }

                return $this->select($fields)
                    ->get()
                    ->map(function (Model $item) use ($label, $value, $disabled): array {
                        $option = [
                            'label' => $item->{$label},
                            'value' => $item->{$value},
                        ];

                        if ($disabled) {
                            $option['disabled'] = ! $item->is_active;
                        }

                        return $option;
                    })
                    ->prepend([
                        'label' => '-',
                        'value' => '',
                        'disabled' => false,
                    ])
                    ->all();
            });

        Builder::macro('toSimpleSelectOptions', fn (): array => collect($this->toSelectOptions())
            ->mapWithKeys(fn (array $item): array => [
                $item['value'] => $item['label'],
            ])
            ->toArray());
    }
}
