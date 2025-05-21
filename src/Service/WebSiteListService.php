<?php

namespace App\Service;

use App\Repository\WebSiteRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WebSiteListService
{
    private WebSiteRepository $webSiteRepository;

    public function __construct(WebSiteRepository $webSiteRepository)
    {
        $this->webSiteRepository = $webSiteRepository;
    }

    public function getWebsites(?string $domain = null): array
    {
        $criteria = [];
        if ($domain) {
            $criteria['domain'] = $domain;
        }

        $websites = $this->webSiteRepository->findBy($criteria);

        return array_map(function ($website) {
            return [
                'id' => $website->getId(),
                'url' => $website->getUrl(),
                'domain' => $website->getDomain(),
                'status' => $website->getStatus(),
                'htmlTagCount' => $website->getHtmlTagCount(),
                'requestDuration' => $website->getRequestDuration(),
                'createdAt' => $website->getCreatedAt()?->format('Y-m-d H:i:s'),
                'updatedAt' => $website->getUpdatedAt()?->format('Y-m-d H:i:s'),
            ];
        }, $websites);
    }

    public function getWebsite(int $id): array
    {
        $website = $this->webSiteRepository->find($id);

        if (!$website) {
            throw new NotFoundHttpException('Website not found');
        }

        return [
            'id' => $website->getId(),
            'url' => $website->getUrl(),
            'domain' => $website->getDomain(),
            'status' => $website->getStatus(),
            'htmlTagCount' => $website->getHtmlTagCount(),
            'requestDuration' => $website->getRequestDuration(),
            'createdAt' => $website->getCreatedAt()?->format('Y-m-d H:i:s'),
            'updatedAt' => $website->getUpdatedAt()?->format('Y-m-d H:i:s'),
        ];
    }
}
