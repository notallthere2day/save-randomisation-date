<?php
namespace NorwichCTU\SaveRandomizationDate ;

class save_randomisation_date  extends \ExternalModules\AbstractExternalModule {
	public function __construct() {
		parent::__construct();
		// Other code to run when object is instantiated
	}
	
	public function redcap_save_record( int $project_id, string $record, string $instrument, int $event_id, int $group_id = NULL, string $survey_hash, int $response_id, int $repeat_instance = 1 ) {
		//Reads the randomisation date for the current record from the log and saves in in a specified field in the project.
		//useful for including the randomisation date in the final dataset, and writing Data Quality Rules involving randomisation date.
		// Define where the randomisation date is recorded (project specific)
		$dateField = getSettingConfig("rand_date");
		$eventName = getSettingConfig("rand_event");

		// Load existing value in the randomisation date field
		$loadData = REDCap::getData('array', $record, $dateField, $eventName);
		$randoDate = $loadData[$record][$eventName][$dateField];

		// If it doesn't already contain data
		if($randoDate === NULL) {

			// Get the randomisation date from the log
			$sql = "SELECT DATE(ts) AS 'logged_date'
			FROM redcap_log_event
			WHERE description = 'randomize record' AND pk = '" . $record . "' AND project_id = " . $project_id;

			$q = db_query($sql);
			$row = db_fetch_assoc($q);

			// Check the returned row isn't empty
				if (!empty($row)) {
		
					// Build an array to save
					$saveData = array(
					$record => array(
					REDCap::getEventIdFromUniqueEvent($eventName) => array($dateField  => $row['logged_date'])
				)
				);
		
			// Save the date
			$response = REDCap::saveData('array', $saveData, 'normal');
			}
		}

	}

	
}