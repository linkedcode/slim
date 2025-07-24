<?php

namespace Linkedcode\Slim\Responder;

use DateTime;

abstract class AbstractTransformer
{
    public function getTimestamp(int $timestamp, $options = []): array
    {
        $datetime = DateTime::createFromTimestamp($timestamp);

        $dt = [
            'timestamp' => $timestamp,
            'datetime' => $datetime->format("d-m-Y H:i:s"),
            'date' => $datetime->format("d-m-Y"),
            'time' => $datetime->format("H:i:s")
        ];

        return $dt;
    }
}
