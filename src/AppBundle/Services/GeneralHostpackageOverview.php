<?php

namespace AppBundle\Services;

use Debver\Version;

class GeneralHostpackageOverview {
	
	private $doctrine = null;
	
	function __construct($doctrine) {
		$this->setDoctrine($doctrine);
	}
	
	function getAllHostsOverview() {
		$query = "SELECT h.*, count(h.id) as 'installedpackages' FROM `Host` h
					INNER JOIN `Hostpackageversion` hpv ON (h.`id` = hpv.`hostid`)
					INNER JOIN `Run` run ON (hpv.`runid` = run.`id`)
					WHERE run.id = (SELECT MAX(id) FROM Run) GROUP BY h.id
					UNION
					SELECT h.*, '?' as 'installedpackages' FROM `Host` h
					LEFT JOIN `Hostpackageversion` hpv ON (h.`id` = hpv.`hostid`)
					LEFT JOIN `Run` run ON (hpv.`runid` = run.`id`)
					GROUP BY h.id
					HAVING count(h.id) = 1";
					
		$stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);

		$stmt->execute();
		$overview = $stmt->fetchAll();
		
		foreach($overview as $key => $value) {
			$overview[$key]['availableupdates'] = count($this->checkUpdatesByHostId($overview[$key]['id']));
		}
		
		return $overview;
	}
	
	function getHostOverview($id) {
		$query = "SELECT pv.id, p.name, pv.`version` FROM `Host` h
					INNER JOIN `Hostpackageversion` hpv ON (h.`id` = hpv.`hostid`)
					INNER JOIN `Packageversion` pv ON (hpv.`packageversionid` = pv.`id`)
					INNER JOIN `Package` p ON (pv.`packageid` = p.`id`)
					WHERE h.`id` = ? AND hpv.runid = (SELECT MAX(id) FROM Run)";
					
		$stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);

		$parameters = array($id);
		$stmt->execute($parameters);
		$overview = $stmt->fetchAll();
		
		$updates = $this->checkUpdatesByHostId($id);
		$package_with_updates = array();
		
		foreach($overview as $key => $value) {
			$overview[$key]['availableupdate'] = '-';
			foreach($updates as $update) {
				if($overview[$key]['name'] == $update['name']) {
					$overview[$key]['availableupdate'] = $update['updateversion'];
					$package_with_updates[] = $overview[$key];
					unset($overview[$key]);
					break;
				}
			}
		}
		$overview = array_values($overview);
		$overview = array_merge($package_with_updates, $overview);
		
		return $overview;
	}

	// Determine updates per host
	// TODO: What happens if two repositories have an update, is it double counted?
	function checkUpdatesByHostId($id) {
		$query = 'SELECT pv.id, p.name, pv.`version`, pvu.`version` as "updateversion" FROM `Host` h
					INNER JOIN `Hostpackageversion` hpv ON (h.`id` = hpv.`hostid`)
					INNER JOIN `Packageversion` pv ON (hpv.`packageversionid` = pv.`id`)
					INNER JOIN `Package` p ON (pv.`packageid` = p.`id`)
					INNER JOIN `Packageversion` pvu ON (pv.`packageid` = pvu.`packageid` AND pvu.`repositoryid` != 0)
					INNER JOIN `Repository` ru ON (pvu.`repositoryid` = ru.`id`)
					INNER JOIN `Hostrepositories` hr ON (h.`id` = hr.`host_id`)
					INNER JOIN `Repository` r ON (hr.`repository_id` = r.`id`)
					WHERE ru.id = r.id AND h.`id` = ? AND hpv.runid = (SELECT MAX(id) FROM Run)';

		$stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
		
		$parameters = array($id);
		$stmt->execute($parameters);
		$updateList = $stmt->fetchAll();
	    
	    $updates = array();
        foreach($updateList as $update) {
	        // TODO: Put a smart proxy class between which choses based on repository package type
	        if(Version::Compare($update['updateversion'], $update['version']) === 1) {
		        $add = true;
		        foreach($updates as $updatekey => $updatevalue) {
			        if($updatevalue['name'] == $update['name']) {
						// Add logic here to add the newest update to the list.				        
				        $add = false;
/*
				        echo "list\n";
				        print_r($updatevalue);
				        echo "update\n";
				        print_r($update);
				        throw \exception('Double update detected, finish handler with this example'); 
*/
				    }
		        }
	        	if($add) {
			        $updates[] = $update;       
		        } else {
		        }		        
	        }
		}
/*
		print_r($updates);
		
		echo "\nnext\n";
*/
		
		return $updates;
	}

	public function getDoctrine() {
		return $this->doctrine;
	}

	public function setDoctrine($doctrine) {
		$this->doctrine = $doctrine;
	}
}

?>