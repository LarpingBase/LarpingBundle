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
    public function AtributeHandler(array $data, array $configuration): array
    {

        $attributes = [];

        foreach($character->getValue("skills") as $skill){

        }

        return ['response' => 'Hello. Your LarpingBundle works'];
    }

    private function calculateAbillities(array $abillities, ObjectEntity $effect): array{

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

        return $abillities;
    }

}
