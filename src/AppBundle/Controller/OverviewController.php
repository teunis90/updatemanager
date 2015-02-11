<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Host;
use AppBundle\Entity\Sshkeychain;
use AppBundle\Form\Type\SshkeychainType;
use Debver\Version;

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
		$generalHostpackageOverview = $this->get('general_hostpackage_overview');
		$overviewList = $generalHostpackageOverview->getHostOverview();
		
        return $this->render('default/overview.html.twig', array(
            'overviewList' => $overviewList,
        ));
    }
}
