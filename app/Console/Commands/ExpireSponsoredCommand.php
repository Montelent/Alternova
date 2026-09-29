<?php

namespace App\Console\Commands;

use App\Models\OpenSourceAlternative;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class ExpireSponsoredCommand extends Command
{
    protected $signature = 'alternova:expire-sponsored';

    protected $description = 'Turn off sponsored placements past their end date';

    public function handle(): int
    {
        if (! Schema::hasColumn('open_source_alternatives', 'is_sponsored')) {
            $this->warn('Sponsored columns missing — run migrations.');

            return self::FAILURE;
        }

        $count = OpenSourceAlternative::query()
            ->where('is_sponsored', true)
            ->whereNotNull('sponsored_until')
            ->where('sponsored_until', '<=', now())
            ->update([
                'is_sponsored' => false,
                // Keep is_featured as-is (editor may still want organic feature)
            ]);

        $this->info("Expired {$count} sponsored placement(s).");

        return self::SUCCESS;
    }
}
