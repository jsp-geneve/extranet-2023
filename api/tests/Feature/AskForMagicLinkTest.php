<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\MagicLink;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AskForMagicLinkTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function it_retrieves_an_existing_user()
    {
        $user = User::factory()->create([
            'email' => 'foo@example.com',
        ]);

        $this->graphQL(/** @lang GraphQL */'
            mutation ($email: String!) {
                askForMagicLink(input: {
                    email: $email
                    verification_url: "https://example.com/verify-email"
                }) {
                    status
                }
            }
        ', [
            'email' => 'foo@example.com',
        ])->assertJson([
            'data' => [
                'askForMagicLink' => [
                    'status' => 'EMAIL_SENT',
                ],
            ],
        ]);

        $this->assertEquals(User::first(), $user->fresh());
    }

    /**
     * @test
     */
    public function it_registers_a_new_user()
    {
        $this->assertDatabaseMissing('users', [
            'email' => 'foo@example.com',
        ]);

        $this->graphQL(/** @lang GraphQL */'
            mutation ($email: String!) {
                askForMagicLink(input: {
                    email: $email
                    verification_url: "https://example.com/verify-email"
                }) {
                    status
                }
            }
        ', [
            'email' => 'foo@example.com',
        ])->assertJson([
            'data' => [
                'askForMagicLink' => [
                    'status' => 'EMAIL_SENT',
                ],
            ],
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'foo@example.com',
        ]);
    }

    /**
     * @test
     */
    public function it_sends_an_email_notification_to_an_existing_user()
    {
        Notification::fake();
        Carbon::setTestNow(Carbon::createFromTimestamp(1609477200));
        $user = User::factory()->create([
            'email' => 'foo@example.com',
        ]);

        $this->graphQL(/** @lang GraphQL */'
            mutation ($email: String!) {
                askForMagicLink(input: {
                    email: $email
                    verification_url: "https://example.com/verify-email?id=__ID__&hash=__HASH__&expires=__EXPIRES__&signature=__SIGNATURE__"
                }) {
                    status
                }
            }
        ', [
            'email' => 'foo@example.com',
        ])->assertJson([
            'data' => [
                'askForMagicLink' => [
                    'status' => 'EMAIL_SENT',
                ],
            ],
        ]);

        $user->refresh();

        Notification::assertSentTo(
            $user,
            function (MagicLink $notification) use ($user) {
                $id        = $user->getKey();
                $hash      = sha1('foo@example.com');
                $expires   = 1609477200 + config('auth.magic_links.expire', 15) * 60;
                $signature = hash_hmac('sha256', serialize([
                    'id'      => $id,
                    'hash'    => $hash,
                    'expires' => $expires,
                ]), config('app.key'));

                $url = "https://example.com/verify-email?id=$id&hash=$hash&expires=$expires&signature=$signature";
                
                return $notification->url === $url;
            }
        );
    }

    /**
     * @test
     */
    public function it_sends_an_email_notification_to_a_new_user()
    {
        Notification::fake();
        Carbon::setTestNow(Carbon::createFromTimestamp(1609477200));

        $this->graphQL(/** @lang GraphQL */'
            mutation ($email: String!) {
                askForMagicLink(input: {
                    email: $email
                    verification_url: "https://example.com/verify-email?id=__ID__&hash=__HASH__&expires=__EXPIRES__&signature=__SIGNATURE__"
                }) {
                    status
                }
            }
        ', [
            'email' => 'foo@example.com',
        ])->assertJson([
            'data' => [
                'askForMagicLink' => [
                    'status' => 'EMAIL_SENT',
                ],
            ],
        ]);

        $user = User::first();

        Notification::assertSentTo(
            $user,
            function (MagicLink $notification) use ($user) {
                $id        = $user->getKey();
                $hash      = sha1('foo@example.com');
                $expires   = 1609477200 + config('auth.magic_links.expire', 15) * 60;
                $signature = hash_hmac('sha256', serialize([
                    'id'      => $id,
                    'hash'    => $hash,
                    'expires' => $expires,
                ]), config('app.key'));

                $url = "https://example.com/verify-email?id=$id&hash=$hash&expires=$expires&signature=$signature";
                
                return $notification->url === $url;
            }
        );
    }

    /**
     * @test
     */
    public function it_returns_an_error_if_the_email_field_is_missing(): void
    {
        $this->graphQL(/** @lang GraphQL */'
            mutation {
                askForMagicLink(input:{}) {
                    status
                }
            }
        ')->assertGraphQLErrorMessage('Field EmailInput.email of required type String! was not provided.');
    }

    /**
     * @test
     */
    public function it_returns_an_error_if_the_email_field_is_not_a_string(): void
    {
         $this->graphQL(/** @lang GraphQL */'
            mutation {
                askForMagicLink(input:{
                    email: 12345
                }) {
                    status
                }
            }
        ')->assertGraphQLErrorMessage('Field "askForMagicLink" argument "input" requires type String!, found 12345.');
    }

    /**
     * @test
     */
    public function it_returns_an_error_if_the_email_field_is_not_an_email(): void
    {
        $this->graphQL(/** @lang GraphQL */'
            mutation {
                askForMagicLink(input:{
                    email: "not an email"
                    verification_url: "https://example.com/verify-email"
                }) {
                    status
                }
            }
        ')->assertGraphQLErrorMessage('Validation failed for the field [askForMagicLink].')
        ->assertGraphQLValidationError(
            'input.email',
            'Le champ input.email doit être une adresse email valide.'
        );
    }

    /**
     * @test
     */
    public function it_returns_an_error_if_the_verification_url_field_is_missing(): void
    {
        $this->graphQL(/** @lang GraphQL */'
            mutation {
                askForMagicLink(input: {
                    email: "foo@bar.com",
                }) {
                    status
                }
            }
        ')->assertGraphQLErrorMessage('Field EmailInput.verification_url of required type String! was not provided.');
    }

    /**
     * @test
     */
    public function it_returns_an_error_if_the_verification_url_field_is_not_a_string(): void
    {
        $this->graphQL(/** @lang GraphQL */'
            mutation {
                askForMagicLink(input: {
                    email: "foo@bar.com",
                    verification_url: 12345
                }) {
                    status
                }
            }
        ')->assertGraphQLErrorMessage('Field "askForMagicLink" argument "input" requires type String!, found 12345.');
    }

    /**
     * @test
     */
    public function it_returns_an_error_if_the_verification_url_field_is_not_a_url(): void
    {
        $this->graphQL(/** @lang GraphQL */'
            mutation {
                askForMagicLink(input: {
                    email: "foo@bar.com",
                    verification_url: "not a url"
                }) {
                    status
                }
            }
        ')
            ->assertGraphQLErrorMessage('Validation failed for the field [askForMagicLink].')
            ->assertGraphQLValidationError(
                'input.verification_url',
                'Le input.verification url doit être une URL valide.',
            );
    }
}