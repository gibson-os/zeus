<?php
declare(strict_types=1);

namespace GibsonOS\Module\Zeus\Model;

use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Attribute\Install\Database\Key;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Model\AutoCompleteModelInterface;
use JsonSerializable;
use Override;

/**
 * @method Price[]       getPrices()
 * @method Home          setPrices(Price[] $prices)
 * @method Home          addPrices(Price[] $prices)
 * @method Measurement[] getMeasurements()
 * @method Home          setMeasurements(Measurement[] $prices)
 * @method Home          addMeasurements(Measurement[] $prices)
 */
#[Table]
#[Key(true, ['access_token', 'metering_point_ean'])]
class Home extends AbstractModel implements JsonSerializable, AutoCompleteModelInterface
{
    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED], autoIncrement: true)]
    private ?int $id = null;

    #[Column(length: 64)]
    private string $accessToken;

    #[Column(length: 64)]
    #[Key(true)]
    private ?string $meteringPointEan = null;

    #[Column(length: 64)]
    #[Key(true)]
    private ?string $meteringPointId = null;

    #[Column(length: 64)]
    private string $name;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $residents = 1;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $size = 0;

    #[Column(length: 64)]
    private ?string $street = null;

    #[Column(length: 6)]
    private ?string $streetNumber = null;

    #[Column(length: 16)]
    private ?string $postalCode = null;

    #[Column(length: 64)]
    private ?string $city = null;

    #[Column(length: 2)]
    private ?string $country = null;

    /**
     * @var Price[]
     */
    #[Constraint('home', Price::class)]
    protected array $prices = [];

    /**
     * @var Measurement[]
     */
    #[Constraint('home', Measurement::class)]
    protected array $measurements = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): Home
    {
        $this->id = $id;

        return $this;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function setAccessToken(string $accessToken): Home
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Home
    {
        $this->name = $name;

        return $this;
    }

    public function getResidents(): int
    {
        return $this->residents;
    }

    public function setResidents(int $residents): Home
    {
        $this->residents = $residents;

        return $this;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function setSize(int $size): Home
    {
        $this->size = $size;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): Home
    {
        $this->street = $street;

        return $this;
    }

    public function getStreetNumber(): ?string
    {
        return $this->streetNumber;
    }

    public function setStreetNumber(?string $streetNumber): Home
    {
        $this->streetNumber = $streetNumber;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): Home
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): Home
    {
        $this->city = $city;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): Home
    {
        $this->country = $country;

        return $this;
    }

    public function getMeteringPointEan(): ?string
    {
        return $this->meteringPointEan;
    }

    public function setMeteringPointEan(?string $meteringPointEan): Home
    {
        $this->meteringPointEan = $meteringPointEan;

        return $this;
    }

    public function getMeteringPointId(): ?string
    {
        return $this->meteringPointId;
    }

    public function setMeteringPointId(?string $meteringPointId): Home
    {
        $this->meteringPointId = $meteringPointId;

        return $this;
    }

    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'residents' => $this->getResidents(),
            'size' => $this->getSize(),
            'street' => $this->getStreet(),
            'streetNumber' => $this->getStreetNumber(),
            'postalCode' => $this->getPostalCode(),
            'city' => $this->getCity(),
            'country' => $this->getCountry(),
            'meteringPointEan' => $this->getMeteringPointEan(),
        ];
    }

    #[Override]
    public function getAutoCompleteId(): int
    {
        return $this->getId() ?? 0;
    }
}
