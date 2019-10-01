<?php
namespace NorwichCTU\save_randomization_date ;

use \REDCap;

class save_randomization_date  extends \ExternalModules\AbstractExternalModule {
	public function __construct() {
		parent::__construct();
		// Other code to run when object is instantiated
	}
	
	
	public function redcap_save_record( int $project_id, string $record, string $instrument, int $event_id, int $group_id, string $survey_hash, int $response_id, int $repeat_instance) {
		
		// Define where the randomisation date is recorded (project specific)
		
		$dateField = $this->getProjectSetting("date-field");
		$dateEvent = $this->getProjectSetting("date-event");
		
				// Load existing value in the randomisation date field
		$loadData = REDCap::getData('array', $record, $dateField, $dateEvent);
		$randoDate = $loadData[$record][$dateEvent][$dateField];


		// If it doesn't already contain data
		if($randoDate =="") {
						
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
						$dateEvent => array($dateField  => $row['logged_date'])
						)
					);
		
				// Save the date
				$response = REDCap::saveData('array', $saveData, 'normal');
			}
		}
		
	}

	
}