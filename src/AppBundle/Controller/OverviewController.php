<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Host;
use AppBundle\Entity\Sshkeychain;
use AppBundle\Form\Type\SshkeychainType;

class OverviewController extends Controller
{
    /**
     * @Route("/", name="index")
     */
    public function indexAction(Request $request)
    {
		return $this->overviewAction($request);
    }
        
    /**
     * @Route("/overview", name="overview")
     */
    public function overviewAction()
    {   
		// Start put in service
		$query = "SELECT h.*, count(h.id) as 'installedpackages', CALCULATE_UPDATES(h.id) as 'availableupdates' FROM `Host` h
					INNER JOIN `Hostpackageversion` hpv ON (h.`id` = hpv.`hostid`)
					INNER JOIN `Run` run ON (hpv.`runid` = run.`id`)
				  WHERE run.id = (SELECT MAX(id) FROM Run) GROUP BY h.id
				  UNION
				  SELECT h.*, '?' as 'installedpackages', '?' as 'availableupdates' FROM `Host` h
					LEFT JOIN `Hostpackageversion` hpv ON (h.`id` = hpv.`hostid`)
					LEFT JOIN `Run` run ON (hpv.`runid` = run.`id`)
				  GROUP BY h.id
				  HAVING count(h.id) = 1";
		
	    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
	    $stmt->execute();
	    $overviewList = $stmt->fetchAll();
	    // End put in service
		
        return $this->render('default/overview.html.twig', array(
            'overviewList' => $overviewList,
        ));
    }
}
