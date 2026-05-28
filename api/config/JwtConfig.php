<?php
/**
 * JwtConfig — JWT token minting and validation using lcobucci/jwt v4.
 *
 * Usage:
 *   $token = JwtConfig::issue(['sub' => $userId, 'role' => 'student', 'email' => $email]);
 *   $claims = JwtConfig::validate($tokenString);  // returns array or null
 */

declare(strict_types=1);

namespace App\Config;

use DateTimeImmutable;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
use Lcobucci\Clock\SystemClock;

class JwtConfig
{
    private static ?Configuration $config = null;

    /**
     * Get JWT configuration singleton.
     */
    private static function getConfig(): Configuration
    {
        if (self::$config === null) {
            $secret = $_ENV['JWT_SECRET'] ?? 'default_secret_change_me';

            self::$config = Configuration::forSymmetricSigner(
                new Sha256(),
                InMemory::plainText($secret)
            );
        }

        return self::$config;
    }

    /**
     * Issue a new JWT token.
     *
     * @param array $claims Must include: 'sub' (user_id), 'role', 'email'
     * @return string The encoded JWT string
     */
    public static function issue(array $claims): string
    {
        $config  = self::getConfig();
        $now     = new DateTimeImmutable();
        $issuer  = $_ENV['JWT_ISSUER'] ?? 'tnp.iiitmanipur.ac.in';
        $expiry  = (int) ($_ENV['JWT_EXPIRY_HOURS'] ?? 24);

        $builder = $config->builder()
            ->issuedBy($issuer)
            ->issuedAt($now)
            ->expiresAt($now->modify("+{$expiry} hours"))
            ->relatedTo($claims['sub'] ?? '')
            ->withClaim('role', $claims['role'] ?? '')
            ->withClaim('email', $claims['email'] ?? '');

        // Allow extra claims (skip registered ones)
        $reserved = ['sub', 'role', 'email', 'iss', 'iat', 'exp', 'nbf', 'jti', 'aud'];
        foreach ($claims as $key => $value) {
            if (!in_array($key, $reserved, true)) {
                $builder = $builder->withClaim($key, $value);
            }
        }

        return $builder
            ->getToken($config->signer(), $config->signingKey())
            ->toString();
    }

    /**
     * Validate and parse a JWT token string.
     *
     * @param string $tokenString The raw JWT string
     * @return array|null Decoded claims array, or null if invalid/expired
     */
    public static function validate(string $tokenString): ?array
    {
        try {
            $config = self::getConfig();
            $token  = $config->parser()->parse($tokenString);

            // Validate signature only
            $signedWith = new SignedWith($config->signer(), $config->signingKey());

            if (!$config->validator()->validate($token, $signedWith)) {
                return null;
            }

            // Manual expiry check (avoids microsecond precision issues with StrictValidAt)
            $exp = $token->claims()->get('exp');
            if ($exp instanceof DateTimeImmutable && $exp < new DateTimeImmutable('now', new \DateTimeZone('UTC'))) {
                return null;
            }

            return [
                'sub'   => $token->claims()->get('sub'),
                'role'  => $token->claims()->get('role'),
                'email' => $token->claims()->get('email'),
                'iat'   => $token->claims()->get('iat'),
                'exp'   => $token->claims()->get('exp'),
            ];
        } catch (\Exception $e) {
            error_log("JWT validation error: " . $e->getMessage());
            return null;
        }
    }
}
