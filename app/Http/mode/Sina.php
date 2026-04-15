<?php

namespace App\Http\mode;

use Illuminate\Support\Facades\DB;

class Sina
{
    protected $defaultQuoteCurrency = 'USDT';

    public function etfgp($name)
    {
        return $this->geteftgp($name, '1min', 300);
    }

    public function real_gu($name)
    {
        $currency = $this->findCurrency([
            ['vcode', '=', $name],
            ['name', '=', $name],
        ]);
        $quote = $this->getTencentUsQuote($name);
        if (!$currency || empty($quote)) {
            return false;
        }

        return $this->saveQuotation($currency, $quote);
    }

    public function geteftgp($name, $peroid, $limit)
    {
        $bars = $this->getTencentUsBars($name, $peroid, $limit);
        return $this->formatBars($bars, $peroid, $name, $limit);
    }

    public function getKline($name, $peroid, $limit)
    {
        $bars = $this->getForexMinuteBars($name);
        return $this->formatBars($bars, $peroid, $name, $limit);
    }

    public function getwaipanKline($name, $peroid, $limit)
    {
        $bars = $this->getPreferredFuturesBars($name, $peroid, $limit);
        return $this->formatBars($bars, $peroid, $name, $limit);
    }

    public function sina($name)
    {
        return $this->getwaipanKline($name, '1min', 300);
    }

    public function foreign($name)
    {
        return $this->getKline($name, '1min', 300);
    }

    public function real_s($name)
    {
        $currency = $this->findCurrency([
            ['vcode', '=', $name],
            ['name', '=', $name],
        ]);
        $quote = $this->getFuturesQuote($name);
        if (!$currency || empty($quote)) {
            return false;
        }

        return $this->saveQuotation($currency, $quote);
    }

    public function real_s_sina($name)
    {
        return $this->real_s($name);
    }

    public function real()
    {
        $list = DB::table('currency_matches')->where('market_from', 3)->paginate(100);
        $list = json_decode(json_encode($list), true);
        foreach ($list['data'] as $item) {
            $currency = DB::table('currency')->where('id', $item['currency_id'])->first();
            if ($currency) {
                $this->real_s($currency->vcode ?: $currency->name);
            }
        }

        return true;
    }

    public function gpreal()
    {
        $list = DB::table('currency_matches')->where('market_from', 6)->paginate(100);
        $list = json_decode(json_encode($list), true);
        foreach ($list['data'] as $item) {
            $currency = DB::table('currency')->where('id', $item['currency_id'])->first();
            if ($currency) {
                $this->real_gu($currency->vcode ?: $currency->name);
            }
        }

        return true;
    }

    public function etfreal()
    {
        $list = DB::table('currency_matches')->where('market_from', 9)->paginate(100);
        $list = json_decode(json_encode($list), true);
        foreach ($list['data'] as $item) {
            $currency = DB::table('currency')->where('id', $item['currency_id'])->first();
            if ($currency) {
                $this->real_gu($currency->vcode ?: $currency->name);
            }
        }

        return true;
    }

    public function foreign_real()
    {
        $list = DB::table('currency_matches')->where('market_from', 0)->paginate(100);
        $list = json_decode(json_encode($list), true);
        foreach ($list['data'] as $item) {
            $currency = DB::table('currency')->where('id', $item['currency_id'])->first();
            if ($currency) {
                $this->foreign_s_sina($currency->name);
            }
        }

        return true;
    }

    public function foreign_s($name)
    {
        return $this->foreign_s_sina($name);
    }

    public function foreign_s_sina($name)
    {
        $currency = $this->findCurrency([
            ['name', '=', $name],
            ['real_name', '=', $name],
            ['vcode', '=', $name],
        ]);
        if (!$currency) {
            return false;
        }

        $quote = $this->getForexQuote($currency);
        if (empty($quote)) {
            return false;
        }

        return $this->saveQuotation($currency, $quote);
    }

    protected function getTencentUsBars($name, $period, $limit)
    {
        $period = $this->normalizePeriod($period);
        return $this->getYahooUsBars($name, $period, $limit);
    }

