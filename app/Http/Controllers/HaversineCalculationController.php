<?php

namespace App\Http\Controllers;

use App\Exports\LocationsExport;
use App\Http\DTO\CSVDataDTO;
use App\Http\DTO\PositionStackDTO;
use FilippoToso\PositionStack\Facade\PositionStack;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Mockery\Exception;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class HaversineCalculationController extends Controller
{
    const EARTH_RADIUS_KM = 6371;
    const EARTH_RADIUS_MILES = 0;

    // @TODO remove this later
    public function distance(): StreamedResponse
    {
        $unit = 'KM';

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

        /*
         * sortnumber, distance, name, address
         */

        $locations = $this->getLocations($data);
        $headquarters = $this->getHeadquarters($locations);
        $csvData = $this->getSortedCSVData($headquarters, $locations);

        return (new LocationsExport($csvData))->download();

        //dd($csvData);

        $distance = $this->haversine($pointA, $pointB);

        return view('distance', [
            'distance' => number_format($distance, 2, ',', '.'),
            'unit' => $unit,
            'pointA' => $pointA,
            'pointB' => $pointB
        ]);
    }

    /**
     * @param PositionStackDTO $pointA
     * @param PositionStackDTO $pointB
     * @return float
     *
     * We are using the third formula of the haversine calculation to determine the distance
     * @url https://wikimedia.org/api/rest_v1/media/math/render/svg/8390236068c8e84f9fea2729c76a21ec3574a7db
     */
    private function haversine(PositionStackDTO $pointA, PositionStackDTO $pointB): float
    {
        $radianLatitudeA = $this->toRadian($pointA->latitude);
        $radianLatitudeB = $this->toRadian($pointB->latitude);
        $radianLongitudeA = $this->toRadian($pointA->longitude);
        $radianLongitudeB = $this->toRadian($pointB->longitude);

        $earthRadius = 6371;

        $multipliedCos = cos($radianLatitudeA) * cos($radianLatitudeB);
        $haversinLatitude = $this->haversin($radianLatitudeB - $radianLatitudeA);
        $haversinLongitude = $this->haversin($radianLongitudeB - $radianLongitudeA);

        $addedResult = $haversinLatitude + $multipliedCos * $haversinLongitude;
        $sqrtAddedResult = sqrt($addedResult);
        $distance = 2 * self::EARTH_RADIUS_KM * asin($sqrtAddedResult);

        return $distance;
    }

    /**
     * @param $radianAngle
     * @return float
     */
    private function haversin($radianAngle): float
    {
        return pow(sin($radianAngle / 2), 2);
    }

    /**
     * @param float $degreeAngle
     * @return float
     */
    private function toRadian(float $degreeAngle): float
    {
        return (M_PI / 180) * $degreeAngle;
    }

    // @TODO refactor this to another place
    private function positionStackData(array $rawData): array
    {
        return $rawData['data'][0];
    }

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

    private function getHeadquarters(array $locations): PositionStackDTO
    {
        return $locations[0];
    }

    private function getSortedCSVData(PositionStackDTO $headquarters, array $locations): array
    {
        $temp = [];

        foreach ($locations as $location)
        {
            $temp []= CSVDataDTO::from([$this->haversine($headquarters, $location), $location->name, $location->label]);
        }

        sort($temp);

        return $temp;
    }
}
