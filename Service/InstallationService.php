<?php

// src/Service/LarpingService.php

namespace LarpingBase\LarpingBundle\Service;

use App\Entity\DashboardCard;
use Doctrine\ORM\EntityManagerInterface;

class InstallationService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function install(){
        $this->checkDataConsistency();
    }

    public function update(){
        $this->checkDataConsistency();
    }

    public function uninstall(){
        // Do some cleanup
    }

    public function checkDataConsistency(){

        // Lets create some genneric dashboard cards
        $objectsThatShouldHaveCards = ['https://larping.nl/character.schema.json','https://larping.nl/skill.schema.json'];

        foreach($objectsThatShouldHaveCards as $object){
            echo $object;
        }

        // Let create some endpoints
        $objectsThatShouldHaveEndpoints = ['https://larping.nl/character.schema.json','https://larping.nl/skill.schema.json','https://larping.nl/story.schema.json'];

        foreach($objectsThatShouldHaveEndpoints as $object){
            echo $object;
        }

        // Lets see if there is a generic search endpoint


    }
}