    protected function getForexMinuteBars($name)
    {
        $currency = $this->findCurrency([
            ['name', '=', $name],
            ['real_name', '=', $name],
            ['vcode', '=', $name],
        ]);
        $code = $currency ? $currency->vcode : $name;
        $url = 'https://vip.stock.finance.sina.com.cn/forex/api/jsonp.php/var%20_fx_data=/NewForexService.getMinKline?symbol=' . $code . '&scale=1&datalen=1440';
        $payload = $this->parseJsonp($this->requestText($url, $this->getSinaHeaders()));
        $bars = [];

        if (!is_array($payload)) {
            return $bars;
        }

        foreach ($payload as $row) {
            $timestamp = strtotime($row['d']);
            if ($timestamp <= 0) {
                continue;
            }
            $open = $this->toFloat($row['o']);
            $close = $this->toFloat($row['c']);
            $high = $this->toFloat($row['h']);
            $low = $this->toFloat($row['l']);
            $bars[] = [
                'timestamp' => $timestamp,
                'open' => $open,
                'close' => $close,
                'high' => $high,
                'low' => $low,
                'volume' => 0,
                'amount' => $close,
            ];
        }

        return $bars;
    }

    protected function getFuturesMinuteBars($name)
    {
        $url = 'https://stock2.finance.sina.com.cn/futures/api/openapi.php/GlobalFuturesService.getGlobalFuturesMinLine?symbol=' . $name . '&callback=var%20t1hf_W=';
        $payload = $this->parseJsonp($this->requestText($url, $this->getSinaHeaders()));
        $rows = $payload['result']['data']['minLine_1d'] ?? [];
        $bars = [];
        $lastClose = 0;

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            if (count($row) >= 10) {
                $price = $this->toFloat($row[1]);
                $avg = $this->toFloat($row[8]);
                $timestamp = strtotime($row[9]);
            } else {
                $price = $this->toFloat($row[1] ?? 0);
                $avg = $this->toFloat($row[4] ?? 0);
                $timestamp = strtotime($row[5] ?? '');
            }
            if ($timestamp <= 0 || $price <= 0) {
                continue;
            }
            $open = $lastClose > 0 ? $lastClose : ($avg > 0 ? $avg : $price);
            $values = [$open, $price];
            if ($avg > 0) {
                $values[] = $avg;
            }
            $bars[] = [
                'timestamp' => $timestamp,
                'open' => $open,
                'close' => $price,
                'high' => max($values),
                'low' => min($values),
                'volume' => 0,
                'amount' => $price,
            ];
            $lastClose = $price;
        }

