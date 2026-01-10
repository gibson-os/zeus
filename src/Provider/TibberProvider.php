<?php
declare(strict_types=1);

use GibsonOS\Core\Attribute\GetEnv;
use GibsonOS\Core\Dto\Web\Body;
use GibsonOS\Core\Dto\Web\Request;
use GibsonOS\Core\Dto\Web\Response;
use GibsonOS\Core\Mapper\GraphQlQueryMapper;
use GibsonOS\Core\Mapper\ModelMapper;
use GibsonOS\Core\Service\WebService;
use GibsonOS\Core\Utility\JsonUtility;
use Model\Price;

class TibberProvider
{
    public function __construct(
        private readonly WebService $webService,
        private readonly GraphQlQueryMapper $graphQlQueryMapper,
        private readonly ModelMapper $modelMapper,
        #[GetEnv('TIBBER_ACCESS_TOKEN')]
        private readonly ?string $accessToken,
    ) {
    }

    /*
     * @return array{
     *  current: ?Price,
     *  today: Price[],
     *  tomorrow: Price[],
     * }
     */
    public function getPrices()
    {
        $prices = [
            'current' => null,
            'today' => [],
            'tomorrow' => [],
        ];

        if ($this->accessToken === null) {
            return $prices;
        }

        $priceInfoQuery = [
            'total',
            'energy',
            'tax',
            'startsAt',
        ];
        $response = $this->request([
            'viewer' => [
                'homes' => [
                    'currentSubscription' => [
                        'priceInfo' => [
                            'current' => $priceInfoQuery,
                            'today' => $priceInfoQuery,
                            'tomorrow' => $priceInfoQuery,
                        ],
                    ],
                ],
            ],
        ]);

        $responseBody = JsonUtility::decode($response->getBody()->getContent());

        foreach ($responseBody['data']['viewer']['homes'] as $home) {
            $priceInfo = $home['currentSubscription']['priceInfo'];
            $prices['current'] = $this->modelMapper->mapToObject(Price::class, $priceInfo['current'])
                ->setCurrent(true)
            ;

            foreach ($priceInfo['today'] as $price) {
                $prices['today'][] = $this->modelMapper->mapToObject(Price::class, $price);
            }

            foreach ($priceInfo['tomorrow'] as $price) {
                $prices['tomorrow'][] = $this->modelMapper->mapToObject(Price::class, $price);
            }
        }

        return $prices;
    }

    private function request(array $query): Response
    {
        $queryString = $this->graphQlQueryMapper->mapToString($query);
        $request = new Request('https://api.tibber.com/v1-beta/gql')
            ->setHeader('Authorization', sprintf('Bearer %s', $this->accessToken ?? ''))
            ->setHeader('Content-Type', 'application/json')
            ->setHeader('User-Agent', 'Homey/10.0.0 com.tibber/1.8.3')
            ->setBody(new Body()->setContent($queryString, mb_strlen($queryString)))
        ;

        return $this->webService->post($request);
    }
}
