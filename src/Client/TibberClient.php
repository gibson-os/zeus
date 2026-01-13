<?php
declare(strict_types=1);

namespace GibsonOS\Module\Zeus\Client;

use GibsonOS\Core\Dto\Web\Body;
use GibsonOS\Core\Dto\Web\Request;
use GibsonOS\Core\Dto\Web\Response;
use GibsonOS\Core\Exception\WebException;
use GibsonOS\Core\Mapper\GraphQlQueryMapper;
use GibsonOS\Core\Service\WebService;
use GibsonOS\Core\Utility\JsonUtility;
use JsonException;

class TibberClient
{
    public function __construct(
        private readonly WebService $webService,
        private readonly GraphQlQueryMapper $graphQlQueryMapper,
    ) {
    }

    /**
     * @throws JsonException
     * @throws WebException
     *
     * @return array{
     *      data: array{
     *          viewer: array{
     *              homes: array{
     *                  id: string,
     *                  appNickname: string,
     *                  size: int,
     *                  numberOfResidents: int,
     *                  currentSubscription: array{
     *                      priceInfo: array{
     *                          current: array{
     *                              total: float,
     *                              energy: float,
     *                              tax: float,
     *                              startsAt: string
     *                          },
     *                          today: array{
     *                              total: float,
     *                              energy: float,
     *                              tax: float,
     *                              startsAt: string
     *                          },
     *                          tomorrow: array{
     *                              total: float,
     *                              energy: float,
     *                              tax: float,
     *                              startsAt: string
     *                          }
     *                      }
     *                  }
     *              }
     *          }
     *      }
     * }
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

        return JsonUtility::decode($response->getBody()->getContent());
    }

    /**
     * @throws WebException
     * @throws JsonException
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
