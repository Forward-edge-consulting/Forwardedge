<?php

namespace App\Jobs\Mailchimp;

use App\Services\Mailchimp;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use MailchimpMarketing\ApiException;

class UpdateTags implements ShouldQueue
{
    use Queueable;

    public $tries = 3;

    public $timeout = 25;

    public $backoff = 60;

    public function __construct(
        protected string $email,
        protected array $tags,
        protected ?string $listId = null
    ) {
        $this->email = strtolower(trim($this->email));
    }

    public function handle(): void
    {
        $tags = collect(Arr::flatten($this->tags))
            ->filter()
            ->map(fn ($tag) => ['name' => (string) $tag, 'status' => 'active'])
            ->values()
            ->all();

        if (empty($tags)) {
            return;
        }

        try {
            $listsApi = Mailchimp::listsApi();
            $listsApi->updateListMemberTags(
                $this->listId ?? Mailchimp::listId(),
                md5($this->email),
                ['tags' => $tags]
            );
        } catch (ApiException $e) {
            Log::warning('Mailchimp tag update failed', [
                'email' => $this->email,
                'message' => $e->getMessage(),
                'body' => $e->getResponseBody(),
            ]);

            throw $e;
        }
    }
}
