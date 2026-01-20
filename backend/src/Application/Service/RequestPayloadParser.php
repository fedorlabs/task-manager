<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Exception\ValidationException;
use Symfony\Component\HttpFoundation\Request;

class RequestPayloadParser
{
    public function parse(Request $request): array
    {
        $content = $request->getContent();
        if ($content === '') {
            return [];
        }

        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ValidationException('Invalid JSON payload');
        }
        if (!is_array($data)) {
            throw new ValidationException('Invalid JSON payload');
        }
        return $data;
    }
}
