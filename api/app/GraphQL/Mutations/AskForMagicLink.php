<?php

namespace App\GraphQL\Mutations;

use App\Contracts\MagicLinkServiceInterface;
use App\Models\User;
use App\Notifications\MagicLink;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Notification;
use RuntimeException;

final class AskForMagicLink
{
    public function __construct(
        protected MagicLinkServiceInterface $magicLinkService,
    ) {}
    
    /**
     * @param  null  $_
     * @param  array{'email': string, 'verification_url': string}  $args
     * @return array<string, string>
     */
    public function __invoke($_, array $args): array
    {
        [
            'email' => $email,
            'verification_url' => $raw_url
        ] = $args;

        $user = $this->retrieveUser($email);

        $url = $this->magicLinkService->transformUrl($user, $raw_url);

        Notification::send($user, new MagicLink($url));

        return [
            'status' => 'EMAIL_SENT'
        ];
    }

    /**
     * @param string $email
     * @return MustVerifyEmail
     * @throws RuntimeException
     */
    protected function retrieveUser(string $email): MustVerifyEmail
    {
        $user = User::firstOrCreate([
            'email' => $email, // (Validation of email done at schema level)
        ]);

        if (! $user instanceof MustVerifyEmail) {
            throw new RuntimeException('User must implement "' . MustVerifyEmail::class . '".');
        }

        return $user;
    }
}
