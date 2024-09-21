<?php

namespace App\Model;

use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponseTrait
{
    public function apiResponse(mixed $data, int $code = Response::HTTP_OK): JsonResponse
    {
        return new JsonResponse([
            'code' => $code,
            'message' => $data,
        ], $code);
    }

    public function apiErrorResponse(string $message, int $code = Response::HTTP_BAD_REQUEST, mixed $extraData = []): JsonResponse
    {
        return self::apiResponse(['text' => $message, 'data' => $extraData], $code);
    }

    public function retrieveFormErrorMessage(FormErrorIterator $formError): array
    {
        $errors = [];
        foreach ($formError as $error) {
            $errors[] = $error->getMessage();
        }

        return $errors;
    }

    public function formErrorToArray(FormInterface $form): array
    {
        $fieldsErrors = [];

        if ($form->isSubmitted()) {
            foreach ($form->all() as $name => $field) {
                if (!$field->isValid()) {
                    $fieldsErrors[$name] = $this->retrieveFormErrorMessage($field->getErrors());
                }
            }
        }

        return [
            'form' => $this->retrieveFormErrorMessage($form->getErrors()),
            'fields' => $fieldsErrors,
        ];
    }
}
