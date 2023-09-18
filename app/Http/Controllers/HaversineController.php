<?php

namespace App\Http\Controllers;

use App\Exports\LocationsExport;
use App\Http\DTO\CSVDataDTO;
use App\Http\DTO\PositionStackDTO;
use App\Services\HaversineService;
use FilippoToso\PositionStack\Facade\PositionStack;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class HaversineController extends Controller
{
    public function distance(): StreamedResponse
    {
        $data = [
            "Sint Janssingel 92, 5211 DA 's-Hertogenbosch, The Netherlands",
            "Deldenerstraat 70, 7551AH Hengelo, The Netherlands",
            "46/1 Office no 1 Ground Floor , Dada House , Inside dada silk mills compound, Udhana Main Rd, near Chhaydo Hospital, Surat, 394210, India",
            "Weena 505, 3013AL Rotterdam, The Netherlands",
            "221B Baker St., London, United Kingdom",
            "1600 Pennsylvania Avenue, Washington, D.C., USA",
            "350 Fifth Avenue, New York City, NY 10118",
            "Saint Martha House, 00120 Citta del Vaticano, Vatican City",
            "5225 Figueroa Mountain Road, Los Olivos, California 93441, USA"
        ];

        $locations = $this->getLocations($data);
        $headquarters = $this->getHeadquarters($locations);
        $csvData = $this->getSortedCSVData($headquarters, $locations);

        $csvData = json_decode(json_encode($csvData), true);

        return (new LocationsExport($csvData))->download("adchieve_okan_ozbek.csv");
    }

    /*
     * Would love to refactor this to another class, perhaps a helper, service or modal
     */
    private function positionStackData(array $rawData): array
    {
        return $rawData['data'][0];
    }

    /*
     * I am unsure if I would keep this in the controller
     */
    private function getLocations(array $data): array
    {
        $temp = [];

        foreach ($data as $address) {
            $temp [] = PositionStackDTO::from($this->positionStackData(
                PositionStack::forward($address)
            ));
        }

        return $temp;
    }

    /*
     * Same for this
     */
    private function getHeadquarters(array $locations): PositionStackDTO
    {
        return $locations[0];
    }

    private function getSortedCSVData(PositionStackDTO $headquarters, array $locations): array
    {
        $temp = [];

        foreach ($locations as $location)
        {
            $distance = HaversineService::calculate($headquarters, $location);

             $temp []= CSVDataDTO::from([
                 'distance' => $distance,
                 'name' => $location->name,
                 'address' => $location->label
             ]);
        }

        sort($temp);

        $sortNumber = 1;

        foreach ($temp as $item) {
            $item->sortNumber = $sortNumber;

            $sortNumber++;
        }

        return $temp;
    }
}
