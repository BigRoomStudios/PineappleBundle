<?php

namespace BRS\PineappleBundle\Twig;

class GeoExtension extends \Twig_Extension
{
	
	/**
	 * Returns a list of functions to add to the existing list.
	 * 
	 * @return array An array of functions
	 */
	public function getFunctions() {
		
		return array(
			'geoDistance' => new \Twig_Function_Method($this, 'geoDistance'),
		);
		
	}
	
	/**
	 * Simple function to calculate the distance between two lat/lng pairs.
	 * 
	 * Code adapted from: http://stackoverflow.com/questions/10053358/measuring-the-distance-between-two-coordinates-in-php
	 * 
	 * @return void
	 */
	public function geoDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000) {
		
		// convert from degrees to radians
		$latFrom = deg2rad($latitudeFrom);
		$lonFrom = deg2rad($longitudeFrom);
		$latTo = deg2rad($latitudeTo);
		$lonTo = deg2rad($longitudeTo);
		
		$lonDelta = $lonTo - $lonFrom;
		$a = pow(cos($latTo) * sin($lonDelta), 2) +
			pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
		$b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);
		
		$angle = atan2(sqrt($a), $b);
		$distance = round($angle * $earthRadius, 2);
		
		print($distance);
		
	}
	
	/**
	 * 
	 */
	public function getName() {
		return 'geo_extension';
	}
	
}