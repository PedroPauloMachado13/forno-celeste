<?php

declare(strict_types=1);

namespace App\Enums\Catalog;

enum CmvHealthStatus: string
{
    case Healthy = 'healthy';
    case Tight = 'tight';
    case Critical = 'critical';
}
