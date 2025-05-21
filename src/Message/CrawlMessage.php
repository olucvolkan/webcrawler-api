<?php

namespace App\Message;

class CrawlMessage
{
    private int $webSiteId;

    public function __construct(int $webSiteId)
    {
        $this->webSiteId = $webSiteId;
    }

    public function getWebSiteId(): int
    {
        return $this->webSiteId;
    }
}
