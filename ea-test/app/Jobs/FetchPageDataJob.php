<?php

namespace App\Jobs;

use App\Console\Commands\FetchDataFromApiCommand;
use App\Models\Incomes;
use App\Models\Orders;
use App\Models\Sales;
use App\Models\Stock;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class FetchPageDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $name;
    protected array $endpoint;

    public function __construct(string $name, array $endpoint)
    {
        $this->name = $name;
        $this->endpoint = $endpoint;
    }

    public function handle(): void
    {
        try {
            $page = $this->endpoint['params']['page'] ?? 1;

            $response = Http::retry(3, 1000)
                ->timeout(60)
                ->get($this->endpoint['url'], $this->endpoint['params']);

            if ($response->successful()) {
                $data = $response->json()['data'] ?? [];

                if (empty($data)) {
                    \Log::warning("No data returned for {$this->name}, page: {$page}");
                    return;
                }
                $this->processData($data, $this->name);
                \Log::info("Processed {$this->name}, page: {$page}, items: " . count($data));
            } else {
                // Делаем копию джобы на случай, если запрос не прошел из-за лимита на кол-во запросов
                $this->logAndRetry("API request failed", $response->status());
            }

        } catch (\Throwable $e) {
            \Log::error("Exception in job for {$this->name}, page: {$this->endpoint['params']['page']} - " . $e->getMessage());
            // Делаем копию джобы на случай, если запрос не прошел из-за лимита на кол-во запросов
            self::dispatch($this->name, $this->endpoint)->delay(now()->addSeconds(60));
        }
    }

    private function processData(array $data, string $name): void
    {
        if ($name == 'stocks') {
            foreach ($data as $item) {
                Stock::updateOrCreate($item);
            }
        }
        if ($name == 'incomes') {
            foreach ($data as $item) {
                Incomes::updateOrCreate($item);
            }
        }
        if ($name == 'sales') {
            foreach ($data as $item) {
                Sales::updateOrCreate($item);
            }
        }
        if ($name == 'orders') {
            foreach ($data as $item) {
                Orders::updateOrCreate($item);
            }
        }
    }

    private function logAndRetry(string $message, int $statusCode): void
    {
        $page = $this->endpoint['params']['page'] ?? 'unknown';

        \Log::error("{$message} for {$this->name}, page: {$page}. Status code: {$statusCode}");

        // Повтор через 1 минуту
        self::dispatch($this->name, $this->endpoint)->delay(now()->addSeconds(60));
    }
}
