<?php
declare(strict_types=1);

namespace GibsonOS\Module\Zeus\Model;

use DateTime;
use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Model\AbstractModel;

#[Table]
class Price extends AbstractModel
{
    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED], autoIncrement: true)]
    private ?int $id = null;

    private float $total;

    private float $energy;

    private float $tax;

    private DateTime $startsAt;

    private bool $current = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): Price
    {
        $this->id = $id;

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
}
