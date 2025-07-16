<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PublicHoliday;
use Illuminate\Support\Facades\Http;

class FetchMalaysiaHolidays extends Command
{
    protected $signature = 'holidays:fetch';
    protected $description = 'Fetch Malaysia public holidays and store in database';

    public function handle()
    {
        $year = now()->year;
        $apiKey = env('CALENDARIFIC_API_KEY');

        $response = Http::get("https://calendarific.com/api/v2/holidays", [
            'api_key' => $apiKey,
            'country' => 'MY',
            'year' => $year
        ]);

        $holidays = $response['response']['holidays'] ?? [];

        foreach ($holidays as $holiday) {
            $isoDate = $holiday['date']['iso']; // e.g., '2025-03-20T17:01:21+08:00'
            $formattedDate = date('Y-m-d', strtotime($isoDate)); // 👉 Fix format

    PublicHoliday::updateOrCreate(
        ['date' => $formattedDate],
        ['name' => $holiday['name']]
    );
}

        $this->info("Malaysia public holidays imported successfully.");
    }
}