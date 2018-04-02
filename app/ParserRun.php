<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ParserRun extends Model
{
    //
    protected $table = 'parser_runs';

    // Whether to re-update all existing entries
    // or just check if it's been done in a previous run.
    public $forceRefresh = false;

    public static $requestTypes = [
		'visitor',
		'study',
		'work',
		'supervisa',
		'refugees_gov',
		'refugees_private',
		'child_dependent',
		'child_adopted',
	];

	public function run() {

		$totalUpdatedRows = 0;

		// Make sure that an entry is created at the start
		// in case there's an unexpected error or timeout when retrieving the JS file:
		$this->save();

		// Try to retrieve the file (for now, use the local version)
		$source = file_get_contents(base_path() . "/../ircc-processing-times-data/raw/pt-messages.js");

		// If it works, set file_retrieve_success to 1
		$this->file_retrieve_success = 1;
		$this->save();

		// Parse the contents and update each section
		$output = [];
		$parseSuccess = 0;
		foreach(self::$requestTypes as $requestType) {
			$lastUpdatedVariable = $requestType . '_last_updated';
			$isNewVariable = $requestType . '_is_new';

			$requestData = self::extractJsData($source, $requestType);
			if($requestData && isset($requestData['lastupdated'])) {
				// At least one of the 8 sections has worked:
				$parseSuccess = 1;

				// Lookup the most recent previous time for this type, if it exists
				$mostRecentRun = self::whereNotNull($lastUpdatedVariable)
					->orderBy($lastUpdatedVariable, 'desc')
					->first();
				if($mostRecentRun) {
					if($requestData['lastupdated'] > $mostRecentRun->$lastUpdatedVariable) {
						$this->$isNewVariable = 1;
					}
				}
				else {
					// This is the first-ever run:
					$this->$isNewVariable = 1;
				}

				$this->$lastUpdatedVariable = $requestData['lastupdated'];
				$this->save();

				if($this->$isNewVariable || $this->forceRefresh) {

					// Update all of the country processing time entries
					if(isset($requestData['countries']) && is_array($requestData['countries'])) {
						foreach($requestData['countries'] as $countryAbbreviation => $weeks) {
							// Find an existing row if it exists
							// with the same "last updated" value
							$row = CountryProcessingTime::firstOrNew([
								'request_type' => $requestType,
								'country_abbr' => $countryAbbreviation,
								'last_updated' => $requestData['lastupdated']
								]);

							// In very rare cases, the number of weeks might change without having changed the last_updated value in the messages JS file.
							// In that case, update with the values from the latest version.
							if($weeks) {
								$row->weeks = $weeks;
							}
							else {
								$row->weeks = null;
							}
							
							$row->parser_run_id = $this->id;

							// Save to the DB
							if($row->save()) {
								$totalUpdatedRows++;
							}
						}
					}

				}
				

			}
		}

		if($parseSuccess) {
			$this->file_parse_success = 1;
			$this->save();
		}

		// echo "Total rows updated: " . $totalUpdatedRows . "\n";
		return $totalUpdatedRows;

	}


	// Get the contents of the pt-messages.js file
	// and use regular expressions to pull out each JS object.
	public static function parseMessagesFile() {

		$output = [];

		$source = file_get_contents(base_path() . "/../ircc-processing-times-data/raw/pt-messages.js");

		foreach($requestTypes as $requestType) {
			$output[$requestType] = self::extractJsData($source, $requestType);
		}

		dd($output);
		

	}

	public static function extractJsData($source, $requestType) {

		$output = [];

		// Finds a string starting with "var visitor = " and ending with a ;
		$regexPattern = '/var ' . $requestType . ' =([^;]*)/';

		$section = trim(self::singleRegexSearch($source, $regexPattern));

		// This is a highly-kludgy way of adding key-name parentheses to each entry in the array
		// from {AF:"31",AL:"11",DZ:"21"
		// to {"AF":"31","AL":"11","DZ":"21",
		// so that it can be json_decode'd
		$section = str_replace([':', ',', '{'], ['":', ',"', '{"'], $section);

		// Converts the JSON-esque string to a PHP array
		$section = json_decode($section, 1);

		if(is_array($section)) {

			// Store the last updated string separately from the country values
			// And, switch from 2018/03/25 syntax to 2018-03-25
			// to avoid confusing the date parser later.
			$output['lastupdated'] = str_replace('/', '-', $section['lastupdated']);
			unset($section['lastupdated']);

			$output['countries'] = $section;

			return $output;
		}

		else {
			echo "Error parsing " . $requestType . " at " . date('Y-m-d H:i:s');
			return [];
		}

		

	}

	public static function singleRegexSearch($text, $regexPattern) {

		$output = '';
		$matches = [];
		$pattern = $regexPattern;

		preg_match($pattern, $text, $matches);
		if ($matches) {
		    $output = $matches[1];
		}

		return $output;


	}

}
