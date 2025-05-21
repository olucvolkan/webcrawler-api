<?php

namespace App\Controller;

use App\DTO\ListWebsiteRequest;
use App\Service\ResponseFormatter;
use App\Service\WebSiteListService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/websites', name: 'api_websites_')]
class WebsiteController extends AbstractController
{
    private WebSiteListService $webSiteListService;
    private ResponseFormatter $responseFormatter;
    private ValidatorInterface $validator;

    public function __construct(
        WebSiteListService $webSiteListService,
        ResponseFormatter $responseFormatter,
        ValidatorInterface $validator
    ) {
        $this->webSiteListService = $webSiteListService;
        $this->responseFormatter = $responseFormatter;
        $this->validator = $validator;
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $listRequest = new ListWebsiteRequest();
        $domain = $request->query->get('domain');

        if ($domain !== null) {
            $listRequest->setDomain($domain);

            $violations = $this->validator->validate($listRequest);
            if (count($violations) > 0) {
                $errors = $this->responseFormatter->formatValidationErrors($violations);
                return $this->responseFormatter->formatError('Validation failed', $errors);
            }
        }

        $websites = $this->webSiteListService->getWebsites($listRequest->getDomain());

        return $this->responseFormatter->formatSuccess(
            'Websites retrieved successfully',
            ['websites' => $websites]
        );
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $website = $this->webSiteListService->getWebsite($id);

        return $this->responseFormatter->formatSuccess(
            'Website retrieved successfully',
            ['website' => $website]
        );
    }
}
