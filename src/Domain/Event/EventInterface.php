<?php

namespace Linkedcode\Slim\Domain\Event;

use Ramsey\Uuid\UuidInterface;

interface EventInterface
{
    public function getEntityClass(): null|string;
    public function getEntityId(): null|int;
    public function getEntityUuid(): null|UuidInterface;
}