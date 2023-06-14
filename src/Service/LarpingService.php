<?php
/**
 * The core larping service
 *
 * @author  Conduction.nl <info@conduction.nl>
 * @license EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 */

namespace LarpingBase\LarpingBundle\Service;

use App\Entity\ObjectEntity;
use Doctrine\ORM\EntityManagerInterface;
use DateTime;
use Symfony\Component\Console\Style\SymfonyStyle;
use CommonGateway\CoreBundle\Service\CacheService;
use Psr\Log\LoggerInterface;

class LarpingService
{

    /**
     * Declaring the entity manager interface
     *
     * @var EntityManagerInterface The entity manager interface
     */
    private EntityManagerInterface $entityManager;

    /**
     * The loger interface
     *
     * @var LoggerInterface
     */
    private LoggerInterface $logger;


    /**
     * The default construt for this clas
     *
     * @param EntityManagerInterface $entityManager The entity manager
     * @param CacheService           $cacheService  The cache service
     * @param LoggerInterface        $pluginLogger  The Logger Interface
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        CacheService $cacheService,
        LoggerInterface $pluginLogger
    ) {
        $this->entityManager = $entityManager;
        $this->cacheService  = $cacheService;
        $this->logger        = $pluginLogger;

    }//end __construct()


    /**
     * Calculates the atribute when an characters is changed
     *
     * @param array $data The data at the time of activaation of the action
     *
     * @return array
     */
    public function statsHandler(array $data): array
    {
        // Lets doe some savety checks.
        if (isset($data['object']) === true
            && $data['object']->getEntity()->getReference() !== 'https://larping.nl/character.schema.json'
            && $data['object']->getId() !== null
        ) {
            $data['object'] = $this->calculateCharacter($data['object']);
            // Let return data.
            return $data;
        }

        // If we do not have a single character then we are going to do all characters.
        $characterEntity = $this->entityManager
            ->getRepository('App:Entity')
            ->findOneBy(['reference' => 'https://larping.nl/character.schema.json']);

        $characters = $this->entityManager
            ->getRepository('App:ObjectEntity')
            ->findBy(['entity' => $characterEntity]);

        foreach ($characters as $character) {
            $character = $this->calculateCharacter($character);
            $this->entityManager->persist($character);
        }

        $this->entityManager->flush();

        $this->cacheService->warmup();
        // Let return data.
        return [];

    }//end statsHandler()


    /**
     * Calculate the stats for a given chararacter
     *
     * @param ObjectEntity $character The charater to calculate for
     *
     * @return ObjectEntity
     *
     * @throws \Exception
     */
    public function calculateCharacter(ObjectEntity $character):ObjectEntity
    {

        // Savety.
        if ($character->getEntity()->getReference() !== 'https://larping.nl/character.schema.json') {
            return $character;
        }

        // Reset the Character.
        $character->setValue('effects', []);
        // $character->setValue('notice', "");
        $character->setValue('stats', []);

        $character = $this->setBaseStats($character);
        $character = $this->calculateSkills($character);
        $character = $this->calculateEvents($character);
        $character = $this->calculateConditions($character);

        $character->setValue('card', $this->getMarkdowCard($character));

        return $character;

    }//end calculateCharacter()


    /**
     * Calculates the base stats for a given character
     *
     * @param ObjectEntity $character The character to calculate for
     *
     * @return ObjectEntity The calculated character
     *
     * @throws \Exception
     */
    private function setBaseStats(ObjectEntity $character): ObjectEntity
    {
        // If we do not have a single character then we are going to do all characters.
        $statsEntity = $this->entityManager
            ->getRepository('App:Entity')
            ->findOneBy(['reference' => 'https://larping.nl/stat.schema.json']);

        $stats = $this->entityManager
            ->getRepository('App:ObjectEntity')
            ->findBy(['entity' => $statsEntity]);

        $characterStats   = $character->getValue('stats');

        foreach($stats as $stat){
            $base = $stat->getValue("base");
            if($base !== null && $base != 0 && !array_key_exists($stat->getId()->toString(),$characterStats)){
                // Set the calculated effects.
                $characterStats[$stat->getId()->toString()]["name"]      = $stat->getValue('name');
                $characterStats[$stat->getId()->toString()]["base"]      = $stat->getValue('base');
                $characterStats[$stat->getId()->toString()]["value"]     = $stat->getValue('base');
            }
        }

        $character->setValue('stats', $stats);

        return $character;

    }//end setBaseStats()


    /**
     * Calculates the skills for a given character
     *
     * @param ObjectEntity $character The character to calculate for
     *
     * @return ObjectEntity The calculated character
     *
     * @throws \Exception
     */
    private function calculateSkills(ObjectEntity $character): ObjectEntity
    {

        // Pull from the character.
        $effects = $character->getValue('effects');
        $notice  = $character->getValue('notice');
        $stats   = $character->getValue('stats');
        $skills  = $character->getValue('skills');

        $this->logger->debug("Calculating ".count($skills)." skills");

        foreach ($skills as $skill) {
            foreach ($skill->getValue('effects') as $effect) {
                $stats     = $this->addEffectToStats($stats, $effect, 'Skill '.$skill->getValue('name'));
                $effects[] = $effect;
            }

            // Check skill requirements.
            foreach ($skill->getValue('requiredSkills') as $requiredSkill) {
                if (in_array($requiredSkill, $skills) === false) {
                    $name1  = $skill->getValue('name');
                    $name2  = $requiredSkill->getValue('name');
                    $notice = $name1." requires ".$name2." but this character dosn't have that skill \n".$notice;
                }
            }
        }

        // Write to the character.
        $character->setValue('effects', $effects);
        $character->setValue('notice', $notice);
        $character->setValue('stats', $stats);

        return $character;

    }//end calculateSkills()


