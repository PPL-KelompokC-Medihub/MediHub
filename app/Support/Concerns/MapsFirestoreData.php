<?php

namespace App\Support\Concerns;

/**
 * Trait helper untuk controller yang membaca data Firestore.
 *
 * Firestore mengembalikan dokumen sebagai associative array. Trait ini
 * membungkusnya jadi object stdClass agar lebih nyaman dipakai di Blade
 * (`$doctor->name` lebih bersih daripada `$doctor['name']`).
 */
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
