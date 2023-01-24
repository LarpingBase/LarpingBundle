<?php

// src/Service/LarpingService.php

namespace LarpingBase\LarpingBundle\Service;

use App\Entity\ObjectEntity;
use Doctrine\ORM\EntityManagerInterface;
use \DateTime;

class LarpingService
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    )
    {
        $this->entityManager = $entityManager;
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

        $effect = [];
        $stats =[];

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


    private function addEffectToStats(array $abillities, ObjectEntity $effect, $effects): array{


        // Stackable
        if($effect->getValue('positive')){

        }

        // What if the abbility has not been added yet
        if(!in_array($effect->getValue('abillity'), $abillities)){
            $abillities[$effect->getValue('abillity')->getId()] =
                [
                    "name" => $effect->getValue('abillity')->getValue('name'),
                    "value" => $effect->getValue('abillity')->getValue('base'),
                    "effects" => []
                ];
        }

        // Get current vallue
        $value = $abillities[$effect->getValue('abillity')->getId()]["value"];

        // Positive versus negative modifaction
        if($effect->getValue('positive')){
            $value = $value + $effect->getValue('modifier');
            $effectDescription = "(+ ".$effect->getValue('modifier').") ".$effect->getValue('name');
        }
        else{
            $value = $value - $effect->getValue('modifier');
            $effectDescription = "(- ".$effect->getValue('modifier').") ".$effect->getValue('name');
        }

        // Set the calculated effects
        $abillities[$effect->getValue('abillity')->getId()]["value"] = $value;
        $abillities[$effect->getValue('abillity')->getId()]["effects"] = $effectDescription;

        return $stats;
    }

}
