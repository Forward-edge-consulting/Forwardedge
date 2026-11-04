<?php

namespace App\Services;

use InvalidArgumentException;
use MailchimpMarketing\ApiClient;
use MailchimpMarketing\Api\ListsApi;

class Mailchimp
{
    public static function client(): ApiClient
    {
        $apiKey = config('services.mailchimp.key');
        $server = config('services.mailchimp.server_prefix');

        if (blank($apiKey) || blank($server)) {
            throw new InvalidArgumentException('Mailchimp credentials are missing. Please set MAILCHIMP_KEY and MAILCHIMP_SERVER_PREFIX.');
        }

        $mc = new ApiClient();
        $mc->setConfig([
            'timeout' => 10,
            'apiKey' => $apiKey,
            'server' => $server,
        ]);

        return $mc;
    }

    /**
     * Typed helper for the Lists API to keep static analysis happy.
     */
    public static function listsApi(): ListsApi
    {
        $client = self::client();

        /** @var ListsApi $lists */
        $lists = $client->lists;

        return $lists;
    }

    public static function listId(): string
    {
        $listId = (string) config('services.mailchimp.list_id', '');

        if (blank($listId)) {
            throw new InvalidArgumentException('Mailchimp list ID is missing. Please set MAILCHIMP_LIST_ID.');
        }

        return $listId;
    }
}
