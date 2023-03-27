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
        $character->setValue('notice', "");
        $character->setValue('stats', []);

        $character = $this->calculateSkills($character);
        $character = $this->calculateEvents($character);
        $character = $this->calculateConditions($character);

        $character->setValue('card', $this->getMarkdowCard($character));

        return $character;

    }//end calculateCharacter()


    /**
     * Calculates the skills for a given character
     *
     * @param  ObjectEntity $character The character to calculate for
     * @return ObjectEntity The calculated character
     */
    private function calculateSkills(ObjectEntity $character): ObjectEntity
    {

        // Pull from the character
        $effects = $character->getValue('effects');
        $notice  = $character->getValue('notice');
        $stats   = $character->getValue('stats');
        $skills  = $character->getValue('skills');

        $this->logger->debug("Calculating ".count($skills)." skills");

        foreach ($skills as $skill) {
            foreach ($skill->getValue('effects') as $effect) {
                $stats     = $this->addEffectToStats($stats, $effect);
                $effects[] = $effect;
            }

            // Check skill requirements.
            foreach ($skill->getValue('requiredSkills') as $requiredSkill) {
                if (in_array($requiredSkill, $skills) === false) {
                    $notice = "The skill ".$skill->getValue('name')." has requirement on the skill ".$requiredSkill->getValue('name')." but this character dosn't seem to have that skill \n".$notice;
                }
            }
        }

        // Write to the character
        $character->setValue('effects', $effects);
        $character->setValue('notice', $notice);
        $character->setValue('stats', $stats);

        return $character;

    }//end calculateSkills()


    /**
     * Calculates the events for a given character
     *
     * @param  ObjectEntity $character The character to calculate for
     * @return ObjectEntity The calculated character
     */
    private function calculateEvents(ObjectEntity $character): ObjectEntity
    {

        // Pull from the character
        $effects = $character->getValue('effects');
        $notice  = $character->getValue('notice');
        $stats   = $character->getValue('stats');
        $events  = $character->getValue('events');
        $this->logger->debug("calculating ".count($events)." events");
        $now = new DateTime();

        foreach ($events as $event) {
            // Todo: Continu if enddate is empty or greater then now.
            if ($event->getValue('endDate') === false || $event->getValue('endDate') > $now) {
                continue;
            }

            foreach ($event->getValue('effects') as $effect) {
                $stats     = $this->addEffectToStats($stats, $effect);
                $effects[] = $effect;
            }
        }

        // Write to the character
        $character->setValue('effects', $effects);
        $character->setValue('notice', $notice);
        $character->setValue('stats', $stats);

        return $character;

    }//end calculateEvents()


    /**
     * Calculates the condictions for a given character
     *
     * @param  ObjectEntity $character The character to calculate for
     * @return ObjectEntity The calculated character
     */
    private function calculateConditions(ObjectEntity $character): ObjectEntity
    {

        // Pull from the character
        $effects    = $character->getValue('effects');
        $notice     = $character->getValue('notice');
        $stats      = $character->getValue('stats');
        $conditions = $character->getValue('conditions');
        $this->logger->debug("calculating ".count($conditions)." conditions");

        foreach ($conditions as $condition) {
            foreach ($condition->getValue('effects') as $effect) {
                $stats     = $this->addEffectToStats($stats, $effect);
                $effects[] = $effect;
            }
        }       //end foreach

        $conditions = $character->getValue('conditions');
        $this->logger->debug("calculating ".count($conditions)." conditions");
        foreach ($conditions as $condition) {
            foreach ($condition->getValue('effects') as $effect) {
                $stats     = $this->addEffectToStats($stats, $effect);
                $effects[] = $effect;
            }
        }

        // Write to the character
        $character->setValue('effects', $effects);
        $character->setValue('notice', $notice);
        $character->setValue('stats', $stats);

        return $character;

    }//end calculateConditions()


    /**
     * Generates a markdown character table
     *
     * @param  ObjectEntity $character the character to create the card for
     * @return string the card as markdown
     */
    public function getMarkdowCard(ObjectEntity $character): string
    {

        $stats = $character->getValue('stats');

        // Lets warn for invallid characters.
        $rows = [
            "|name|base|value|effects|",
            "|---|---|---|---|",
        ];

        foreach ($stats as $stat) {
            // Let's throw a worning if skills end up beneath 0.
            if ((int) $stat['value'] <= 0) {
                $notice = "The stat ".$stat['name']." has a below 0 value of ".$stat['value']." \n".$notice;
            }

            $row = "|".$stat['name']."|".$stat['base']."|".$stat['value']."|".implode(',', $stat['effects'])."|";

            $rows[] = $row;
        }

        return  implode("\n", $rows);

    }//end getMarkdowCard()


    /**
     * Actually add the efeect to the stats
     *
     * @param array        $stats  the stats
     * @param ObjectEntity $effect the effects
     *
     * @return array
     */
    private function addEffectToStats(array $stats, ObjectEntity $effect): array
    {

        // Stackable.
        if ($effect->getValue('positive') === true) {
        }

        $stat = $effect->getValue('stat');

        // Savety.
        if ($stat === false) {
            $this->logger->error("Effect ".$effect->getValue('name')." is not asigned to a stat so wont be calculated");
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
        $this->logger->debug("Effect ".$effect->getValue('name')." has a  ".$effect->getValue('modification')." modification of ".$effect->getValue('modifier'));

        // Positive versus negative modifaction.
        if ($effect->getValue('modification') === 'positive') {
            $value             = ($value + $effect->getValue('modifier'));
            $effectDescription = "(+ ".$effect->getValue('modifier').") ".$effect->getValue('name');
        } elseif($effect->getValue('modification') !== 'positive') {
            $value             = ($value - $effect->getValue('modifier'));
            $effectDescription = "(- ".$effect->getValue('modifier').") ".$effect->getValue('name');
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
