<?php

namespace Agenciafmd\Support\Providers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\ServiceProvider;

class EloquentServiceProvider extends ServiceProvider
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
            function (string $label = 'name', string $value = 'id', bool $disabled = false) {
                $fields = [$value, $label];
                if ($disabled) {
                    $fields[] = 'is_active';
                }

                return $this->select($fields)
                    ->get()
                    ->map(function ($item) use ($label, $value, $disabled) {
                        $option = [
                            'label' => $item->{$label},
                            'value' => $item->{$value},
                        ];

                        if ($disabled) {
                            $option['disabled'] = !$item->is_active;
                        }

                        return $option;
                    })
                    ->prepend([
                        'label' => '-',
                        'value' => '',
                        'disabled' => false,
                    ])
                    ->toArray();
            });

        Builder::macro('toSimpleSelectOptions', function () {
            return collect($this->toSelectOptions())
                ->mapWithKeys(function ($item) {
                    return [
                        $item['value'] => $item['label'],
                    ];
                })
                ->toArray();
        });
    }
}
