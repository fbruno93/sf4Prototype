<?php

namespace App\Core\Service;

use App\Core\Model\JwtPayload;
use DateTime;

use Exception;
use Firebase\JWT\JWT;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

/**
 * Class JwtService
 * @package App\Core\Service
 *
 * Service to manager Json Web Token
 */
class JwtService
{
    private const ALGORITHM = 'HS256';

    private string $jwtSecret;
    private string $jwtDuration;

    public function __construct(ContainerBagInterface $params)
    {
        $this->jwtSecret = $params->get('jwt_secret');
        $this->jwtDuration = $params->get('jwt_duration');
    }

    /**
     * Decode content of JWT
     *
     * @param string $credentials
     *
     * @return JwtPayload
     */
    public function decode(string $credentials): JwtPayload
    {
        $payload = (array) JWT::decode($credentials, $this->jwtSecret, [self::ALGORITHM]);
        return JwtPayload::loadFromArray($payload);
    }

    /**
     * Encode payload to JWT with an expiration date to now() + jwt_duration
     * find jwt_duration in service.yaml
     *
     * @param mixed $payload
     *
     * @return string
     */
    public function encode($payload): string
    {
        $modifier = '+'.$this->jwtDuration;
        try {
            $exp = (new DateTime())
                ->modify($modifier)
                ->getTimestamp();
        } catch (Exception $e) {
            $exp = strtotime($modifier);
        }

        $payload = array_merge($payload, [
            'exp' => $exp
        ]);

        return JWT::encode($payload, $this->jwtSecret, self::ALGORITHM);
    }
}
