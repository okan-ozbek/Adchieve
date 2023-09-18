<?php

namespace App\Services;

use App\Http\DTO\PositionStackDTO;

final class HaversineService
{
    private const EARTH_RADIUS_KM = 6371;

    /**
     * @param PositionStackDTO $pointA
     * @param PositionStackDTO $pointB
     * @return string
     *
     * We are using the third formula of the haversine calculation to determine the distance
     * @url https://wikimedia.org/api/rest_v1/media/math/render/svg/8390236068c8e84f9fea2729c76a21ec3574a7db
     */
    public static function calculate(PositionStackDTO $pointA, PositionStackDTO $pointB): string
    {
        $radianLatitudeA = self::toRadian($pointA->latitude);
        $radianLatitudeB = self::toRadian($pointB->latitude);
        $radianLongitudeA = self::toRadian($pointA->longitude);
        $radianLongitudeB = self::toRadian($pointB->longitude);

        $multipliedCos = cos($radianLatitudeA) * cos($radianLatitudeB);
        $haversinLatitude = self::haversin($radianLatitudeB - $radianLatitudeA);
        $haversinLongitude = self::haversin($radianLongitudeB - $radianLongitudeA);

        $addedResult = $haversinLatitude + $multipliedCos * $haversinLongitude;
        $sqrtAddedResult = sqrt($addedResult);
        $distance = 2 * self::EARTH_RADIUS_KM * asin($sqrtAddedResult);

        return round($distance, 2) . "KM";
    }

    /**
     * @param $radianAngle
     * @return float
     */
    private static function haversin($radianAngle): float
    {
        return pow(sin($radianAngle / 2), 2);
    }

    /**
     * @param float $degreeAngle
     * @return float
     */
    private static function toRadian(float $degreeAngle): float
    {
        return (M_PI / 180) * $degreeAngle;
    }
}