        return $bars;
    }

    protected function getPreferredFuturesBars($name, $period, $limit)
    {
        $yahooSymbol = $this->getFuturesYahooSymbol($name);
        if ($yahooSymbol) {
            $bars = $this->getYahooUsBars($yahooSymbol, $period, $limit);
            if (!empty($bars)) {
                return $bars;
            }
        }

        return $this->getFuturesMinuteBars($name);
    }

    protected function getTencentUsQuote($name)
    {
        return $this->getYahooUsQuote($name);
    }

    protected function getYahooUsBars($name, $period, $limit)
    {
        $map = [
            '1min' => ['interval' => '1m', 'range' => '5d'],
            '5min' => ['interval' => '5m', 'range' => '1mo'],
            '15min' => ['interval' => '15m', 'range' => '1mo'],
            '30min' => ['interval' => '30m', 'range' => '1mo'],
            '60min' => ['interval' => '60m', 'range' => '3mo'],
            '1day' => ['interval' => '1d', 'range' => '10y'],
            '1week' => ['interval' => '1wk', 'range' => '10y'],
            '1mon' => ['interval' => '1mo', 'range' => '10y'],
            '1year' => ['interval' => '3mo', 'range' => '10y'],
        ];
        $config = $map[$period] ?? $map['1day'];
        $payload = $this->requestYahooChart($name, $config['interval'], $config['range']);
        return $this->parseYahooChartBars($payload, $limit);
    }

    protected function getYahooUsQuote($name)
    {
        $payload = $this->requestYahooChart($name, '1d', '6mo');
        $result = $payload['chart']['result'][0] ?? [];
        $meta = $result['meta'] ?? [];
        $indicators = $result['indicators']['quote'][0] ?? [];
        $closes = array_values(array_filter($indicators['close'] ?? [], function ($value) {
            return $value !== null;
        }));
        $opens = array_values(array_filter($indicators['open'] ?? [], function ($value) {
            return $value !== null;
        }));

        $price = $this->toFloat($meta['regularMarketPrice'] ?? 0);
        $open = $this->toFloat(!empty($opens) ? end($opens) : ($meta['regularMarketOpen'] ?? 0));
        $preclose = $this->toFloat($meta['previousClose'] ?? 0);
        $high = $this->toFloat($meta['regularMarketDayHigh'] ?? $price);
        $low = $this->toFloat($meta['regularMarketDayLow'] ?? $price);
        $volume = $this->toFloat($meta['regularMarketVolume'] ?? 0);

        if ($price <= 0) {
            $price = !empty($closes) ? $this->toFloat(end($closes)) : 0;
        }
        if ($preclose <= 0 && count($closes) >= 2) {
            $preclose = $this->toFloat($closes[count($closes) - 2]);
        }
        if ($open <= 0) {
            $open = $preclose > 0 ? $preclose : $price;
        }

        if ($price <= 0) {
            return [];
        }

        return [
            'price' => $price,
            'open' => $open > 0 ? $open : $price,
            'preclose' => $preclose > 0 ? $preclose : $price,
            'high' => $high > 0 ? $high : $price,
            'low' => $low > 0 ? $low : $price,
            'volume' => $volume,
        ];
    }

    protected function requestYahooChart($symbol, $interval, $range)
    {
        $url = sprintf(
            'https://query1.finance.yahoo.com/v8/finance/chart/%s?interval=%s&range=%s&includePrePost=false&events=div%%2Csplits',
            urlencode(strtoupper($symbol)),
            urlencode($interval),
            urlencode($range)
        );
        return $this->requestJson($url, [
            'User-Agent: Mozilla/5.0',
            'Accept: application/json,text/plain,*/*',
            'Referer: https://finance.yahoo.com/',
        ]);
    }

    protected function parseYahooChartBars(array $payload, $limit)
    {
        $result = $payload['chart']['result'][0] ?? [];
        $timestamps = $result['timestamp'] ?? [];
        $quote = $result['indicators']['quote'][0] ?? [];
        $opens = $quote['open'] ?? [];
        $closes = $quote['close'] ?? [];
        $highs = $quote['high'] ?? [];
        $lows = $quote['low'] ?? [];
        $volumes = $quote['volume'] ?? [];
        $bars = [];

        foreach ($timestamps as $index => $timestamp) {
            $open = $this->toFloat($opens[$index] ?? 0);
            $close = $this->toFloat($closes[$index] ?? 0);
            $high = $this->toFloat($highs[$index] ?? 0);
            $low = $this->toFloat($lows[$index] ?? 0);
            $volume = $this->toFloat($volumes[$index] ?? 0);

            if ($timestamp <= 0 || $close <= 0) {
                continue;
            }

            if ($open <= 0) {
                $open = $close;
            }
            if ($high <= 0) {
                $high = max($open, $close);
            }
            if ($low <= 0) {
                $low = min($open, $close);
            }

            $bars[] = [
                'timestamp' => intval($timestamp),
                'open' => $open,
                'close' => $close,
                'high' => $high,
                'low' => $low,
                'volume' => $volume,
                'amount' => $volume * $close,
            ];
        }

        if (!empty($limit)) {
            $bars = array_slice($bars, -intval($limit));
        }

        return $bars;
    }

    protected function getFuturesQuote($name)
    {
        $yahooSymbol = $this->getFuturesYahooSymbol($name);
        if ($yahooSymbol) {
            $quote = $this->getYahooUsQuote($yahooSymbol);
            if (!empty($quote)) {
                return $quote;
            }
        }

        $url = 'https://w.sinajs.cn/?_=' . time() . '&list=hf_' . $name;
        $text = $this->requestText($url, $this->getSinaHeaders());
        $fields = $this->extractQuotedFields($text);
        if (count($fields) < 6) {
            return [];
        }

        $price = $this->toFloat($fields[0]);
        $open = $this->toFloat($fields[1]);
        $preclose = $this->toFloat($fields[3] ?? $price);
        $high = $this->toFloat($fields[4]);
        $low = $this->toFloat($fields[5]);
        if ($price <= 0) {
            return [];
        }

        return [
            'price' => $price,
            'open' => $open > 0 ? $open : $price,
            'preclose' => $preclose > 0 ? $preclose : $price,
            'high' => $high > 0 ? $high : $price,
            'low' => $low > 0 ? $low : $price,
            'volume' => 0,
        ];
    }

    protected function getFuturesYahooSymbol($name)
    {
        $map = [
            'GC' => 'GC=F',
            'CL' => 'CL=F',
            'OIL' => 'BZ=F',
            'NG' => 'NG=F',
            'HO' => 'HO=F',
            'C' => 'ZC=F',
            'S' => 'ZS=F',
            'SM' => 'ZM=F',
            'W' => 'ZW=F',
            'RS' => 'SB=F',
            'CT' => 'CT=F',
            'KC' => 'KC=F',
            'LHC' => 'HE=F',
        ];

        $symbol = strtoupper(trim($name));
        return $map[$symbol] ?? null;
    }

    protected function getForexQuote($currency)
    {
        $code = $currency->vcode ?: $currency->real_name ?: $currency->name;
        $url = 'https://w.sinajs.cn/?_=' . time() . '&list=' . $code . ',sys_time';
        $text = $this->requestText($url, $this->getSinaHeaders());
        if (!preg_match('/var hq_str_' . preg_quote($code, '/') . '="([^"]*)"/', $text, $matches)) {
            return [];
        }
        $fields = explode(',', $matches[1]);
        if (count($fields) < 9) {
            return [];
        }

        $price = $this->toFloat($fields[1]);
        $preclose = $this->toFloat($fields[8] ?? $price);
        $open = $this->toFloat($fields[8] ?? $price);
        $high = $this->toFloat($fields[6] ?? $price);
        $low = $this->toFloat($fields[7] ?? $price);

        if ($price <= 0) {
            return [];
        }

        return [
            'price' => $price,
            'open' => $open > 0 ? $open : $price,
            'preclose' => $preclose > 0 ? $preclose : $price,
            'high' => $high > 0 ? $high : $price,
            'low' => $low > 0 ? $low : $price,
            'volume' => 0,
        ];
    }

    protected function saveQuotation($currency, array $quote)
    {
        $price = $this->toFloat($quote['price'] ?? 0);
        if ($price <= 0) {
            return false;
        }

        $preclose = $this->toFloat($quote['preclose'] ?? ($currency->price ?? $price));
        if ($preclose <= 0) {
            $preclose = $price;
        }

        $payload = [
            'now_price' => $this->formatDecimal($price),
            'add_time' => time(),
            'change' => $this->formatChange($price, $preclose),
            'high' => $this->formatDecimal($quote['high'] ?? $price),
            'low' => $this->formatDecimal($quote['low'] ?? $price),
            'open' => $this->formatDecimal($quote['open'] ?? $price),
            'close' => $this->formatDecimal($preclose),
            'volume' => $this->formatDecimal($quote['volume'] ?? 0),
        ];

        $quotation = DB::table('currency_quotation')->where('currency_id', $currency->id)->first();
        if ($quotation) {
            return DB::table('currency_quotation')->where('currency_id', $currency->id)->update($payload);
        }

        $match = DB::table('currency_matches')->where('currency_id', $currency->id)->orderBy('id', 'asc')->first();
        if (!$match) {
            return false;
        }

        $payload['currency_id'] = $currency->id;
        $payload['legal_id'] = $match->legal_id;
        $payload['match_id'] = $match->id;
        return DB::table('currency_quotation')->insert($payload);
    }

    protected function formatBars(array $bars, $period, $name, $limit)
    {
        $period = $this->normalizePeriod($period);
        $bars = $this->aggregateBars($bars, $period);
        if (!empty($limit)) {
            $bars = array_slice($bars, -intval($limit));
        }

        $result = [];
        foreach ($bars as $bar) {
            $result[] = [
                'id' => $bar['timestamp'],
                'period' => $period,
                'base-currency' => $name,
                'quote-currency' => $this->defaultQuoteCurrency,
                'open' => $bar['open'],
                'close' => $bar['close'],
                'high' => $bar['high'],
                'low' => $bar['low'],
                'vol' => $bar['volume'],
                'amount' => $bar['amount'],
            ];
        }

        return $result;
    }

    protected function aggregateBars(array $bars, $period)
    {
        if (empty($bars)) {
            return [];
        }

        usort($bars, function ($left, $right) {
            if ($left['timestamp'] == $right['timestamp']) {
                return 0;
            }
            return $left['timestamp'] < $right['timestamp'] ? -1 : 1;
        });

        $period = $this->normalizePeriod($period);
        if ($period === '1min') {
            return array_values($bars);
        }

        $grouped = [];
        foreach ($bars as $bar) {
            $key = $this->makeBucketKey($bar['timestamp'], $period);
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'timestamp' => $this->makeBucketTimestamp($bar['timestamp'], $period),
                    'open' => $bar['open'],
                    'close' => $bar['close'],
                    'high' => $bar['high'],
                    'low' => $bar['low'],
                    'volume' => $bar['volume'],
                    'amount' => $bar['amount'],
                ];
                continue;
            }

            $grouped[$key]['close'] = $bar['close'];
            $grouped[$key]['high'] = max($grouped[$key]['high'], $bar['high']);
            $grouped[$key]['low'] = min($grouped[$key]['low'], $bar['low']);
            $grouped[$key]['volume'] += $bar['volume'];
            $grouped[$key]['amount'] += $bar['amount'];
        }

        return array_values($grouped);
    }

    protected function makeBucketKey($timestamp, $period)
    {
        switch ($period) {
            case '1day':
                return date('Y-m-d', $timestamp);
            case '1week':
                return date('o-W', $timestamp);
            case '1mon':
                return date('Y-m', $timestamp);
            case '1year':
                return date('Y', $timestamp);
            default:
                $minutes = $this->periodToMinutes($period);
                return floor($timestamp / ($minutes * 60));
        }
    }

    protected function makeBucketTimestamp($timestamp, $period)
    {
        switch ($period) {
            case '1day':
                return strtotime(date('Y-m-d 00:00:00', $timestamp));
            case '1week':
                return strtotime(date('o-\WW-1 00:00:00', $timestamp));
            case '1mon':
                return strtotime(date('Y-m-01 00:00:00', $timestamp));
            case '1year':
                return strtotime(date('Y-01-01 00:00:00', $timestamp));
            default:
                $minutes = $this->periodToMinutes($period);
                return intval(floor($timestamp / ($minutes * 60)) * $minutes * 60);
        }
    }

    protected function normalizePeriod($period)
    {
        $map = [
            '1D' => '1day',
            '1W' => '1week',
            '1M' => '1mon',
            '1Y' => '1year',
            '1hour' => '60min',
            '1min' => '1min',
            '5min' => '5min',
            '15min' => '15min',
            '30min' => '30min',
            '60min' => '60min',
            '1day' => '1day',
            '1week' => '1week',
            '1mon' => '1mon',
            '1year' => '1year',
        ];

        return $map[$period] ?? '1min';
    }

    protected function periodToMinutes($period)
    {
        $map = [
            '1min' => 1,
            '5min' => 5,
            '15min' => 15,
            '30min' => 30,
            '60min' => 60,
            '1day' => 1440,
            '1week' => 10080,
            '1mon' => 43200,
            '1year' => 525600,
        ];

        return $map[$period] ?? 1;
    }

    protected function requestJson($url, array $headers = [])
    {
        $text = $this->requestText($url, $headers);
        if ($text === '') {
            return [];
        }
        $data = json_decode($text, true);
        return is_array($data) ? $data : [];
    }

    protected function requestText($url, array $headers = [])
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_USERAGENT => 'Mozilla/5.0',
        ]);
        $response = curl_exec($curl);
        $error = curl_errno($curl);
        curl_close($curl);

        if ($error || $response === false || $response === null) {
            return '';
        }

        return trim($response);
    }

    protected function parseJsonp($text)
    {
        if ($text === '') {
            return [];
        }
        if (preg_match('/^[^(]+\((.*)\)\s*;?$/s', $text, $matches)) {
            $text = $matches[1];
        }
        $data = json_decode($text, true);
        return is_array($data) ? $data : [];
    }

    protected function extractQuotedFields($text)
    {
        if (!preg_match('/="([^"]*)"/', $text, $matches)) {
            return [];
        }
        return explode(',', $matches[1]);
    }

    protected function extractQtTradeDate(array $qt)
    {
        foreach ($qt as $value) {
            if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) {
                return substr($value, 0, 10);
            }
        }
        return date('Y-m-d');
    }

    protected function getSinaHeaders()
    {
        return [
            'referer: https://finance.sina.com.cn/',
            'Accept-Language: zh-CN,zh;q=0.9,en-US;q=0.8,en;q=0.7',
        ];
    }

    protected function findCurrency(array $conditions)
    {
        foreach ($conditions as $condition) {
            $currency = DB::table('currency')->where($condition[0], $condition[1], $condition[2])->first();
            if ($currency) {
                return $currency;
            }
        }

        return null;
    }

    protected function formatChange($price, $preclose)
    {
        if ($preclose <= 0) {
            return '+0.00';
        }
        $percent = (($price - $preclose) / $preclose) * 100;
        $formatted = number_format(abs($percent), 2, '.', '');
        return ($percent >= 0 ? '+' : '-') . $formatted;
    }

    protected function formatDecimal($value, $scale = 5)
    {
        return number_format($this->toFloat($value), $scale, '.', '');
    }

    protected function toFloat($value)
    {
        if ($value === null || $value === '') {
            return 0;
        }
        return floatval($value);
    }
}
