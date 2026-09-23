<?php

namespace App\DataTransferObjects;

readonly class FaceMatchResult
{
    public function __construct(
        public int $childId,
        public float $confidence,
        public string $externalFaceId,
    ) {}
}
