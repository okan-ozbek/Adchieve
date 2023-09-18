<?php

namespace App\Http\DTO;

use Spatie\LaravelData\Data;

final class CSVDataDTO extends Data
{
    public ?string $sortNumber;
    public string $distance;
    public string $name;
    public string $address;
}
