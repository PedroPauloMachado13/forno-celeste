<?php

declare(strict_types=1);

namespace App\Exceptions\Catalog;

use App\Models\Packaging;
use RuntimeException;

final class PackagingInUseException extends RuntimeException
{
    public static function for(Packaging $packaging): self
    {
        return new self(
            "A embalagem \"{$packaging->name}\" não pode ser excluída porque está vinculada a produtos.",
        );
    }
}
