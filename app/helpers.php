<?php

use Carbon\Carbon;

function generateFileName($image){
    $carbon = Carbon::now();
    return $carbon->year .'-'.$carbon->month.'-'.$carbon->day.'-'.$carbon->micro.'-'.$image;
}
