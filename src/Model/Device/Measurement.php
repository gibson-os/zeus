<?php
declare(strict_types=1);

namespace GibsonOS\Module\Zeus\Model\Device;

use DateTime;
use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Attribute\Install\Database\Key;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Module\Zeus\Model\Device;
use JsonSerializable;
use Override;

/**
 * @method Device      getDevice()
 * @method Measurement setDevice(Device $device)
 */
#[Table]
#[Key(true, ['from', 'device_id'])]
#[Key(true, ['to', 'device_id'])]
class Measurement extends AbstractModel implements JsonSerializable
{
    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED], autoIncrement: true)]
    private ?int $id = null;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $deviceId;

    #[Column]
    private DateTime $from;

    #[Column]
    private DateTime $to;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $deviceConsumption;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $gridConsumption;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $gridFeedIn;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $batteryConsumption;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $batteryFeedIn;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $pvGeneration;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $pvToBattery;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $pvToDevice;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $pvToGrid;

    #[Constraint]
    protected Device $device;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): Measurement
    {
        $this->id = $id;

        return $this;
    }

    public function getDeviceId(): int
    {
        return $this->deviceId;
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

    public function getTo(): DateTime
    {
        return $this->to;
    }

    public function setTo(DateTime $to): Measurement
    {
        $this->to = $to;

        return $this;
    }

    public function setDeviceId(int $deviceId): Measurement
    {
        $this->deviceId = $deviceId;

        return $this;
    }

    public function getDeviceConsumption(): int
    {
        return $this->deviceConsumption;
    }

    public function setDeviceConsumption(int $deviceConsumption): Measurement
    {
        $this->deviceConsumption = $deviceConsumption;

        return $this;
    }

    public function getGridConsumption(): int
    {
        return $this->gridConsumption;
    }

    public function setGridConsumption(int $gridConsumption): Measurement
    {
        $this->gridConsumption = $gridConsumption;

        return $this;
    }

    public function getGridFeedIn(): int
    {
        return $this->gridFeedIn;
    }

    public function setGridFeedIn(int $gridFeedIn): Measurement
    {
        $this->gridFeedIn = $gridFeedIn;

        return $this;
    }

    public function getBatteryConsumption(): int
    {
        return $this->batteryConsumption;
    }

    public function setBatteryConsumption(int $batteryConsumption): Measurement
    {
        $this->batteryConsumption = $batteryConsumption;

        return $this;
    }

    public function getBatteryFeedIn(): int
    {
        return $this->batteryFeedIn;
    }

    public function setBatteryFeedIn(int $batteryFeedIn): Measurement
    {
        $this->batteryFeedIn = $batteryFeedIn;

        return $this;
    }

    public function getPvGeneration(): int
    {
        return $this->pvGeneration;
    }

    public function setPvGeneration(int $pvGeneration): Measurement
    {
        $this->pvGeneration = $pvGeneration;

        return $this;
    }

    public function getPvToBattery(): int
    {
        return $this->pvToBattery;
    }

    public function setPvToBattery(int $pvToBattery): Measurement
    {
        $this->pvToBattery = $pvToBattery;

        return $this;
    }

    public function getPvToDevice(): int
    {
        return $this->pvToDevice;
    }

    public function setPvToDevice(int $pvToDevice): Measurement
    {
        $this->pvToDevice = $pvToDevice;

        return $this;
    }

    public function getPvToGrid(): int
    {
        return $this->pvToGrid;
    }

    public function setPvToGrid(int $pvToGrid): Measurement
    {
        $this->pvToGrid = $pvToGrid;

        return $this;
    }

    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'deviceId' => $this->getDeviceId(),
            'deviceConsumption' => $this->getDeviceConsumption(),
            'gridConsumption' => $this->getGridConsumption(),
            'gridFeedIn' => $this->getGridFeedIn(),
            'batteryConsumption' => $this->getBatteryConsumption(),
            'batteryFeedIn' => $this->getBatteryFeedIn(),
            'pvGeneration' => $this->getPvGeneration(),
            'pvToBattery' => $this->getPvToBattery(),
            'pvToGrid' => $this->getPvToGrid(),
            'from' => $this->getFrom()->format('Y-m-d H:i:s'),
            'to' => $this->getTo()->format('Y-m-d H:i:s'),
        ];
    }
}
