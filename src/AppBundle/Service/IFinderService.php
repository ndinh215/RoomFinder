<?php

namespace AppBundle\Service;

interface IFinderService {

    public function findRooms($hotel, $date);
    public function removeOffer($id);

} 