<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CountryProcessingTime extends Model
{
    //

    protected $table = 'country_processing_times';

    protected $fillable = [
    	'request_type',
    	'country_abbr',
    	'last_updated',
    ];
    
}
