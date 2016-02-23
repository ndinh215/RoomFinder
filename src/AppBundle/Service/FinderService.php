<?php

namespace AppBundle\Service;

use AppBundle\Entity\Offer;
use AppBundle\Entity\Room;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class FinderService implements IFinderService
{
    const DATE_PATTERN = 'Y-m-d';
    const SERVICE_DATE_PATTERN = 'd/m/Y';

    private $em = null;

    public function  __construct()
    {

    }

    public function setEntityManager($em)
    {
        $this->em = $em;
    }

    public function findRooms($hotel, $date)
    {
        $fromDate = \DateTime::createFromFormat(self::DATE_PATTERN, $date);
        $fromDate->setTime(0, 0, 0);

        $toDate = clone $fromDate;
        $toDate->modify('1 day');

        // Get rooms in DB firstly
        $returnedRooms = $this->retrieveRoomsFromRepo($date);
        if (count($returnedRooms) > 0) {
            return ['status' => 'OK', 'result' => $this->normalizeData($returnedRooms)];
        }

        // Get rooms from URL
        $uri = sprintf($this->getUriPattern(), $fromDate->format(self::SERVICE_DATE_PATTERN),
            $toDate->format(self::SERVICE_DATE_PATTERN));

        $client = new Client(['base_url' => $this->getBaseUrl()]);
        $request = $client->get($uri);
        $content = $request->getBody();

        $crawler = new Crawler($content->getContents());
        $filter = $crawler->filter('#rooms-and-rates .room-info h3');
        $result = array();

        if (iterator_count($filter) > 1) {
            foreach ($filter as $i => $content) {
                $crawler = new Crawler($content);
                $result[$i] = array(
                    'name' => $crawler->filter('h3')->text()
                );
            }

            $this->storeRooms($result, $fromDate);

            $returnedRooms = $this->retrieveRoomsFromRepo($date);
            return ['status' => 'OK', 'result' => $this->normalizeData($returnedRooms)];;
        }

        $returnedData = ['status' => 'ERROR', 'error' => 'No room found from '.
            $fromDate->format(self::DATE_PATTERN).' to '.$toDate->format(self::DATE_PATTERN)];

        return $returnedData;
    }

    public function removeOffer($id)
    {
        $offer = $this->em->getRepository('AppBundle:Offer')->find($id);

        if (!$offer) {
            return ['status' => 'ERROR', 'error' =>  'No offer found for id '.$id];
        }

        $this->em->remove($offer);
        $this->em->flush();

        return ['status' => 'OK', 'msg' =>  'Removed offer id '.$id];
    }

    private function getUriPattern()
    {
        $pattern = 'http://www.hotels.com/hotel/details.html?' .
        'q-localised-check-in=%s&hotel-id=555246&q-localised-check-out=%s';

        return $pattern;
    }

    private function getBaseUrl()
    {
        $baseUrl = 'http://www.hotels.com';
        return $baseUrl;
    }

    private function storeRooms($rooms, $date)
    {
        if (count($rooms) == 0) {
            return false;
        }

        // Check room name exists or not
        if ($this->checkOfferExists($date)) {
            return false;
        }

        $em = $this->em;
        try {
            $em->getConnection()->beginTransaction();

            // Create new offer
            $storedOffer = new Offer();
            $storedOffer->setName('Offer');
            $storedOffer->setDate($date);

            $em->persist($storedOffer);
            $em->flush();

            // Create new rooms
            foreach ($rooms as $index => $room) {
                $storedRoom = new Room();
                $storedRoom->setName($room['name']);
                $storedRoom->setOffer($storedOffer);

                $em->persist($storedRoom);
                $em->flush();
            }

            $em->getConnection()->commit();
        } catch (\Exception $e) {
            $em->getConnection()->rollback();
            throw $e;
        }

        return true;
    }

    private function checkOfferExists($date)
    {
        $em = $this->em;

        $query = $em->createQuery(
            'SELECT o
            FROM AppBundle:Offer o
            WHERE o.date = :date'
        )->setParameter('date', $date);

        $products = $query->getResult();

        return count($products) > 0? true : false;
    }

    private function retrieveRoomsFromRepo($date)
    {
        $em = $this->em;

        $query = $em->createQuery(
            'SELECT r FROM AppBundle:Room r
             JOIN r.offer o
             WHERE o.date = :date'
        )->setParameter('date', $date);

        $rooms = $query->getResult();

        return $rooms;
    }

    private function normalizeData($returnedRooms)
    {
        if (count($returnedRooms) == 0) {
            return false;
        }

        $result = [];
        foreach ($returnedRooms as $index => $room) {
            $normalizedRoom = [];
            $normalizedRoom['id'] = $room->getId();
            $normalizedRoom['name'] = $room->getName();
            $normalizedRoom['offer_id'] = $room->getOffer()->getId();

            $result[] = $normalizedRoom;
        }

        return $result;
    }

}