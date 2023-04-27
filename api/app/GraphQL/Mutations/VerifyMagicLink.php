<?php

namespace App\GraphQL\Mutations;

use App\Models\User;
use Carbon\Carbon;
use DanielDeWit\LighthouseSanctum\Contracts\Services\SignatureServiceInterface;
use Exception;
use Illuminate\Auth\AuthenticationException as AuthAuthenticationException;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Nuwave\Lighthouse\Exceptions\AuthenticationException;

final class VerifyMagicLink
{
    /**
     * @param SignatureServiceInterface $signatureService
     */
    public function __construct(
        protected SignatureServiceInterface $signatureService
    ) {}

    /**
     * @param  null  $_
     * @param  array{id:string, hash: string, expires: int, signature: string}  $args
     * @return array{token: string}
     * @throws Exception
     */
    public function __invoke($_, array $args): array
    {
        [
            'id' => $id,
            'hash' => $hash,
            'expires' => $expires,
            'signature' => $signature,
        ] = $args;

        $user = User::find($id);

        if (! $user) {
           throw new AuthenticationException();
        }

        if (! hash_equals($hash, sha1($user->email))) {
            throw new AuthenticationException();
        }

        if ($expires < Carbon::now()->getTimestamp()) {
            throw new AuthenticationException();
        }

        try {
            $this->signatureService->verify([
                'id'      => $user->id,
                'hash'    => $hash,
                'expires' => $expires,
            ], $signature);
        } catch (InvalidSignatureException) {
            throw new AuthAuthenticationException();
        }

        $user->markEmailAsVerified();

        return [
            'token' => $user->createToken('default')->plainTextToken,
        ];
    }
}
