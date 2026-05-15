<?php

namespace App\Console\Commands;

use App\Models\OrderItem;
use Illuminate\Console\Command;

class ResetCategoryLookupFailed extends Command
{


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    protected $signature = 'ozon:reset-category-flags {--sku=}';

    public function handle()
    {
        $query = OrderItem::where('category_lookup_failed', true);
        if ($this->option('sku')) {
            $query->where('sku', $this->option('sku'));
        }
        $count = $query->update(['category_lookup_failed' => false]);
        $this->info("Сброшено флагов: {$count}");
    }
}
