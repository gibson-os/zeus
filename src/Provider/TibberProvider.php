<?php
declare(strict_types=1);

namespace GibsonOS\Module\Zeus\Provider;

use GibsonOS\Core\Dto\Web\Body;
use GibsonOS\Core\Dto\Web\Request;
use GibsonOS\Core\Dto\Web\Response;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\MapperException;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\ViolationException;
use GibsonOS\Core\Exception\WebException;
use GibsonOS\Core\Mapper\GraphQlQueryMapper;
use GibsonOS\Core\Mapper\ModelMapper;
use GibsonOS\Core\Service\WebService;
use GibsonOS\Core\Utility\JsonUtility;
use GibsonOS\Core\Wrapper\ModelWrapper;
use GibsonOS\Module\Zeus\Model\Home;
use GibsonOS\Module\Zeus\Model\Price;
use JsonException;
use MDO\Exception\RecordException;
use ReflectionException;

class TibberProvider
{
    public function __construct(
        private readonly WebService $webService,
        private readonly GraphQlQueryMapper $graphQlQueryMapper,
        private readonly ModelMapper $modelMapper,
        private readonly ModelWrapper $modelWrapper,
    ) {
    }

    /**
     * @throws FactoryError
     * @throws JsonException
     * @throws MapperException
     * @throws ReflectionException
     * @throws WebException
     * @throws SaveError
     * @throws ViolationException
     * @throws RecordException
     *
     * @return Price[]
     */
    public function getPrices(string $accessToken): array
    {
        $priceInfoQuery = [
            'total',
            'energy',
            'tax',
            'startsAt',
        ];
        $response = $this->request($accessToken, [
            'viewer' => [
                'homes' => [
                    'id',
                    'appNickname',
                    'size',
                    'numberOfResidents',
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

        $prices = [];
        $responseBody = JsonUtility::decode($response->getBody()->getContent());

        foreach ($responseBody['data']['viewer']['homes'] ?? [] as $home) {
            $homeModel = new Home($this->modelWrapper)
                ->setForeignId($home['id'])
                ->setAccessToken($accessToken)
                ->setName($home['appNickname'])
                ->setSize($home['size'])
                ->setResidents($home['numberOfResidents'])
            ;
            $this->modelWrapper->getModelManager()->saveWithoutChildren($homeModel);
            $priceInfo = $home['currentSubscription']['priceInfo'] ?? [];

            foreach ($priceInfo['today'] ?? [] as $price) {
                $prices[$price['startsAt']] = $this->modelMapper->mapToObject(Price::class, $price)
                    ->setHome($homeModel)
                ;
            }

            foreach ($priceInfo['tomorrow'] ?? [] as $price) {
                $prices[$price['startsAt']] = $this->modelMapper->mapToObject(Price::class, $price)
                    ->setHome($homeModel)
                ;
            }

            $prices[$priceInfo['current']['startsAt']] = $this->modelMapper->mapToObject(Price::class, $priceInfo['current'])
                ->setCurrent(true)
                ->setHome($homeModel)
            ;
        }

        return array_values($prices);
    }

    /**
     * @throws WebException
     */
    private function request(string $accessToken, array $query): Response
    {
        $queryString = JsonUtility::encode(['query' => $this->graphQlQueryMapper->mapToString($query)]);
        $request = new Request('https://api.tibber.com/v1-beta/gql')
            ->setHeader('Authorization', sprintf('Bearer %s', $accessToken))
            ->setHeader('Content-Type', 'application/json')
            ->setHeader('User-Agent', 'Homey/10.0.0 com.tibber/1.8.3')
            ->setBody(new Body()->setContent($queryString, mb_strlen($queryString)))
        ;

        return $this->webService->post($request);
    }
}
