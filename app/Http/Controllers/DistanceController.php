<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DistanceController extends Controller
{
    function calc(Request $request) {
        // Get Post Data
        $post_data = $request->json()->all();
        if (isset($post_data["origin"]) && isset($post_data["destination"]) && isset($post_data["vehicle"])) {
            $orig_lat = $post_data["origin"][0]["latitude"];
            $orig_lng = $post_data["origin"][0]["longitude"];
            $dest_lat = $post_data["destination"][0]["latitude"];
            $dest_lng = $post_data["destination"][0]["longitude"];
            $vhcl_type = $post_data["vehicle"][0]["type"];
            $vhcl_dpl = $post_data["vehicle"][0]["distance_per_litre"];
            $vhcl_ppl = $post_data["vehicle"][0]["price_per_litre"];
            
            switch (strtolower($vhcl_type)) {
                case 'walking':
                    $vhcl_group = 'walking';
                    break;
                case 'bicycle':
                    $vhcl_group = 'bicycling';
                    break;
                case 'train':
                    $vhcl_group = 'transit';
                    break;
                default:
                    $vhcl_group = 'driving';
                    break;
            }

            $distance_data = [
                'orig' => [
                    'lat' => $orig_lat,
                    'lng' => $orig_lng
                ],
                'dest' => [
                    'lat' => $dest_lat,
                    'lng' => $dest_lng
                ],
                'vehicle_group' => $vhcl_group
            ];

            $calc_distance = self::_getDistance($distance_data);

            $fin_distance = $calc_distance->rows[0]->elements[0]->distance->value / 1000;
            $fin_duration = round($calc_distance->rows[0]->elements[0]->duration->value / 60, 2);
            
            if ($vhcl_group == 'driving') {
                $fin_litre = $fin_distance / $vhcl_dpl;
                $fin_cost = $fin_litre * $vhcl_ppl;

                return response()->json([
                    'distance' => $fin_distance,
                    'duration' => $fin_duration,
                    'cost' => $fin_cost
                ]);
            } else {
                return response()->json([
                    'distance' => $fin_distance,
                    'duration' => $fin_duration
                ]);
            }
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred!'
            ], 500);
        }
    }

    private function _getDistance($data) {
        // Get Distance
        $client = new \GuzzleHttp\Client();
        $res = $client->request('GET', 'http://maps.googleapis.com/maps/api/distancematrix/json?origins='. $data["orig"]["lat"] .','. $data["orig"]["lng"] .'&destinations='. $data["dest"]["lat"] .','. $data["dest"]["lng"] .'&mode='. $data["vehicle_group"] .'&sensor=false');
        return json_decode($res->getBody());
    }
}
