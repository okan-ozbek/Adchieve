<?php

namespace App\Exports;

use Vitorccs\LaravelCsv\Concerns\Exportable;
use Vitorccs\LaravelCsv\Concerns\FromArray;
use Vitorccs\LaravelCsv\Concerns\WithHeadings;

class LocationsExport implements FromArray, WithHeadings
{
    use Exportable;

    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Sortnumber',
            'Distance',
            'Name',
            'Address'
        ];
    }

}
