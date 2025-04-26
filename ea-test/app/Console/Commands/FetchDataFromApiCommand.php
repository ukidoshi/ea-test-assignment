<?php

namespace App\Console\Commands;

use App\Jobs\FetchPageDataJob;
use App\Models\Incomes;
use App\Models\Sales;
use App\Models\Stock;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class FetchDataFromApiCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:api-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'стянуть все данные по описанным эндпоинтам и сохранить в БД.';

    /**
     * Execute the console command.
     * @throws ConnectionException
     */
    public function handle(): int
    {
        $this->info('Start fetching...');

        $endpoints = config('fetch_endpoints.endpoints');

        foreach ($endpoints as $name => $endpoint) {
            $this->info("Fetching {$name}...");

            // Получаем первую страницу, чтобы узнать last_page
            $firstResponse = Http::get($endpoint['url'], $endpoint['params']);
            $lastPage = $firstResponse->json()['meta']['last_page'] ?? 1;
            $this->info("Total pages for {$name}: {$lastPage}");

            // Проходим по каждой странице и регистрируем джобу для его парсинга
            for ($page = 1; $page <= $lastPage; $page++) {
                $params = $endpoint['params'];
                $params['page'] = $page;

                // Добавим задержку, чтобы достигнуть лимита у API
                $delaySeconds = $page * 2;

                FetchPageDataJob::dispatch($name, [
                    'url' => $endpoint['url'],
                    'params' => $params,
                ])->delay(now()->addSeconds($delaySeconds));

                $this->info("Dispatched job for {$name}, page {$page}");
            }
        }

        $this->info("All jobs dispatched.");

        return Command::SUCCESS;
    }
}
