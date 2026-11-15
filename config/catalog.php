<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Card Machine Fee Percent
    |--------------------------------------------------------------------------
    |
    | The percentage fee charged by the card machine on each sale.
    | Used by the CMV pricing engine to suggest prices.
    |
    */
    'card_machine_fee_percent' => (float) env('CATALOG_CARD_MACHINE_FEE_PERCENT', 3.15),

];
