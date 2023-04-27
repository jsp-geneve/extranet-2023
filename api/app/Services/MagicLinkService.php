<?php

namespace App\Services;

use App\Contracts\MagicLinkServiceInterface;
use DanielDeWit\LighthouseSanctum\Services\EmailVerificationService;

class MagicLinkService extends EmailVerificationService implements MagicLinkServiceInterface
{
    // ...
}