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
use Illuminate\Http\Client\ConnectionException;
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
            $maxRetries = 5;
            $attempt = 0;

            $response = $this->sendRequest();

            while ($response->status() == 429 && $attempt < $maxRetries) {
                $retryAfter = (int) $response->header('Retry-After', 1); // по умолчанию 1 сек
                \Log::warning("Server returned 429. Retrying after {$retryAfter} seconds... Attempt {$attempt}");

                sleep($retryAfter);

                $response = $this->sendRequest();
                $attempt++;
            }

            if ($response->successful()) {
                $data = $response->json()['data'] ?? [];

                if (empty($data)) {
                    \Log::warning("No data returned for {$this->name}, page: {$page}");
                    return;
                }

                $this->processData($data, $this->name);
                \Log::info("Processed {$this->name}, page: {$page}, items: " . count($data));
            } else {
                $this->logAndRetry("API request failed", $response->status());
            }

        } catch (\Throwable $e) {
            \Log::error("Exception in job for {$this->name}, page: {$this->endpoint['params']['page']} - " . $e->getMessage());

            // Повторный запуск через небольшую задержку
            self::dispatch($this->name, $this->endpoint)->delay(now()->addMinutes(1));
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
        self::dispatch($this->name, $this->endpoint);
    }

    /**
     * @throws ConnectionException
     */
    private function sendRequest(int $retry_after_sec = 0): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        $request = Http::timeout(5);

        if ($retry_after_sec !== 0) {
            $request = $request->retry(1, $retry_after_sec * 1000);
        }

        return $request->get($this->endpoint['url'], $this->endpoint['params']);
    }
}
