<?php

namespace App\Console\Commands;

use App\Http\mode\Sina;
use Illuminate\Console\Command;

class SyncExternalMarket extends Command
{
    protected $signature = 'market:sync-external {--type=all}';

    protected $description = '同步外汇、外盘、美股和 ETF 行情到 currency_quotation';

    public function handle()
    {
        $type = $this->option('type');
        $sina = new Sina();

        $map = [
            'futures' => 'real',
            'forex' => 'foreign_real',
            'stock' => 'gpreal',
            'etf' => 'etfreal',
        ];

        if ($type === 'all') {
            foreach ($map as $label => $method) {
                $this->info('sync ' . $label . '...');
                $sina->$method();
            }
            $this->info('sync done');
            return;
        }

        if (!isset($map[$type])) {
            $this->error('unsupported type: ' . $type);
            return;
        }

        $sina->{$map[$type]}();
        $this->info('sync done: ' . $type);
    }
}
