<?php

declare(strict_types=1);

namespace App\Enums\Catalog;

enum RecipeType: string
{
    case Base = 'base';
    case Sub = 'sub';
}
