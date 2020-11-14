<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Member;
use AppBundle\Entity\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Member controller.
 *
 * @Route("member")
 */
class MemberController extends Controller
{
    /**
     * Lists all member entities.
     *
     * @Route("/", name="member_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $members = $em->getRepository('AppBundle:Member')->findAll();

        return $this->render('member/index.html.twig', array(
            'members' => $members,
        ));
    }

    /**
     * Creates a new member entity.
     *
     * @Route("/new", name="member_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request, ValidatorInterface $validator)
    {

        if ($request->isMethod("GET")) {
            return $this->render('member/new.html.twig');
        }


        $member = new Member();
        $member->setName($request->get("name"));

        $uploadedFile = $request->files->get('file-upload');


        $destination = $this->getParameter('kernel.project_dir') . '/web/back/img';


        $uploadedFile->move(
            $destination,
            $uploadedFile->getClientOriginalName());

        $member->setImage($uploadedFile->getClientOriginalName());
        $member->setInformation($request->get("information"));
        $member->setPosition($request->get("select-search"));
        $errors = $validator->validate($member);
        if ($errors->count() > 0) {
            return $this->render('member/new.html.twig', array("errormsg" => "Verfy entred data"));
        }


        $em = $this->getDoctrine()->getManager();
        $em->persist($member);
        $em->flush();

        return $this->redirectToRoute('member_index');


    }

    /**
     * Finds and displays a member entity.
     *
     * @Route("/{id}", name="member_show")
     * @Method("GET")
     */
    public function showAction(Member $member)
    {
        $deleteForm = $this->createDeleteForm($member);

        return $this->render('member/show.html.twig', array(
            'member' => $member,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Creates a form to delete a member entity.
     *
     * @param Member $member The member entity
     *
     * @return Form The form
     */
    private function createDeleteForm(Member $member)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('member_delete', array('id' => $member->getId())))
            ->setMethod('DELETE')
            ->getForm();
    }

    /**
     * Displays a form to edit an existing member entity.
     *
     * @Route("/{id}/edit", name="member_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Member $member)
    {
        $deleteForm = $this->createDeleteForm($member);
        $editForm = $this->createForm('AppBundle\Form\MemberType', $member);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('member_index');
        }

        return $this->render('member/edit.html.twig', array(
            'member' => $member,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a member entity.
     *
     * @Route("/{id}", name="member_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Member $member)
    {
        if ($member != null) {
            $this->getDoctrine()->getManager()->remove($member);
            $this->getDoctrine()->getManager()->flush();
        }

        return $this->redirectToRoute('member_index');
    }

    /**
     * Lists all member entities.
     *
     * @Route("/api/members", name="memberapi_index")
     * @Method("GET")
     */
    public function membersApiAction()
    {
        $em = $this->getDoctrine()->getManager();

        $members = $em->getRepository('AppBundle:Member')->findAll();
        $em = $this->getDoctrine()->getManager();
        $members=$em->getRepository(Member::class)->findAll();

        $data = array('locations' => array());

        foreach ($members as $m) {
            $json = file_get_contents('https://geocoder.ls.hereapi.com/6.2/geocode.json?searchtext=' .urlencode ( $m->getPosition() ) . '&gen=9&apiKey=CxxCHigH6e2itFdUuYEJdiNCKYOFT2wwtIF2QxxIjiw');
            $obj = json_decode($json);
            $lat = $obj->Response->View[0]->Result[0]->Location->DisplayPosition->Latitude;
            $long = $obj->Response->View[0]->Result[0]->Location->DisplayPosition->Longitude;

            $model = new Model($m->getId(), $m->getName(), $m->getInformation(), $m->getImage(), $m->getPosition(), $lat, $long);
            $data['locations'][] = $this->serializeObject($model);

        }

        $response = new Response(json_encode($data), 200);
        $response->headers->set('Content-Type', 'application/json');
        return $response;

    }
    private function serializeObject(Model $model)
    {


        return array(
            'id' => $model->getId(),
            'Name' => $model->getName(),
            'Information' => $model->getInformation(),
            'Image' => $model->getImage(),
            'Position' => $model->getPosition(),
            'Lat' => $model->getLatitude(),
            'Long' => $model->getLongitude(),

        );
    }


}
