<?php
declare(strict_types=1);

namespace GibsonOS\Module\Zeus\Model;

use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Key;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Model\AutoCompleteModelInterface;
use JsonSerializable;
use Override;

#[Table]
#[Key(true, ['access_key', 'serial_number'])]
class Device extends AbstractModel implements JsonSerializable, AutoCompleteModelInterface
{
    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED], autoIncrement: true)]
    private ?int $id = null;

    #[Column(length: 64)]
    private string $serialNumber;

    #[Column(length: 64)]
    private string $deviceName;

    #[Column(length: 64)]
    private string $accessKey;

    #[Column]
    private bool $online = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): Device
    {
        $this->id = $id;

        return $this;
    }

    public function getSerialNumber(): string
    {
        return $this->serialNumber;
    }

    public function setSerialNumber(string $serialNumber): Device
    {
        $this->serialNumber = $serialNumber;

        return $this;
    }

    public function getDeviceName(): string
    {
        return $this->deviceName;
    }

    public function setDeviceName(string $deviceName): Device
    {
        $this->deviceName = $deviceName;

        return $this;
    }

    public function getAccessKey(): string
    {
        return $this->accessKey;
    }

    public function setAccessKey(string $accessKey): Device
    {
        $this->accessKey = $accessKey;

        return $this;
    }

    public function isOnline(): bool
    {
        return $this->online;
    }

    public function setOnline(bool $online): Device
    {
        $this->online = $online;

        return $this;
    }

    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'serialNumber' => $this->getSerialNumber(),
            'deviceName' => $this->getDeviceName(),
            'online' => $this->isOnline(),
        ];
    }

    #[Override]
    public function getAutoCompleteId(): int
    {
        return $this->getId() ?? 0;
    }
}
