<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
		$overviewList = $generalHostpackageOverview->getAllHostsOverview();
		
        return $this->render('default/overview.html.twig', array(
            'overviewList' => $overviewList,
        ));
    }
    
    /**
     * @Route("/overview/host/{id}", name="host_overview")
     */
    public function hostOverviewAction($id)
    {   
		$generalHostpackageOverview = $this->get('general_hostpackage_overview');
		$overviewList = $generalHostpackageOverview->getHostOverview($id);
		
        return $this->render('default/host_overview.html.twig', array(
            'overviewList' => $overviewList,
        ));
    }
}
