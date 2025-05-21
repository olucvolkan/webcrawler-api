<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ListWebsiteRequest
{
    #[Assert\Type('string', message: 'Domain must be a string')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Domain cannot be longer than {{ limit }} characters'
    )]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9][a-zA-Z0-9-_.]*\.[a-zA-Z]{2,}$/',
        message: 'Invalid domain format'
    )]
    private ?string $domain = null;

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function setDomain(?string $domain): self
    {
        $this->domain = $domain ? strtolower(trim($domain)) : null;
        return $this;
    }
}
