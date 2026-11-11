<?php

namespace App\Console\Commands;

use App\Services\Bmkg\BmkgImporter;
use Illuminate\Console\Command;

class SyncBmkg extends Command
{
    protected $signature = 'bmkg:sync {--force : Abaikan jeda minimum antar-request}';

    protected $description = 'Ambil prakiraan cuaca Sekotong Barat, peringatan dini NTB, dan gempa dirasakan dari BMKG ke database';

    public function handle(BmkgImporter $importer): int
    {
        foreach ($importer->refresh($this->option('force')) as $source => $status) {
            $this->line(str_pad(config("bmkg.sources.$source.label"), 36).' '.$status);
        }

        return self::SUCCESS;
    }
}
