<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Offer\CloseFilledOffers;
use App\Actions\Offer\ExpireOffers;
use Illuminate\Console\Command;

class DeactivateOffersCommand extends Command
{
    protected $signature = "offers:deactivate";
    protected $description = "Deactivate published offers past their end date or with no remaining spots";

    public function handle(CloseFilledOffers $closeFilledOffers, ExpireOffers $expireOffers): void
    {
        $closed = $closeFilledOffers->execute();
        $expired = $expireOffers->execute();

        $this->info("Filled offers closed: {$closed}");
        $this->info("Outdated offers expired: {$expired}");
    }
}
