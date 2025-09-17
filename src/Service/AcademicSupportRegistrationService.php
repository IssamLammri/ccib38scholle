<?php

namespace App\Service;

use App\Entity\AcademicSupportRegistration;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class AcademicSupportRegistrationService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ValidatorInterface $validator,
    ) {}

    /**
     * @throws \InvalidArgumentException when validation fails (errors as array in exception message)
     */
    public function createFromPayload(array $payload): AcademicSupportRegistration
    {
        $reg = (new AcademicSupportRegistration())
            ->setStudentFirstName($payload['student_first_name'] ?? '')
            ->setStudentLastName($payload['student_last_name'] ?? '')
            ->setLevel($payload['level'] ?? '')
            ->setSubjects((array)($payload['subjects'] ?? []))
            ->setParentFirstName($payload['parent_first_name'] ?? '')
            ->setParentLastName($payload['parent_last_name'] ?? '')
            ->setEmail($payload['email'] ?? '')
            ->setPhone($payload['phone'] ?? null)
            ->setMotherFirstName($payload['mother_first_name'] ?? null)
            ->setMotherLastName($payload['mother_last_name'] ?? null)
            ->setMotherPhone($payload['mother_phone'] ?? null)
            ->setAddress($payload['address'] ?? null)
            ->setPostalCode($payload['postal_code'] ?? null)
            ->setCity($payload['city'] ?? null)
            ->setAcceptedPaymentTerms((bool)($payload['accepted_payment_terms'] ?? false));

        $errors = $this->validator->validate($reg);
        if (\count($errors) > 0) {
            throw new \InvalidArgumentException(json_encode($this->formatErrors($errors), JSON_UNESCAPED_UNICODE));
        }

        $this->em->persist($reg);
        $this->em->flush();

        return $reg;
    }

    /** @return array<string, string[]> */
    private function formatErrors(ConstraintViolationListInterface $errors): array
    {
        $out = [];
        foreach ($errors as $e) {
            $path = $e->getPropertyPath() ?: '_form';
            $out[$path][] = $e->getMessage();
        }
        return $out;
    }
}
