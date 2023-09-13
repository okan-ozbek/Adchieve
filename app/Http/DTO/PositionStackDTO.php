<?php

namespace App\Http\DTO;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
final class PositionStackDTO extends Data
{
    public float $latitude;
    public float $longitude;
    public ?string $type;
    public ?string $name;
    public ?string $number;
    public ?string $postalCode;
    public ?string $street;
    public ?string $confidence;
    public ?string $region;
    public ?string $regionCode;
    public ?string $county;
    public ?string $locality;
    public ?string $administrativeArea;
    public ?string $neighbourhood;
    public ?string $country;
    public ?string $countryCode;
    public ?string $continent;
    public ?string $label;
}
