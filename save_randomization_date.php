<?php
namespace NorwichCTU\save_randomization_date ;

use \REDCap;

class save_randomization_date  extends \ExternalModules\AbstractExternalModule {
	public function __construct() {
		parent::__construct();
		// Other code to run when object is instantiated
	}
	
	
	public function redcap_save_record($project_id, $record, $instrument, $event_id, $group_id, $survey_hash, $response_id, $repeat_instance) {
		
		// Define where the randomisation date is recorded (project specific)
		
		$dateField = $this->getProjectSetting("date-field");
		$dateEvent = $this->getProjectSetting("date-event");
		$dateFormat = $this->getProjectSetting("date-format");
		//set the format of the date/time
		$dateFormats=array();
		$dateFormats[0]="%Y-%m-%d";
		$dateFormats[1]="%Y-%m-%d %H:%i";
		$dateFormats[2]="%Y-%m-%d %T";
		
		$dateFormatString=$dateFormats[$dateFormat];
				// Load existing value in the randomisation date field
		$loadData = REDCap::getData('array', $record, $dateField, $dateEvent);
		$randoDate = $loadData[$record][$dateEvent][$dateField];


		// If it doesn't already contain data
		if($randoDate =="") {
						
			// Get the randomisation date from the log
			$sql = "SELECT DATE_FORMAT(ts, '$dateFormatString') AS 'logged_date'
			FROM redcap_log_event
			WHERE description = 'randomize record' AND pk = '" . db_escape($record) . "' AND project_id = " . $project_id;

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
