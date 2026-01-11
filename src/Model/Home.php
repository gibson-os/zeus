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
 * @method Price[] getPrices()
 * @method Home    setPrices(Price[] $prices)
 * @method Home    addPrices(Price[] $prices)
 */
#[Table]
#[Key(true, ['access_token', 'foreign_id'])]
class Home extends AbstractModel implements JsonSerializable, AutoCompleteModelInterface
{
    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED], autoIncrement: true)]
    private ?int $id = null;

    #[Column(length: 64)]
    private string $accessToken;

    #[Column(length: 64)]
    #[Key(true)]
    private string $foreignId;

    #[Column(length: 64)]
    private string $name;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $residents = 1;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $size = 0;

    /**
     * @var Price[]
     */
    #[Constraint('home_id', Price::class)]
    protected array $prices = [];

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

    public function getForeignId(): string
    {
        return $this->foreignId;
    }

    public function setForeignId(string $foreignId): Home
    {
        $this->foreignId = $foreignId;

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

    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'residents' => $this->getResidents(),
            'size' => $this->getSize(),
        ];
    }

    #[Override]
    public function getAutoCompleteId(): int
    {
        return $this->getId() ?? 0;
    }
}
