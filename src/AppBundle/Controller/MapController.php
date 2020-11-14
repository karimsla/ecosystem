<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Member;
use AppBundle\Entity\Model;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;


/**
 * Member controller.
 *
 *
 * @Route("ecosystem")
 */
class MapController extends Controller
{
    /**
     * Lists all member entities.
     *
     * @Route("/", name="map_index")
     * @Method("GET")
     */
    public function indexAction()
    {

        return $this->render('front/map.html.twig');
    }


    /**
     * Lists all member entities.
     *
     * @Route("/api/members", name="members_index")
     */
    public function membersapiAction()
    {
 $em=$this->getDoctrine()->getManager()->getRepository(Member::class)->findAll();
        $model=new Model($em[0]->getId(),$em[0]->getName(),$em[0]->getInformation(),$em[0]->getImage(),$em[0]->getPosition(),"30","40");

        $response = new Response();
        $response->setContent(json_encode([
            'data' => $model->getPosition(),
        ]));
        $response->headers->set('Content-Type', 'application/json');
return $response;
    }

}
