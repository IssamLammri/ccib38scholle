<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class CommaSeparatedToArrayTransformer implements DataTransformerInterface
{
    public function transform($value): string
    {
        // Transform array to string (comma separated)
        return is_array($value) ? implode(', ', $value) : '';
    }

    public function reverseTransform($value): array
    {
        // Transform comma separated string to array
        return array_map('trim', explode(',', $value));
    }
}
