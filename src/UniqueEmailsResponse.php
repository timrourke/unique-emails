<?php

declare(strict_types=1);

namespace UniqueEmails;

use JsonSerializable;
use Psr\Http\Message\RequestInterface;

final class UniqueEmailsResponse implements JsonSerializable
{
    private const JSON_KEY_EMAILS = 'emails';

    private $emails;

    private function __construct(string ...$emails)
    {
        $this->emails = $emails;
    }

    public function jsonSerialize(): array
    {
        return [
            'numUniqueEmails' => count($this->emails),
            'uniqueEmails' => $this->emails,
        ];
    }

    public static function fromRequest(RequestInterface $request): UniqueEmailsResponse
    {
        $body = $request->getBody()->getContents();

        $bodyJson = json_decode($body, true);

        if (null === $bodyJson) {
            throw new InvalidRequestException('Failed to parse request body JSON');
        }

        if (!is_array($bodyJson) || !array_key_exists(self::JSON_KEY_EMAILS, $bodyJson)) {
            throw new InvalidRequestException('Required JSON key "emails" does not exist');
        }

        if (!is_array($bodyJson[self::JSON_KEY_EMAILS])) {
            throw new InvalidRequestException('Expected JSON key "emails" to contain an array of strings');
        }

        $uniqueEmails = self::filterEmails(...$bodyJson[self::JSON_KEY_EMAILS]);

        return new UniqueEmailsResponse(...array_keys($uniqueEmails));
    }

    private static function filterEmails(string ...$emails): array
    {
        return array_reduce(
            $emails,
            function (array $acc, string $email): array {
                if (array_key_exists($email, $acc)) {
                    return $acc;
                }

                $standardizedEmail = self::standardizeEmail($email);

                if (array_key_exists($standardizedEmail, $acc)) {
                    return $acc;
                }

                return array_merge(
                    $acc,
                    [$standardizedEmail => $email]
                );
            },
            []
        );
    }

    public static function standardizeEmail(string $email): string
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidRequestException(
                sprintf('Email address appears invalid: %s', $email)
            );
        }

        list($beforeAt, $afterAt) = mb_split("@", $email);

        $beforeAtWithoutDots = str_replace(".", "", $beforeAt);
        $beforeAtWithoutDotsAndLabel = preg_replace('/\+.*$/', '', $beforeAtWithoutDots);

        return sprintf(
            '%s@%s',
            $beforeAtWithoutDotsAndLabel,
            $afterAt
        );
    }
}
