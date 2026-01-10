<?php
declare(strict_types=1);

namespace GibsonOS\Module\Zeus\Model;

use DateTime;
use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Attribute\Install\Database\Key;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Model\AbstractModel;
use JsonSerializable;
use Override;

/**
 * @method Home  getHome()
 * @method Price setHome(Home $home)
 */
#[Table]
#[Key(true, ['starts_at', 'home_id'])]
class Price extends AbstractModel implements JsonSerializable
{
    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED], autoIncrement: true)]
    private ?int $id = null;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $homeId;

    #[Column(type: 'double')]
    private float $total;

    #[Column(type: 'double')]
    private float $energy;

    #[Column(type: 'double')]
    private float $tax;

    #[Column]
    private DateTime $startsAt;

    #[Column]
    private bool $current = false;

    #[Constraint]
    protected Home $home;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): Price
    {
        $this->id = $id;

        return $this;
    }

    public function getHomeId(): int
    {
        return $this->homeId;
    }

    public function setHomeId(int $homeId): Price
    {
        $this->homeId = $homeId;

        return $this;
    }

    public function getTotal(): float
    {
        return $this->total;
    }

    public function setTotal(float $total): Price
    {
        $this->total = $total;

        return $this;
    }

    public function getEnergy(): float
    {
        return $this->energy;
    }

    public function setEnergy(float $energy): Price
    {
        $this->energy = $energy;

        return $this;
    }

    public function getTax(): float
    {
        return $this->tax;
    }

    public function setTax(float $tax): Price
    {
        $this->tax = $tax;

        return $this;
    }

    public function getStartsAt(): DateTime
    {
        return $this->startsAt;
    }

    public function setStartsAt(DateTime $startsAt): Price
    {
        $this->startsAt = $startsAt;

        return $this;
    }

    public function isCurrent(): bool
    {
        return $this->current;
    }

    public function setCurrent(bool $current): Price
    {
        $this->current = $current;

        return $this;
    }

    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'total' => $this->getTotal(),
            'energy' => $this->getEnergy(),
            'tax' => $this->getTax(),
            'startsAt' => $this->getStartsAt()->format('Y-m-d H:i:s'),
        ];
    }
}
