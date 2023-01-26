<?php

// src/Service/LarpingService.php

namespace LarpingBase\LarpingBundle\Service;

use App\Entity\ObjectEntity;
use Doctrine\ORM\EntityManagerInterface;
use \DateTime;
use Symfony\Component\Console\Style\SymfonyStyle;

class LarpingService
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    )
    {
        $this->entityManager = $entityManager;
    }


    /**
     * Set symfony style in order to output to the console
     *
     * @param SymfonyStyle $io
     * @return self
     */
    public function setStyle(SymfonyStyle $io):self
    {
        $this->io = $io;

        return $this;
    }
    /*
     * Calculates the atribute when an characters is changed
     *
     * @return array
     */
    public function statsHandler(array $data, array $configuration): array
    {
        // Lets doe some savetie;s
        if(
            isset($data['object'])  // only trigger id we have an object
            || $data['object']->getEntity()->getReference() != 'https://larping.nl/character.schema.json' // Make sure we have a larping character
            || $data['object']->getId() // make sure the character has an id
        ){
            return $data;
        }

        // It;s oke! so lets calculat continue
        $data['object'] = $this->calculateCharacter($data['object']);

        return ['response' => 'Hello. Your LarpingBundle works'];
    }

    /**
     * Calculate the stats for a given chararacter
     *
     * @param ObjectEntity $character
     * @return ObjectEntity
     * @throws \Exception
     */
    public function calculateCharacter(ObjectEntity $character):ObjectEntity{

        // Savety
        if($character->getEntity()->getReference() != 'https://larping.nl/character.schema.json' ){
            return $character;
        }

        $effects = [];
        $stats = [];

        // Skills
        foreach($character->getValue('skills') as $skill){
            foreach($skill->getValue('effects') as $effect){
                $stats = $this->addEffectToStats($stats, $effect, $effects);
                $effects[] = $effect;
            }
        }

        // Events
        $now = New DateTime();
        foreach($character->getValue('events') as $event){
            // Todo: Continu if enddate is empty or greater then now
            if(! $event->getValue('endDate') || $event->getValue('endDate') > $now){
                continue;
            }
            foreach($event->getValue('effects') as $effect){
                $stats = $this->addEffectToStats($stats, $effect, $effects);
                $effects[] = $effect;
            }
        }

        // Conditions
        foreach($character->getValue('conditions') as $condition){
            foreach($condition->getValue('effects') as $effect){
                $stats = $this->addEffectToStats($stats, $effect, $effects);
                $effects[] = $effect;
            }
        }

        $character->setValue('stats', $stats);

        return $character;
    }


    private function addEffectToStats(array $stats, ObjectEntity $effect, $effects): array{


        // Stackable
        if($effect->getValue('positive')){

        }

        $stat = $effect->getValue('stat');

        //Savety
        if(!$stat){
            return $stats;
        }

        // What if the abbility has not been added yet
        if(!in_array($stat->getId()->toString(), $stats)){
            $stats[$stat->getId()->toString()] =
                [
                    "name" => $stat->getValue('name'),
                    "value" => $stat->getValue('base'),
                    "effects" => []
                ];
        }

        // Get current vallue
        $value = $stats[$stat->getId()->toString()]["value"];

        // Positive versus negative modifaction
        if($effect->getValue('modification') == 'positive'){
            $value = $value + $effect->getValue('modifier');
            $effectDescription = "(+ ".$effect->getValue('modifier').") ".$effect->getValue('name');
        }
        else{
            $value = $value - $effect->getValue('modifier');
            $effectDescription = "(- ".$effect->getValue('modifier').") ".$effect->getValue('name');
        }

        // Set the calculated effects
        $stats[$stat->getId()->toString()]["base"] = $stat->getValue('base');
        $stats[$stat->getId()->toString()]["value"] = $value;
        $stats[$stat->getId()->toString()]["effects"][] = $effectDescription;

        return $stats;
    }

}
