<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ResponseFormatter
{
    public function formatSuccess(string $message, array $data = [], int $statusCode = 200): JsonResponse
    {
        return new JsonResponse([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    public function formatError(string $message, array $errors = [], int $statusCode = 400): JsonResponse
    {
        return new JsonResponse([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors,
        ], $statusCode);
    }

    public function formatValidationErrors(ConstraintViolationListInterface $violations): array
    {
        $errors = [];

        foreach ($violations as $violation) {
            $propertyPath = $violation->getPropertyPath();
            $errors[$propertyPath] = $violation->getMessage();
        }

        return $errors;
    }
}
