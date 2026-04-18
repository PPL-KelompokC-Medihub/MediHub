<?php

namespace App\Support;

trait MapsFirestoreData
{
    /**
     * @param array<string, mixed> $record
     */
    protected function toObject(array $record): object
    {
        return (object) $record;
    }

    /**
     * @param array<int, array<string, mixed>> $records
     * @return array<int, object>
     */
    protected function toObjects(array $records): array
    {
        return array_map($this->toObject(...), $records);
    }
}
