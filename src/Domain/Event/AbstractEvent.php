<?php

namespace Linkedcode\Slim\Domain\Event;

use Ramsey\Uuid\UuidInterface;

abstract class AbstractEvent implements EventInterface
{
    abstract protected function getEntity();

    public function getEntityClass(): ?string
    {
        return get_class($this->getEntity());
    }

    public function getEntityId(): ?int
    {
        return $this->getEntity()->getId();
    }

    public function getEntityUuid(): null|UuidInterface
    {
        return $this->getEntity()->getUuid();
    }
}