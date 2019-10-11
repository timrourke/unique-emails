<?php

declare(strict_types=1);

namespace UniqueEmails\UnitTests;

use Psr\Http\Message\RequestInterface;
use UniqueEmails\InvalidRequestException;
use UniqueEmails\UniqueEmailsResponse;
use PHPUnit\Framework\TestCase;
use Zend\Diactoros\Request;

class UniqueEmailsResponseTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnNumberOfUniqueEmails(): void
    {
        $request = $this->createRequestWithBody(json_encode([
            'emails' => [
                'foo@bar.com',
                'timothyrourke@gmail.com',
                'timothy.rourke@gmail.com',
                'timothy.rourke+somelabel@gmail.com',
            ],
        ]));

        $response = UniqueEmailsResponse::fromRequest($request);

        static::assertSame(
            [
                'numUniqueEmails' => 2,
                'uniqueEmails' => [
                    'foo@bar.com',
                    'timothyrourke@gmail.com',
                ],
            ],
            json_decode(json_encode($response), true)
        );
    }

    /**
     * @test
     */
    public function shouldReturnZeroUniqueEmailsWhenNoneProvided(): void
    {
        $request = $this->createRequestWithBody(json_encode([
            'emails' => [],
        ]));

        $response = UniqueEmailsResponse::fromRequest($request);

        static::assertSame(
            [
                'numUniqueEmails' => 0,
                'uniqueEmails' => []
            ],
            json_decode(json_encode($response), true)
        );
    }

    /**
     * @test
     * @dataProvider emailProvider
     * @param string $emailToStandardize
     * @param string $expectedEmail
     */
    public function shouldStandardizeEmail(string $emailToStandardize, string $expectedEmail): void
    {
        static::assertSame(
            $expectedEmail,
            UniqueEmailsResponse::standardizeEmail($emailToStandardize)
        );
    }

    public function emailProvider(): array
    {
        return [
            'Should not change already standard email' => [
                'timothyrourke@gmail.com',
                'timothyrourke@gmail.com',
            ],
            'Should remove dots from first segment of email' => [
                'timothy.rourke@gmail.com',
                'timothyrourke@gmail.com',
            ],
            'Should remove label from first segment of email' => [
                'timothyrourke+somelabel@gmail.com',
                'timothyrourke@gmail.com',
            ],
            'Should not remove dots from domain' => [
                'timothyrourke@some.imaginary.domain.org',
                'timothyrourke@some.imaginary.domain.org',
            ],
        ];
    }

    /**
     * @test
     */
    public function shouldFailToStandardizeInvalidEmail(): void
    {
        static::expectException(InvalidRequestException::class);
        static::expectDeprecationMessage(
            'Email address appears invalid: something that is clearly not an email address'
        );

        UniqueEmailsResponse::standardizeEmail('something that is clearly not an email address');
    }

    /**
     * @test
     */
    public function shouldFailToParseNonJsonRequest(): void
    {
        static::expectException(InvalidRequestException::class);
        static::expectDeprecationMessage('Failed to parse request body JSON');

        $invalidRequest = $this->createRequestWithBody('{{]]');

        UniqueEmailsResponse::fromRequest($invalidRequest);
    }

    /**
     * @test
     */
    public function shouldFailToParseScalarRequest(): void
    {
        static::expectException(InvalidRequestException::class);
        static::expectDeprecationMessage('Required JSON key "emails" does not exist');

        $invalidRequest = $this->createRequestWithBody('true');

        UniqueEmailsResponse::fromRequest($invalidRequest);
    }

    /**
     * @test
     */
    public function shouldFailToParseRequestLackingKeyEmails(): void
    {
        static::expectException(InvalidRequestException::class);
        static::expectDeprecationMessage('Required JSON key "emails" does not exist');

        $invalidRequest = $this->createRequestWithBody(json_encode([
            'invalid' => 'payload',
            'because' => 'key email is not defined',
        ]));

        UniqueEmailsResponse::fromRequest($invalidRequest);
    }

    /**
     * @test
     */
    public function shouldFailToParseRequestWhereEmailsKeyIsNotAnArray(): void
    {
        static::expectException(InvalidRequestException::class);
        static::expectDeprecationMessage('Expected JSON key "emails" to contain an array of strings');

        $invalidRequest = $this->createRequestWithBody(json_encode([
            'emails' => 'key is invalid because it is not an array',
        ]));

        UniqueEmailsResponse::fromRequest($invalidRequest);
    }

    private function createRequestWithBody(string $body): RequestInterface
    {
        $request = new Request();

        $request->getBody()->write($body);
        $request->getBody()->rewind();

        return $request;
    }
}
