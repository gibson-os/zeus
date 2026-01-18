<?php
declare(strict_types=1);

namespace GibsonOS\Module\Zeus\Model;

use DateTime;
use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Attribute\Install\Database\Key;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Model\AbstractModel;

/**
 * @method Home        getHome()
 * @method Measurement setHome(Home $home)
 */
#[Table]
#[Key(true, ['from', 'home_id'])]
#[Key(true, ['to', 'home_id'])]
class Measurement extends AbstractModel
{
    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED], autoIncrement: true)]
    private ?int $id = null;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $homeId;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $consumption;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $sum;

    #[Column]
    private DateTime $from;

    #[Column]
    private ?DateTime $to = null;

    #[Constraint]
    protected Home $home;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): Measurement
    {
        $this->id = $id;

        return $this;
    }

    public function getHomeId(): int
    {
        return $this->homeId;
    }

    public function setHomeId(int $homeId): Measurement
    {
        $this->homeId = $homeId;

        return $this;
    }

    public function getConsumption(): int
    {
        return $this->consumption;
    }

    public function setConsumption(int $consumption): Measurement
    {
        $this->consumption = $consumption;

        return $this;
    }

    public function getSum(): int
    {
        return $this->sum;
    }

    public function setSum(int $sum): Measurement
    {
        $this->sum = $sum;

        return $this;
    }

    public function getFrom(): DateTime
    {
        return $this->from;
    }

    public function setFrom(DateTime $from): Measurement
    {
        $this->from = $from;

        return $this;
    }

    public function getTo(): ?DateTime
    {
        return $this->to;
    }

    public function setTo(?DateTime $to): Measurement
    {
        $this->to = $to;

        return $this;
    }
}
