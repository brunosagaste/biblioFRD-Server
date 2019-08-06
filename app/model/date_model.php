<?php
namespace App\Model;

class Date {

	public static function isWeekend($date) {
		return (date('N', strtotime($date)) >= 6);
	}

	public static function addDays($date, $days) {
    	$d = getdate(strtotime($date));
    return date('Y-m-d', mktime(0, 0, 0, $d['mon'], $d['mday']+$days, $d['year']));
  }
}