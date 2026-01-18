<?php
declare(strict_types=1);

namespace GibsonOS\Module\Zeus\Client;

use DateTimeInterface;
use GibsonOS\Core\Attribute\GetEnv;
use GibsonOS\Core\Dto\Web\Request;
use GibsonOS\Core\Enum\HttpMethod;
use GibsonOS\Core\Exception\WebException;
use GibsonOS\Core\Service\DateTimeService;
use GibsonOS\Core\Service\WebService;
use GibsonOS\Core\Utility\JsonUtility;
use GibsonOS\Module\Zeus\Dto\InexogyConsumer;

class InexogyClient
{
    private const string URL = 'https://api.inexogy.com/public/v1/';

    public function __construct(
        private readonly DateTimeService $dateTimeService,
        private readonly WebService $webService,
        #[GetEnv('APP_NAME')]
        private readonly string $appName,
    ) {
    }

    /**
     * @throws WebException
     */
    public function getConsumerToken(InexogyConsumer $consumer): InexogyConsumer
    {
        if (
            $consumer->getKey() !== null
            && $consumer->getSecret() !== null
        ) {
            return $consumer;
        }

        $request = new Request(sprintf('%soauth1/consumer_token', self::URL))
            ->setHeader('Accept', 'text/html, image/gif, image/jpeg, *; q=.2, */*; q=.2')
            ->setParameter('client', sprintf('%s-client', $this->appName))
        ;
        $response = $this->webService->post($request);
        $content = JsonUtility::decode($response->getBody()->getContent());

        return $consumer
            ->setKey($content['key'] ?? null)
            ->setSecret($content['secret'] ?? null)
        ;
    }

    /**
     * @throws WebException
     */
    public function getRequestToken(InexogyConsumer $consumer): InexogyConsumer
    {
        $consumer
            ->setRequestToken(null)
            ->setRequestTokenSecret(null)
            ->setAccessToken(null)
            ->setAccessTokenSecret(null)
        ;
        $request = $this->getOAuthRequest(
            $consumer,
            HttpMethod::POST,
            'oauth1/request_token',
            [
                'oauth_callback' => 'oob',
            ],
        );
        $response = $this->webService->post($request);
        parse_str($response->getBody()->getContent(), $result);

        return $consumer
            ->setRequestToken($result['oauth_token'] ?? null)
            ->setRequestTokenSecret($result['oauth_token_secret'] ?? null)
        ;
    }

    /**
     * @throws WebException
     */
    public function getAuthorize(InexogyConsumer $consumer): InexogyConsumer
    {
        $request = new Request(sprintf('%soauth1/authorize', self::URL))
            ->setHeader('Accept', 'text/html, image/gif, image/jpeg, *; q=.2, */*; q=.2')
            ->setParameter('oauth_token', $consumer->getRequestToken() ?? '')
            ->setParameter('email', $consumer->getEmail())
            ->setParameter('password', $consumer->getPassword())
        ;

        $response = $this->webService->get($request);
        parse_str($response->getBody()->getContent(), $result);

        return $consumer->setVerifier($result['oauth_verifier'] ?? null);
    }

    /**
     * @throws WebException
     */
    public function getAccessToken(InexogyConsumer $consumer): InexogyConsumer
    {
        $consumer
            ->setAccessToken(null)
            ->setAccessTokenSecret(null)
        ;
        $request = $this->getOAuthRequest(
            $consumer,
            HttpMethod::POST,
            'oauth1/access_token',
            [
                'oauth_verifier' => $consumer->getVerifier(),
            ],
        );
        $response = $this->webService->post($request);
        parse_str($response->getBody()->getContent(), $result);

        return $consumer
            ->setAccessToken($result['oauth_token'] ?? null)
            ->setAccessTokenSecret($result['oauth_token_secret'] ?? null)
        ;
    }

    //    public function getMeters(InexogyConsumer $consumer): array
    //    {
    //        $request = $this->getOAuthRequest(
    //            $consumer,
    //            HttpMethod::GET,
    //            'meters',
    //            [
    //                'oauth_consumer_key' => $consumer->getKey(),
    //                'oauth_signature_method' => 'HMAC-SHA1',
    //                'oauth_timestamp' => (string) $this->dateTimeService->get()->getTimestamp(),
    //                'oauth_token' => $consumer->getRequestToken(),
    //                'oauth_version' => '1.0',
    //            ],
    //        );
    //        $response = $this->webService->get($request);
    //
    //        return JsonUtility::decode($response->getBody()->getContent());
    //    }

