<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use AppBundle\Entity\Offer;
use AppBundle\Entity\Room;

class OfferControllerTest extends WebTestCase
{
    const URL_PATTERN = 'http://127.0.0.1:8000/api/offers/%s';

    private $em = null;

    protected function setUp() {
        self::bootKernel();
        $this->em = self::$kernel->getContainer()->get('doctrine.orm.entity_manager');
    }

    public function testAddOffer()
    {
        $client = static::createClient();

        $date = new \DateTime('now');
        $url = sprintf(self::URL_PATTERN, $date->format('Y-m-d'));

        $crawler = $client->request('POST', $url);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testRemoveOffer()
    {
        // Add mockup offer & rooms to repository
        $date = new \DateTime('now');
        $offer = $this->retrieveOfferFromRepo($date);

        if (!isset($offer)) {
            $storedOffer = $this->createMockupOffer($date);
        }

        $offerId = isset($offer)? $offer->getId() : $storedOffer->getId();

        $client = static::createClient();
        $url = sprintf(self::URL_PATTERN, (string) $offerId);

        $crawler = $client->request('DELETE', $url);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    private function retrieveOfferFromRepo($date)
    {
        $em = $this->em;

        $query = $em->createQuery('SELECT o FROM AppBundle:Offer o WHERE o.date = :date')->setParameter('date', $date);
        $offer = $query->setMaxResults(1)->getOneOrNullResult();

        return $offer;
    }

    private function createMockupOffer($date)
    {
        $storedOffer = null;
        try {
            $this->em->getConnection()->beginTransaction();

            // Create new offer
            $storedOffer = new Offer();
            $storedOffer->setName('Offer');
            $storedOffer->setDate($date);

            $this->em->persist($storedOffer);
            $this->em->flush();

            // Create new rooms
            $storedRoom = new Room();
            $storedRoom->setName('Best room');
            $storedRoom->setOffer($storedOffer);

            $this->em->persist($storedRoom);
            $this->em->flush();

            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollback();
            throw $e;
        }

        return $storedOffer;
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->em->close();
    }
}
