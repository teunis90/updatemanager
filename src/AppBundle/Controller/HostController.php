<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Host;
use AppBundle\Entity\Sshkeychain;
use AppBundle\Form\Type\SshkeychainType;

class HostController extends Controller
{       
    /**
     * @Route("/host", name="host")
     */
    public function hostAction()
    {   
		$hostList = $this->getDoctrine()->getRepository('AppBundle:Host')->findAll();
		
        return $this->render('default/host.html.twig', array(
            'hostList' => $hostList,
        ));
    }
    
    /**
     * @Route("/host/edit/{id}", name="hostEdit")
     */
    public function hostEditAction($id, Request $request)
	{
		$host = $this->getDoctrine()
        ->getRepository('AppBundle:Host')
        ->find($id);
        
	    if (!$host) {
	        throw $this->createNotFoundException('No keypair found for id '.$id);
	    }
	    
        $form = $this->createFormBuilder($host)
            ->add('hostname', 'text')
            ->add('ip', 'text')
            ->add('useip', 'checkbox', array('required'  => false))
            ->add('sshuser', 'text')
            ->add('sshsudo', 'checkbox', array('required'  => false))
			->add('sshkeychainid', 'entity', array(
				  'class' => 'AppBundle:Sshkeychain',
				  'property' => 'sshpublickeyfilepath',
				  ))
			->add('password', 'password', array('mapped' => false))
            ->getForm();
	    
		$form->handleRequest($request);

		if ($form->isValid()) {
			$hostname = $host->getUseip() ? $host->getIp() : $host->getHostname();
			$sshstatus = false;
			try {
				$host->uploadPublickey( $form->get('password')->getData() );
				$sshstatus = $host->testKeyLogin();					
			} catch(\Exception $e) {
				$request->getSession()->getFlashBag()->set( 'error', $e->getMessage() );
			}
			if(! $sshstatus) {
				if( !$request->getSession()->getFlashBag()->peek('error') ) {
					$request->getSession()->getFlashBag()->add('error', 'SSH connection failed!');
				}
			} else {
				$request->getSession()->getFlashBag()->add('success', 'SSH key connection established succesfully, click <a class="alert-link" href="'.$this->generateUrl('host').'">here</a> to return to host overview.');
			
			    $em = $this->getDoctrine()->getManager();
			    $em->persist($host);
			    $em->flush();
			}
		}
        
        return $this->render('default/host-edit.html.twig', array(
            'form' => $form->createView(),
            'host' => $host,
        ));
    }
    
    /**
     * @Route("/host/new", name="hostNew")
     */
    public function hostNewAction(Request $request)
	{
		$host = new Host();
	    
        $form = $this->createFormBuilder($host)
            ->add('hostname', 'text')
            ->add('ip', 'text')
            ->add('useip', 'checkbox', array('required'  => false))
            ->add('sshuser', 'text', array('data'  => 'root'))
            ->add('sshsudo', 'checkbox', array('required'  => false))
			->add('sshkeychainid', 'entity', array(
				  'class' => 'AppBundle:Sshkeychain',
				  'property' => 'sshpublickeyfilepath',
				  ))
			->add('password', 'password', array('mapped' => false))
            ->getForm();
	    
		$form->handleRequest($request);

		if ($form->isValid()) {
			$hostname = $host->getUseip() ? $host->getIp() : $host->getHostname();
			$sshstatus = false;
			try {
				$host->uploadPublickey( $form->get('password')->getData() );
				$sshstatus = $host->testKeyLogin();
			} catch(Exception $e) {
				$request->getSession()->getFlashBag()->set( 'error', $e->getMessage() );
			}
			if(! $sshstatus) {
				if( !$request->getSession()->getFlashBag()->peek('error') ) {
					$request->getSession()->getFlashBag()->add('error', 'SSH connection failed!');
				}
			} else {
				$host->setSshstatus(1);
				$request->getSession()->getFlashBag()->add('success', 'SSH key connection established succesfully, click <a class="alert-link" href="'.$this->generateUrl('host').'">here</a> to return to host overview.');
			
			    $em = $this->getDoctrine()->getManager();
			    $em->persist($host);
			    $em->flush();
			}
		}
        
        return $this->render('default/host-new.html.twig', array(
            'form' => $form->createView(),
            'host' => $host,
        ));
    }
    
    /**
     * @Route("/host/delete/{id}", name="hostDelete")
     */
    public function hostDeleteAction($id, Request $request)
    {   
		$hostList = $this->getDoctrine()->getRepository('AppBundle:Host')->findAll();
		
        return $this->render('default/host.html.twig', array(
            'hostList' => $hostList,
        ));
    }
}
