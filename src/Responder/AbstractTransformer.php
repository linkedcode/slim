<?php

namespace Linkedcode\Slim\Responder;

use DateTime;

abstract class AbstractTransformer
{
    public function getTimestamp(int $timestamp, $options = []): array
    {
        $dt = [];

        $datetime = new DateTime();
        $datetime->setTimestamp($timestamp);

        $dt['datetime'] = $datetime->format("d-m-Y H:i:s");
        $dt['date'] = $datetime->format("d-m-Y");
        $dt['time'] = $datetime->format("H:i:s");

        return $dt;
    }
}