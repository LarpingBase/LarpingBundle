<?php

// src/Service/LarpingService.php

namespace CommonGateway\PetStoreBundle\Service;

class LarpingService
{
    /*
     * Returns a welcoming string
     *
     * @return array
     */
    public function petStoreHandler(array $data, array $configuration): array
    {
        return ['response' => 'Hello. Your LarpingBundle works'];
    }
}
