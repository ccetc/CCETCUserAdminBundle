<?php
namespace CCETC\UserAdminBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Sonata\AdminBundle\Datagrid\ORM\ProxyQuery;

class UserAdminController extends Controller
{

  protected function getPageLink()
  {
    $httpHost = $this->container->get('request')->getHttpHost();
    $baseUrl = $this->container->get('request')->getBaseUrl();
    return 'http://' . $httpHost . $baseUrl;
  }

  public function sendAccountApprovedEmail($toAddress)
  {
    $applicationTitle = $this->container->get('adminSettings')->adminTitle;  
      
    $message = \Swift_Message::newInstance()
            ->setSubject($applicationTitle.' - Account Approved')
            ->setFrom($this->container->getParameter('fos_user.registration.confirmation.from_email'))
            ->setTo($toAddress)
            ->setContentType('text/html')
            ->setBody('<html>
               Your '.$applicationTitle.' account has been approved.<br/>
               You can now log in.<br/>
               <a href="' . $this->getPageLink().'">'.$this->getPageLink().'</a></html>')
    ;
    $this->get('mailer')->send($message);
  }

  public function sendAccountPromotedEmail($toAddress)
  {
    $applicationTitle = $this->container->get('adminSettings')->adminTitle;  

    $message = \Swift_Message::newInstance()
            ->setSubject($applicationTitle.' - Promoted to Admin')
            ->setFrom($this->container->getParameter('fos_user.registration.confirmation.from_email'))
            ->setTo($toAddress)
            ->setContentType('text/html')
            ->setBody('<html>
              You have been given administrator access to '.$applicationTitle.'.<br/>
              You can now log in and use the admin tools.<br/>
              <a href="' . $this->getPageLink() . '">'.$this->getPageLink().'</a></html>')
    ;
    $this->get('mailer')->send($message);
  }

  public function batchActionApprove($query)
  {
    $em = $this->getDoctrine()->getEntityManager();

    foreach($query->getQuery()->iterate() as $pos => $object)
    {
      $object[0]->setEnabled('1');

      $this->sendAccountApprovedEmail($object[0]->getEmail());
    }

    $em->flush();
    $em->clear();

    $this->getRequest()->getSession()->setFlash('sonata_flash_success', 'The selected users have been approved');

    return new RedirectResponse($this->admin->generateUrl('list', $this->admin->getFilterParameters()));
  }

  public function batchActionUnapprove($query)
  {
    $em = $this->getDoctrine()->getEntityManager();

    foreach($query->getQuery()->iterate() as $pos => $object)
    {
      $object[0]->setEnabled('0');
    }

    $em->flush();
    $em->clear();

    $this->getRequest()->getSession()->setFlash('sonata_flash_success', 'The selected users have been unapproved');

    return new RedirectResponse($this->admin->generateUrl('list', $this->admin->getFilterParameters()));
  }

  public function batchActionPromote(ProxyQuery $queryProxy)
  {
    $em = $this->getDoctrine()->getEntityManager();

    foreach($queryProxy->getQuery()->iterate() as $pos => $object)
    {
      $object[0]->addRole('ROLE_ADMIN');

      $this->sendAccountPromotedEmail($object[0]->getEmail());
    }

    $em->flush();
    $em->clear();

    $this->getRequest()->getSession()->setFlash('sonata_flash_success', 'The selected users have been promoted');

    return new RedirectResponse($this->admin->generateUrl('list', $this->admin->getFilterParameters()));
  }

  public function batchActionDemote(ProxyQuery $queryProxy)
  {
    $em = $this->getDoctrine()->getEntityManager();

    foreach($queryProxy->getQuery()->iterate() as $pos => $object)
    {
      $object[0]->removeRole('ROLE_ADMIN');
    }

    $em->flush();
    $em->clear();

    $this->getRequest()->getSession()->setFlash('sonata_flash_success', 'The selected users have been demoted');

    return new RedirectResponse($this->admin->generateUrl('list', $this->admin->getFilterParameters()));
  }

  public function approveAction($id)
  {
    $em = $this->getDoctrine()->getEntityManager();

    $userManager = $this->container->get('fos_user.user_manager');
    $user = $userManager->findUserBy(array("id" => $id));
    $user->setEnabled('1');

    $em->flush();
    $em->clear();

    $this->sendAccountApprovedEmail($user->getEmail());

    $this->getRequest()->getSession()->setFlash('sonata_flash_success', $user->getEmail() . ' has been approved');

    return new RedirectResponse($this->admin->generateUrl('list', $this->admin->getFilterParameters()));
  }

  public function unapproveAction($id)
  {
    $em = $this->getDoctrine()->getEntityManager();

    $userManager = $this->container->get('fos_user.user_manager');
    $user = $userManager->findUserBy(array("id" => $id));
    $user->setEnabled('0');

    $em->flush();
    $em->clear();

    $this->getRequest()->getSession()->setFlash('sonata_flash_success', $user->getEmail() . ' has been unapproved');


    return new RedirectResponse($this->admin->generateUrl('list', $this->admin->getFilterParameters()));
  }

  public function promoteAction($id)
  {
    $em = $this->getDoctrine()->getEntityManager();

    $userManager = $this->container->get('fos_user.user_manager');
    $user = $userManager->findUserBy(array("id" => $id));
    $user->addRole('ROLE_ADMIN');

    $em->flush();
    $em->clear();

    $this->sendAccountPromotedEmail($user->getEmail());

    $this->getRequest()->getSession()->setFlash('sonata_flash_success', $user->getEmail() . ' has been promoted');

    return new RedirectResponse($this->admin->generateUrl('list', $this->admin->getFilterParameters()));
  }

  public function demoteAction($id)
  {
    $em = $this->getDoctrine()->getEntityManager();

    $userManager = $this->container->get('fos_user.user_manager');
    $user = $userManager->findUserBy(array("id" => $id));
    $user->removeRole('ROLE_ADMIN');

    $em->flush();
    $em->clear();

    $this->getRequest()->getSession()->setFlash('sonata_flash_success', $user->getEmail() . ' has been demoted');

    return new RedirectResponse($this->admin->generateUrl('list', $this->admin->getFilterParameters()));
  }

}