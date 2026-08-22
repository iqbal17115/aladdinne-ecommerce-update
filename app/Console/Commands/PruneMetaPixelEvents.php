<?php

namespace App\Console\Commands;

use App\Models\MetaPixelEvent;
use Illuminate\Console\Command;

class PruneMetaPixelEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meta-pixel:prune-events';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete Meta Pixel event log rows older than 30 days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $deleted = MetaPixelEvent::where('created_at', '<', now()->subDays(30))->delete();

        $this->info("Deleted {$deleted} Meta Pixel event log row(s) older than 30 days.");
    }
}
