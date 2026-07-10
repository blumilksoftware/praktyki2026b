<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Offer\ExpireOffers;
use Illuminate\Console\Command;

class ExpireOffersCommand extends Command
{
    protected $signature = "offers:expire";
    protected $description = "Mark published offers past their end date as expired";

    public function handle(ExpireOffers $expireOffers): int
    {
        $expireOffers->execute();

        return self::SUCCESS;
    }
}
