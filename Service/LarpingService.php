<?php

// src/Service/LarpingService.php

namespace LarpingBase\LarpingBundle\Service;

use App\Entity\ObjectEntity;

class LarpingService
{
    /*
     * Calculates the atribute when an characters is changed
     *
     * @return array
     */
    public function statsHandler(array $data, array $configuration): array
    {

        var_dump($data['object']);
        die;


        // Lets doe some savetie;s
        if(
            isset($data['object'])  // only trigger id we have an object
            || $data['object']->getEntity()->getReference() != 'https://larping.nl/character.schema.json' // Make sure we have a larping character
            || $data['object']->getId() // make sure the character has an id
        ){
            return $data;
        }

        // It;s oke! so elts continue
        $character = $data['object'];
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
        foreach($character->getValue('skills') as $skill){
            foreach($skill->getValue('effects') as $effect){
                $stats = $this->addEffectToStats($stats, $effect, $effects);
                $effects[] = $effect;
            }
        }


        // Conditions
        foreach($character->getValue('skills') as $skill){
            foreach($skill->getValue('effects') as $effect){
                $stats = $this->addEffectToStats($stats, $effect, $effects);
                $effects[] = $effect;
            }
        }


        // Prepare the return
        $data['object'] = $character;
        return ['response' => 'Hello. Your LarpingBundle works'];
    }


    private function addEffectToStats(array $abillities, ObjectEntity $effect, $effects): array{

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
