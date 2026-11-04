<?php

namespace App\Jobs\Mailchimp;

use App\Jobs\Mailchimp\UpdateTags;
use App\Services\Mailchimp;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use MailchimpMarketing\ApiException;

class UpsertMember implements ShouldQueue
{
    use Queueable, Batchable;

    public $tries = 3;

    public $timeout = 25;

    public $backoff = 60;

    public function __construct(
        protected string $email,
        protected array $mergeFields = [],
        protected array $options = []
    ) {
        $this->email = strtolower(trim($this->email));
    }

    public function handle(): void
    {
        try {
            $listId    = $this->options['list_id'] ?? Mailchimp::listId();
            $status    = ($this->options['double_opt_in'] ?? config('services.mailchimp.double_opt_in')) ? 'pending' : 'subscribed';
            $tags      = Arr::wrap($this->options['tags'] ?? []);
            $listsApi  = Mailchimp::listsApi();
            $hash      = md5($this->email);

            $listsApi->setListMember($listId, $hash, [
                'email_address' => $this->email,
                'status'        => $status,
                'status_if_new' => $status,
                'merge_fields'  => $this->mergeFields,
            ]);

            if (!empty($tags)) {
                UpdateTags::dispatch($this->email, $tags, $listId)->onQueue($this->options['queue'] ?? null);
            }
        } catch (InvalidArgumentException $e) {
            Log::error('Mailchimp member sync failed (config)', [
                'email'   => $this->email,
                'message' => $e->getMessage(),
            ]);

            throw $e;
        } catch (ApiException $e) {
            Log::error('Mailchimp member sync failed (api)', [
                'email'   => $this->email,
                'message' => $e->getMessage(),
                'body'    => $e->getResponseBody(),
            ]);

            throw $e;
        } catch (GuzzleException $e) {
            Log::error('Mailchimp member sync failed (http)', [
                'email'   => $this->email,
                'message' => $e->getMessage(),
                'response' => method_exists($e, 'getResponse') ? (string) optional($e->getResponse())->getBody() : null,
            ]);

            throw $e;
        } catch (\Throwable $e) {
            Log::error('Mailchimp member sync failed (client)', [
                'email'     => $this->email,
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
