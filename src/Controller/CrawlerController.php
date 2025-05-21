<?php

namespace App\Controller;

use App\DTO\CrawlWebsiteRequest;
use App\Service\ResponseFormatter;
use App\Service\WebSiteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/crawler', name: 'api_crawler_')]
class CrawlerController extends AbstractController
{
    private SerializerInterface $serializer;
    private ValidatorInterface $validator;
    private WebSiteService $webSiteService;
    private ResponseFormatter $responseFormatter;

    public function __construct(
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        WebSiteService $webSiteService,
        ResponseFormatter $responseFormatter
    ) {
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->webSiteService = $webSiteService;
        $this->responseFormatter = $responseFormatter;
    }

    #[Route('/crawl', name: 'crawl', methods: ['POST'])]
    public function crawl(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $crawlRequest = new CrawlWebsiteRequest();

        if (isset($data['url'])) {
            $crawlRequest->setUrl($data['url']);
        }

        $violations = $this->validator->validate($crawlRequest);

        if (count($violations) > 0) {
            $errors = $this->responseFormatter->formatValidationErrors($violations);
            return $this->responseFormatter->formatError('Validation failed', $errors);
        }

        $webSite = $this->webSiteService->createWebSite($crawlRequest->getUrl());

        return $this->responseFormatter->formatSuccess(
            'We are crawling website now.',
            ['id' => $webSite->getId(), 'status' => $webSite->getStatus()]
        );
    }
}
