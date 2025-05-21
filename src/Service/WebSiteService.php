<?php

namespace App\Service;

use App\Entity\WebSite;
use App\Message\CrawlMessage;
use App\Repository\WebSiteRepository;
use Symfony\Component\Messenger\MessageBusInterface;

class WebSiteService
{
    private WebSiteRepository $webSiteRepository;
    private MessageBusInterface $messageBus;
    private CrawlerService $crawlerService;

    public function __construct(
        WebSiteRepository $webSiteRepository,
        MessageBusInterface $messageBus,
        CrawlerService $crawlerService
    ) {
        $this->webSiteRepository = $webSiteRepository;
        $this->messageBus = $messageBus;
        $this->crawlerService = $crawlerService;
    }

    public function createWebSite(string $url): WebSite
    {
        $domain = $this->crawlerService->extractDomain($url);

        $webSite = new WebSite();
        $webSite->setUrl($url);
        $webSite->setDomain($domain);
        $webSite->setStatus('waiting');

        // Save first to get an ID
        $this->webSiteRepository->save($webSite);

        if ($webSite->getId()) {
            $this->messageBus->dispatch(new CrawlMessage($webSite->getId()));
        }

        return $webSite;
    }
}