    /**
     * @throws WebException
     *
     * @return array<array{
     *     meterId: string,
     *     manufactureId: string,
     *     serialNumber: string,
     *     fullSerialNumber: string,
     *     printedFullSerialNumber: string,
     *     location: array{
     *         street: string,
     *         streetNumber: string,
     *         zip: string,
     *         city: string,
     *         country: string,
     *     },
     *     administrationNumber: string,
     *     type: string,
     *     measurementType: string,
     *     loadProfileType: string,
     *     scalingFactor: int,
     *     currentScalingFactor: int,
     *     voltageScalingFactor: int,
     *     internalMeters: int,
     *     firstMeasurementTime: int,
     *     submeter: bool,
     * }>
     */
    public function getMeters(InexogyConsumer $consumer): array
    {
        $nonce = uniqid();
        $parameters = [
            'oauth_consumer_key' => $consumer->getKey() ?? '',
            'oauth_nonce' => $nonce,
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => (string) $this->dateTimeService->get()->getTimestamp(),
            'oauth_token' => $consumer->getAccessToken() ?? '',
            'oauth_version' => '1.0',
        ];
        $url = sprintf('%smeters', self::URL);
        $parameters['oauth_signature'] = $this->generateSignature('GET', $url, $parameters, $consumer);
        $request = new Request($url)
            ->setHeader('Accept', 'text/html, image/gif, image/jpeg, *; q=.2, */*; q=.2')
            ->setHeader(
                'Authorization',
                sprintf(
                    'OAuth %s',
                    implode(', ', array_map(
                        static fn (string $key, string $value): string => sprintf('%s="%s"', $key, $value),
                        array_keys($parameters),
                        $parameters,
                    )),
                ),
            )
        ;
        $response = $this->webService->get($request);

        return JsonUtility::decode($response->getBody()->getContent());
    }

    /**
     * @throws WebException
     */
    public function getReadings(
        InexogyConsumer $consumer,
        string $meterId,
        DateTimeInterface $from,
        DateTimeInterface $to,
    ): array {
        $nonce = uniqid();
        $parameters = [
            'oauth_consumer_key' => $consumer->getKey() ?? '',
            'oauth_nonce' => $nonce,
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => (string) $this->dateTimeService->get()->getTimestamp(),
            'oauth_token' => $consumer->getAccessToken() ?? '',
            'oauth_version' => '1.0',
        ];
        $signatureParameters = $parameters;
        $signatureParameters['meterId'] = $meterId;
        $from = (string) ($from->getTimestamp() * 1000);
        $to = (string) ($to->getTimestamp() * 1000);
        $signatureParameters['from'] = $from;
        $signatureParameters['to'] = $to;
        $url = sprintf('%sreadings', self::URL);
        $parameters['oauth_signature'] = $this->generateSignature('GET', $url, $signatureParameters, $consumer);
        $request = new Request($url)
            ->setHeader('Accept', 'text/html, image/gif, image/jpeg, *; q=.2, */*; q=.2')
            ->setHeader(
                'Authorization',
                sprintf(
                    'OAuth %s',
                    implode(', ', array_map(
                        static fn (string $key, string $value): string => sprintf('%s="%s"', $key, $value),
                        array_keys($parameters),
                        $parameters,
                    )),
                ),
            )
            ->setParameter('meterId', $meterId)
            ->setParameter('from', $from)
            ->setParameter('to', $to)
        ;
        $response = $this->webService->get($request);

        return JsonUtility::decode($response->getBody()->getContent());
    }

    private function getOAuthRequest(
        InexogyConsumer $consumer,
        HttpMethod $method,
        string $path,
        array $parameters = [],
    ): Request {
        $url = sprintf('%s%s', self::URL, $path);
        $parameters['nonce'] = uniqid();
        $parameters['oauth_signature_method'] = 'HMAC-SHA1';
        $parameters['oauth_timestamp'] = (string) $this->dateTimeService->get()->getTimestamp();
        $parameters['version'] = '1.0';
        $parameters['oauth_consumer_key'] = $consumer->getKey();
        $authToken = $consumer->getAccessToken() ?? $consumer->getRequestToken();

        if ($authToken !== null) {
            $parameters['oauth_token'] = $authToken;
        }

        $parameters['oauth_signature'] = $this->generateSignature(strtoupper($method->value), $url, $parameters, $consumer);

        return new Request($url)
            ->setHeader('Accept', 'text/html, image/gif, image/jpeg, *; q=.2, */*; q=.2')
            ->setHeader(
                'Authorization',
                sprintf(
                    'OAuth %s',
                    implode(', ', array_map(
                        static fn (string $key, string $value): string => sprintf('%s="%s"', $key, $value),
                        array_keys($parameters),
                        $parameters,
                    )),
                ),
            )
        ;
    }

    private function generateSignature(
        string $method,
        string $url,
        array $params,
        InexogyConsumer $consumerToken,
    ): string {
        ksort($params);

        $paramString = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        $baseString = strtoupper($method) . '&'
            . rawurlencode($url) . '&'
            . rawurlencode($paramString)
        ;

        $signingKey = rawurlencode($consumerToken->getSecret() ?? '') . '&'
            . rawurlencode($consumerToken->getAccessTokenSecret() ?? $consumerToken->getRequestTokenSecret() ?? '')
        ;

        return base64_encode(hash_hmac('sha1', $baseString, $signingKey, true));
    }
}
