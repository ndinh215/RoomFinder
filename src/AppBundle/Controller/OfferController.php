<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Offer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;

class OfferController extends Controller
{
    protected  $finderService = null;
    protected $container = null;
    private $hotelName = 'The Reverie Residence';

    public function __construct($container) {
        $this->container = $container;
    }

    /**
     * Add offer action
     */
    public function addAction($date = null, Request $request)
    {
        $result = $this->finderService->findRooms($this->hotelName, $date);

        if($request->isXmlHttpRequest()) {
            $response = new Response(json_encode($result));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }

        return $this->render('finder/list_rooms.html.twig', [
            'status' => $result['status'],
            'date' => $date,
            'rooms' => json_decode(json_encode($result['result'], false)),
            'error' => isset($result['error'])? $result['error'] : null,
        ]);
    }

    /**
     * Remove offer action
     */
    public function removeAction($id, Request $request)
    {
        $result = $this->finderService->removeOffer($id);

        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Set finder service
     */
    public function setFinder($finderService) {
        $this->finderService = $finderService;
    }
}
