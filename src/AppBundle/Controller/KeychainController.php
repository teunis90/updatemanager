<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Sshkeychain;

class KeychainController extends Controller
{
    /**
     * @Route("/", name="index")
     */
    public function indexAction(Request $request)
    {
	    // This function should be replaced to another controller
		return $this->keychainAction($request);
    }
        
    /**
     * @Route("/keychain", name="keychain")
     */
    public function keychainAction()
    {   
		$conn = $this->get('database_connection');
        $keychainList = $conn->fetchAll('SELECT `Sshkeychain`.*, IF(ISNULL(`Host`.`id`), 0, 1) as "hasChildren" 
        								 FROM `Sshkeychain` LEFT JOIN `Host` ON (`Host`.`Sshkeychainid` = `Sshkeychain`.`id`) GROUP BY `Sshkeychain`.`id`');
		
        return $this->render('default/keychain.html.twig', array(
            'keychainList' => $keychainList,
        ));
    }

    /**
     * @Route("/keychain/default/{id}", name="keychainDefault")
     */
    public function keychainDefaultAction($id, Request $request)
    {   
		$em = $this->getDoctrine()->getManager();
		$query = $em->createQuery('UPDATE AppBundle:Sshkeychain s SET s.defaultkey = 0');
		$query->getResult();
		$query = $em->createQuery('UPDATE AppBundle:Sshkeychain s SET s.defaultkey = 1 WHERE s.id = :id')->setParameter('id', $id);
		$query->getResult();
	    
		return $this->keychainAction();

    }
    
    /**
     * @Route("/keychain/delete/{id}", name="keychainDelete")
     */
    public function keychainDeleteAction($id, Request $request)
    {
		$conn = $this->get('database_connection');
        $delete = $conn->prepare('DELETE `Sshkeychain` FROM `Sshkeychain` LEFT JOIN `Host` ON (`Host`.`Sshkeychainid` = `Sshkeychain`.`id`)
        						  WHERE `Sshkeychain`.`id` = :id AND `Sshkeychain`.`defaultkey` = 0 AND ISNULL(`Host`.`id`)');
		$delete->bindValue('id', $id, \PDO::PARAM_INT);
		$delete->execute();
        
		return $this->keychainAction();
    }
    
    /**
     * @Route("/keychain/edit/{id}", name="keychainEdit")
     */
    public function keychainEditAction($id, Request $request)
    {
		$keypair = $this->getDoctrine()
        ->getRepository('AppBundle:Sshkeychain')
        ->find($id);
        
	    if (!$keypair) {
	        throw $this->createNotFoundException('No keypair found for id '.$id);
	    }
	    
        $form = $this->createFormBuilder($keypair)
            ->add('sshprivatekeyfilepath', 'text')
            ->add('sshpublickeyfilepath', 'text')
            ->add('comment', 'text')
            ->getForm();
	    
		$form->handleRequest($request);

		if ($form->isValid()) {
		    $em = $this->getDoctrine()->getManager();
		    $em->persist($keypair);
		    $em->flush();
		    
		    return $this->redirect( $this->generateUrl('keychain') );
		}
        
        return $this->render('default/keychain-edit.html.twig', array(
            'form' => $form->createView(),
            'keypair' => $keypair,
        ));

    }
    
    /**
     * @Route("/keychain/new", name="keychainNew")
     */
    public function keychainNewAction(Request $request)
    {
		$keypair = new Sshkeychain();
	    
        $form = $this->createFormBuilder($keypair)
            ->add('sshprivatekeyfilepath', 'text')
            ->add('comment', 'text')
            ->getForm();
	    
		$form->handleRequest($request);

		if ( $form->isValid() ) {
			$checkDefault = $this->getDoctrine()->getRepository('AppBundle:Sshkeychain')->findBy(
				array('defaultkey' => 1)
			);
			$checkDefault > 0 ? $defaultkey = 0 : $defaultkey = 1;

			$keypair->setSshpublickeyfilepath( $keypair->getSshprivatekeyfilepath() . '.pub' );
			$keypair->setDefaultkey($defaultkey);
			
			if( $retcode = $this->createsshkey( $keypair->getSshprivatekeyfilepath() )) {
			    $em = $this->getDoctrine()->getManager();
			    $em->persist($keypair);
			    $em->flush();	
			} else {
				throw new \Exception('Could not create Sshkey ['.$keypair->getSshprivatekeyfilepath().':'.$retcode.']');
			}
		    
		    return $this->redirect( $this->generateUrl('keychain') );
		}		
        
        return $this->render('default/keychain-new.html.twig', array(
            'form' => $form->createView(),
            'keypair' => $keypair,
        ));

    }
    
    private function createsshkey($filename) {
		$ret = false;
		$appdir = $this->get('kernel')->getRootDir() . '/sshkeys/';
		if(is_writable($appdir)) {
			if(!file_exists($keypath = $appdir . '/' . $filename)) {
				exec("/usr/bin/ssh-keygen -q -t rsa -b 2048 -N '' -f " . $keypath, $o, $ret);
				$ret == 0 ? $ret = true : $ret = false;
			}
		}
		return $ret;
    }
}
