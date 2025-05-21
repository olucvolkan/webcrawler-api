<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CrawlWebsiteRequest
{
    #[Assert\NotBlank(message: 'URL should not be blank')]
    #[Assert\Url(message: 'The URL {{ value }} is not a valid URL')]
    private string $url;

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }
}
