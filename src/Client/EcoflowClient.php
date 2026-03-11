<?php
declare(strict_types=1);

namespace GibsonOS\Module\Zeus\Client;

use DateTimeInterface;
use GibsonOS\Core\Dto\Web\Body;
use GibsonOS\Core\Dto\Web\Request;
use GibsonOS\Core\Service\DateTimeService;
use GibsonOS\Core\Service\WebService;
use GibsonOS\Core\Utility\JsonUtility;
use GibsonOS\Module\Zeus\Enum\QuotaCode;
use GibsonOS\Module\Zeus\Model\Device;

class EcoflowClient
{
    private const string URL = 'https://api-e.ecoflow.com/';

    public function __construct(
        private readonly WebService $webService,
        private readonly DateTimeService $dateTimeService,
    ) {
    }

    public function getDeviceList(string $accessKey, string $secretKey): array
    {
        $request = $this->getRequest($accessKey, $secretKey, 'iot-open/sign/device/list');
        $response = $this->webService->get($request);

        return JsonUtility::decode($response->getBody()->getContent())['data'] ?? [];
    }

    public function getDeviceQuotaAll(string $accessKey, string $secretKey, Device $device): array
    {
        $parameters = ['sn' => $device->getSerialNumber()];
        $request = $this->getRequest(
            $accessKey,
            $secretKey,
            'iot-open/sign/device/quota/all',
            $parameters,
        )
            ->setParameters($parameters)
        ;
        $response = $this->webService->get($request);

        return JsonUtility::decode($response->getBody()->getContent());
    }

    public function getDeviceQuota(
        string $accessKey,
        string $secretKey,
        Device $device,
        QuotaCode $code,
        DateTimeInterface $from,
        DateTimeInterface $to,
    ): array {
        $parameters = [
            'sn' => $device->getSerialNumber(),
            'params' => [
                'beginTime' => $from->format('Y-m-d H:i:s'),
                'endTime' => $to->format('Y-m-d H:i:s'),
                'code' => $code->value,
            ],
        ];
        $jsonBody = JsonUtility::encode($parameters);
        $request = $this->getRequest(
            $accessKey,
            $secretKey,
            'iot-open/sign/device/quota/data',
            $parameters,
        )
            ->setBody(new Body()->setContent($jsonBody, mb_strlen($jsonBody)))
        ;
        $response = $this->webService->post($request);

        return JsonUtility::decode($response->getBody()->getContent());
    }

    private function getRequest(
        string $accessKey,
        string $secretKey,
        string $path,
        array $parameters = [],
    ): Request {
        $nonce = (string) mt_rand(100000, 999999);
        $timestamp = (string) ($this->dateTimeService->get()->getTimestamp() * 1000);
        $url = sprintf('%s%s', self::URL, $path);

        return new Request($url)
            ->setHeader('Accept', 'application/json;charset=UTF-8')
            ->setHeader('Content-Type', 'application/json;charset=UTF-8')
            ->setHeader('accessKey', $accessKey)
            ->setHeader('nonce', $nonce)
            ->setHeader('timestamp', $timestamp)
            ->setHeader('sign', $this->generateSignature($parameters, $accessKey, $secretKey, $nonce, $timestamp))
        ;
    }

    private function flatArrayParameter(string $key, array $values): array
    {
        $parameters = [];

        foreach ($values as $itemKey => $value) {
            $parameterKey = sprintf('%s%s', $key, $itemKey);

            if (is_array($value)) {
                $parameters = array_merge($parameters, $this->flatArrayParameter(sprintf('%s.', $parameterKey), $value));

                continue;
            }

            $parameters[$parameterKey] = $value;
        }

        return $parameters;
    }

    private function generateSignature(
        array $parameters,
        string $accessKey,
        string $secretKey,
        string $nonce,
        string $timestamp,
    ): string {
        $parameters = $this->flatArrayParameter('', $parameters);
        ksort($parameters, SORT_STRING);

        $parameters['accessKey'] = $accessKey;
        $parameters['nonce'] = $nonce;
        $parameters['timestamp'] = $timestamp;

        // $paramString = http_build_query($parameters, '', '&', PHP_QUERY_RFC3986);
        $paramString = implode(
            '&',
            array_map(
                static fn (string $key, string|int|float|bool $value): string => sprintf(
                    '%s=%s',
                    $key,
                    is_bool($value) ? ($value ? 'true' : 'false') : (string) $value,
                ),
                array_keys($parameters),
                array_values($parameters),
            ),
        );

        return bin2hex(hash_hmac('sha256', $paramString, $secretKey, true));
    }
}
