<?php
declare(strict_types=1);

namespace GibsonOS\Module\Zeus\Provider;

use GibsonOS\Core\Attribute\GetEnv;
use GibsonOS\Core\Dto\Web\Request;
use GibsonOS\Core\Service\DateTimeService;
use GibsonOS\Core\Service\WebService;
use GibsonOS\Core\Utility\JsonUtility;

class InexogyProvider
{
    private const string URL = 'https://api.inexogy.com/public/v1/';

    public function __construct(
        private readonly DateTimeService $dateTimeService,
        private readonly WebService $webService,
        #[GetEnv('APP_NAME')]
        private readonly string $appName,
    ) {
    }

    public function getConsumerToken(): array
    {
        $request = new Request(sprintf('%soauth1/consumer_token', self::URL))
            ->setHeader('Accept', 'text/html, image/gif, image/jpeg, *; q=.2, */*; q=.2')
            ->setParameter('client', sprintf('%s-client', $this->appName))
        ;
        $response = $this->webService->post($request);

        return JsonUtility::decode($response->getBody()->getContent());
    }

    public function getRequestToken(): string
    {
        $consumerToken = $this->getConsumerToken();
        $nonce = uniqid();
        $parameters = [
            'oauth_callback' => 'oob',
            'oauth_consumer_key' => $consumerToken['key'],
            'oauth_nonce' => $nonce,
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => (string) $this->dateTimeService->get()->getTimestamp(),
            'oauth_version' => '1.0',
        ];
        $signatureParameters = $parameters;
        ksort($signatureParameters);

        $url = sprintf('%soauth1/request_token', self::URL);
        $parameters['oauth_signature'] = $this->generateSignature('POST', $url, $signatureParameters, $consumerToken['secret']);
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

        $response = $this->webService->post($request);
        parse_str($response->getBody()->getContent(), $result);

        return $result['oauth_token'] ?? '';
    }

    private function generateSignature(
        string $method,
        string $url,
        array $params,
        string $tokenSecret,
    ): string {
        ksort($params);

        $paramString = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        $baseString = strtoupper($method) . '&'
            . rawurlencode($url) . '&'
            . rawurlencode($paramString);

        $signingKey = rawurlencode($tokenSecret) . '&' . rawurlencode('');

        return base64_encode(hash_hmac('sha1', $baseString, $signingKey, true));
    }
}
