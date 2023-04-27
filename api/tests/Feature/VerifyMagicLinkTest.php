<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\MagicLink;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class VerifyMagicLinkTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * @test
     */
    public function it_authenticates_a_user_and_verifies_his_email(): void
    {
        Carbon::setTestNow(Carbon::createFromTimestamp(1609477200));

        $user = User::factory()->create([
            'id' => '01gr552mzdsdarf2349syvvr8q',
            'email' => 'john.doe@gmail.com',
            'email_verified_at' => null,
        ]);

        $signature = hash_hmac('sha256', serialize([
            'id'      => '01gr552mzdsdarf2349syvvr8q',
            'hash'    => sha1('john.doe@gmail.com'),
            'expires' => 1609480800,
        ]), config('app.key'));

        $this->graphQL(/** @lang GraphQL */ '
            mutation {
                verifyMagicLink(input: {
                    id: "01gr552mzdsdarf2349syvvr8q",
                    hash: "' . sha1('john.doe@gmail.com') . '",
                    expires: 1609480800,
                    signature: "' . $signature . '"
                }) {
                    token
                }
            }
        ')->assertJsonStructure([
            'data' => [
                'verifyMagicLink' => [
                    'token',
                ],
            ],
        ]);

        $user->refresh();

        $this->assertNotNull($user->getAttribute('email_verified_at'));
    }

    /**
     * @test
     */
    public function it_returns_an_error_if_the_user_is_not_found(): void
    {
        $this->graphQL(/** @lang GraphQL */ '
            mutation {
                verifyMagicLink(input: {
                    id: "foo"
                    hash: "random"
                    expires: 123
                    signature: "random"
                }) {
                    token   
                }
            }
        ')->assertGraphQLErrorMessage('Unauthenticated.');
    }

    /**
     * @test
     */
    public function it_returns_an_error_if_the_hash_is_incorrect(): void
    {
        User::factory()->create([
            'id' => '01gr552mzdsdarf2349syvvr8q'
        ]);

        $this->graphQL(/** @lang GraphQL */ '
            mutation {
                verifyMagicLink(input: {
                    id: "01gr552mzdsdarf2349syvvr8q"
                    hash: "random"
                    expires: 123
                    signature: "random"
                }) {
                    token   
                }
            }
        ')->assertGraphQLErrorMessage('Unauthenticated.');
    }
    
    /**
     * @test
     */
    public function it_returns_an_error_if_the_expires_is_incorrect(): void
    {
        Carbon::setTestNow(Carbon::createFromTimestamp(1609477200));

        User::factory()->create([
            'id'                => '01gr552mzdsdarf2349syvvr8q',
            'email'             => 'john.doe@gmail.com',
            'email_verified_at' => null,
        ]);

        $signature = hash_hmac('sha256', serialize([
            'id'      => '01gr552mzdsdarf2349syvvr8q',
            'hash'    => sha1('john.doe@gmail.com'),
            'expires' => 1609480800,
        ]), config('app.key'));

        $this->graphQL(/** @lang GraphQL */ '
            mutation {
                verifyMagicLink(input: {
                    id: "01gr552mzdsdarf2349syvvr8q",
                    hash: "' . sha1('john.doe@gmail.com') . '"
                    expires: 123,
                    signature: "' . $signature . '"
                }) {
                    token
                }
            }
        ')->assertGraphQLErrorMessage('Unauthenticated.');
    }

    /**
     * @test
     */
    public function it_returns_an_error_if_the_signature_has_expired(): void
    {
        Carbon::setTestNow(Carbon::createFromTimestamp(1609477200));

        User::factory()->create([
            'id'                => '01gr552mzdsdarf2349syvvr8q',
            'email'             => 'john.doe@gmail.com',
            'email_verified_at' => null,
        ]);

        $signature = hash_hmac('sha256', serialize([
            'id'      => '01gr552mzdsdarf2349syvvr8q',
            'hash'    => sha1('john.doe@gmail.com'),
            'expires' => 1609476200,
        ]), config('app.key'));

        $this->graphQL(/** @lang GraphQL */ '
            mutation {
                verifyMagicLink(input: {
                    id: "01gr552mzdsdarf2349syvvr8q",
                    hash: "' . sha1('john.doe@gmail.com') . '"
                    expires: 1609476200,
                    signature: "' . $signature . '"
                }) {
                    token
                }
            }
        ')->assertGraphQLErrorMessage('Unauthenticated.');
    }

    /**
     * @test
     */
    public function it_returns_an_error_if_the_signature_is_incorrect(): void
    {
        Carbon::setTestNow(Carbon::createFromTimestamp(1609477200));

        User::factory()->create([
            'id'                => '01gr552mzdsdarf2349syvvr8q',
            'email'             => 'john.doe@gmail.com',
            'email_verified_at' => null,
        ]);

        $this->graphQL(/** @lang GraphQL */ '
            mutation {
                verifyMagicLink(input: {
                    id: "01gr552mzdsdarf2349syvvr8q",
                    hash: "' . sha1('john.doe@gmail.com') . '"
                    expires: 1609480800,
                    signature: "1234567890"
                }) {
                    token
                }
            }
        ')->assertGraphQLErrorMessage('Unauthenticated.');
    }

    /**
     * @test
     */
    public function it_returns_an_error_if_the_id_field_is_missing(): void
    {
        $this->graphQL(/** @lang GraphQL */ '
            mutation {
                verifyMagicLink(input: {
                    hash: "foobar"
                }) {
                    token
                }
            }
        ')->assertGraphQLErrorMessage('Field MagicLinkInput.id of required type ID! was not provided.');
    }

    /**
     * @test
     */
    public function it_returns_an_error_if_the_id_field_is_not_an_id(): void
    {
        $this->graphQL(/** @lang GraphQL */ '
            mutation {
                verifyMagicLink(input: {
                    id: true,
                    hash: "foobar"
                }) {
                    token
                }
            }
        ')->assertGraphQLErrorMessage('Field "verifyMagicLink" argument "input" requires type ID!, found true.');
    }

    /**
     * @test
     */
    public function it_returns_an_error_if_the_hash_field_is_missing(): void
    {
        $this->graphQL(/** @lang GraphQL */ '
            mutation {
                verifyMagicLink(input: {
                    id: 123,
                }) {
                    token
                }
            }
        ')->assertGraphQLErrorMessage('Field MagicLinkInput.hash of required type String! was not provided.');
    }

    /**
     * @test
     */
    public function it_returns_an_error_if_the_hash_field_is_not_a_string(): void
    {
        $this->graphQL(/** @lang GraphQL */ '
            mutation {
                verifyMagicLink(input: {
                    id: 123,
                    hash: 12345
                }) {
                    token
                }
            }
        ')->assertGraphQLErrorMessage('Field "verifyMagicLink" argument "input" requires type String!, found 12345.');
    }

    /**
     * @test
     */
    public function it_returns_an_error_if_the_expires_field_is_missing(): void
    {
        $this->graphQL(/** @lang GraphQL */ '
            mutation {
                verifyMagicLink(input: {
                    id: "01gr552mzdsdarf2349syvvr8q",
                    hash: "foobar",
                    signature: "1234567890"
                }) {
                    token
                }
            }
        ')
            ->assertGraphQLErrorMessage('Field MagicLinkInput.expires of required type Int! was not provided.');
    }

    /**
     * @test
     */
    public function it_returns_an_error_if_the_expires_field_is_not_an_int(): void
    {
        $this->graphQL(/** @lang GraphQL */ '
            mutation {
                verifyMagicLink(input: {
                    id: "01gr552mzdsdarf2349syvvr8q",
                    hash: "foobar",
                    expires: true,
                    signature: "1234567890"
                }) {
                    token
                }
            }
        ')->assertGraphQLErrorMessage('Field "verifyMagicLink" argument "input" requires type Int!, found true.');
    }

    /**
     * @test
     */
    public function it_returns_an_error_if_the_signature_field_is_missing(): void
    {
        $this->graphQL(/** @lang GraphQL */ '
            mutation {
                verifyMagicLink(input: {
                    id: "01gr552mzdsdarf2349syvvr8q",
                    hash: "foobar",
                    expires: 1609480800
                }) {
                    token
                }
            }
        ')
            ->assertGraphQLErrorMessage('Field MagicLinkInput.signature of required type String! was not provided.');
    }

    /**
     * @test
     */
    public function it_returns_an_error_if_the_signature_field_is_not_a_string(): void
    {
        $this->graphQL(/** @lang GraphQL */ '
            mutation {
                verifyMagicLink(input: {
                    id: "01gr552mzdsdarf2349syvvr8q",
                    hash: "foobar",
                    expires: 1609480800,
                    signature: 12345
                }) {
                    token
                }
            }
        ')->assertGraphQLErrorMessage('Field "verifyMagicLink" argument "input" requires type String!, found 12345.');
    }
}