    /**
     * Calculates the events for a given character
     *
     * @param ObjectEntity $character The character to calculate for
     *
     * @return ObjectEntity The calculated character
     *
     * @throws \Exception
     */
    private function calculateEvents(ObjectEntity $character): ObjectEntity
    {

        // Pull from the character.
        $effects = $character->getValue('effects');
        $notice  = $character->getValue('notice');
        $stats   = $character->getValue('stats');
        $events  = $character->getValue('events');

        $this->logger->debug("Calculating ".count($events)." events");
        $now = new DateTime();

        foreach ($events as $event) {
            if ($event->getValue('endDate') === false || $event->getValue('endDate') > $now) {
                continue;
            }

            foreach ($event->getValue('effects') as $effect) {
                $stats     = $this->addEffectToStats($stats, $effect, 'Event '.$event->getValue('name'));
                $effects[] = $effect;
            }
        }

        // Write to the character.
        $character->setValue('effects', $effects);
        $character->setValue('notice', $notice);
        $character->setValue('stats', $stats);

        return $character;

    }//end calculateEvents()


    /**
     * Calculates the condictions for a given character
     *
     * @param ObjectEntity $character The character to calculate for
     *
     * @return ObjectEntity The calculated character
     */
    private function calculateConditions(ObjectEntity $character): ObjectEntity
    {

        // Pull from the character.
        $effects    = $character->getValue('effects');
        $notice     = $character->getValue('notice');
        $stats      = $character->getValue('stats');
        $conditions = $character->getValue('conditions');

        $this->logger->debug("Calculating ".count($conditions)." conditions");

        foreach ($conditions as $condition) {
            foreach ($condition->getValue('effects') as $effect) {
                $stats     = $this->addEffectToStats($stats, $effect, 'Condition '.$condition->getValue('name'));
                $effects[] = $effect;
            }
        }

        // Write to the character.
        $character->setValue('effects', $effects);
        $character->setValue('notice', $notice);
        $character->setValue('stats', $stats);

        return $character;

    }//end calculateConditions()


    /**
     * Generates a markdown character table
     *
     * @param ObjectEntity $character the character to create the card for
     *
     * @return string the card as markdown
     */
    public function getMarkdowCard(ObjectEntity $character): string
    {

        $stats  = $character->getValue('stats');
        $notice = $character->getValue('notice');

        // Lets warn for invallid characters.
        $skilltable = [
            "* Calculation table*",
            "|name|base|value|effects|",
            "|---|---|---|---|",
        ];

        $skillList = ["*Skill List*"];

        foreach ($stats as $stat) {
            // Let's throw a worning if skills end up beneath 0.
            if ((int) $stat['value'] <= 0) {
                $notice = "The stat ".$stat['name']." has a below 0 value of ".$stat['value']." \n".$notice;
            }

            $skillList[] = $stat['name'].":".$stat['value'];
            $tableRow    = "|".$stat['name']."|".$stat['base']."|".$stat['value']."|".implode(',', $stat['effects'])."|";

            $skilltable[] = $tableRow;
        }

        $rows = array_merge($skillList, $skilltable);
        $character->setValue('notice', $notice);

        return  implode("\n", $rows);

    }//end getMarkdowCard()


    /**
     * Actually add the effect to the stats
     *
     * @param array        $stats  the stats
     * @param ObjectEntity $effect the effects
     *
     * @return array
     */
    private function addEffectToStats(array $stats, ObjectEntity $effect, string $title=""): array
    {

        // Stackable.
        if ($effect->getValue('positive') === true) {
        }

        $stat = $effect->getValue('stat');

        // Savety.
        if ($stat === false) {
            $this->logger->error(
                "Effect ".$effect->getValue('name')." is not asigned to a stat so wont be calculated"
            );
            return $stats;
        }

        $this->logger->debug("Calculating Effect ".$effect->getValue('name'));
        $this->logger->debug("Effect ".$effect->getValue('name')." targets ".$stat->getValue('name'));

        // Get current value.
        $value = $stat->getValue("base");
        if (isset($stats[$stat->getId()->toString()]["value"]) === true) {
            $value = $stats[$stat->getId()->toString()]["value"];
        }

        $this->logger->debug("Stat ".$stat->getValue('name')." has a current value of ".$value);
        $name         = $effect->getValue('name');
        $modification = $effect->getValue('modification');
        $modifier     = $effect->getValue('modifier');
        $this->logger->debug($name." has a  ".$modification." modification of ".$modifier);

        // Positive versus negative modifaction.
        if ($effect->getValue('modification') === 'positive') {
            $value             = ($value + $effect->getValue('modifier'));
            $effectDescription = $title." (+ ".$effect->getValue('modifier').") ".$effect->getValue('name');
        } else if ($effect->getValue('modification') !== 'positive') {
            $value             = ($value - $effect->getValue('modifier'));
            $effectDescription = $title." (- ".$effect->getValue('modifier').") ".$effect->getValue('name');
        }

        $this->logger->debug("Stat ".$stat->getValue('name')." has a end value of ".$value);

        // Set the calculated effects.
        $stats[$stat->getId()->toString()]["name"]      = $stat->getValue('name');
        $stats[$stat->getId()->toString()]["base"]      = $stat->getValue('base');
        $stats[$stat->getId()->toString()]["value"]     = $value;
        $stats[$stat->getId()->toString()]["effects"][] = $effectDescription;

        return $stats;

    }//end addEffectToStats()


}//end class
