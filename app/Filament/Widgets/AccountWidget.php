<?php

namespace App\Filament\Widgets;

use Filament\Widgets\AccountWidget as BaseWidget;

class AccountWidget extends BaseWidget
{
    /**
     * @var int | string | array<string, int | null>
     */
    protected int | string | array $columnSpan = 'full';
}
