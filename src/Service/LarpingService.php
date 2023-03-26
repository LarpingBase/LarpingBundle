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
use \DateTime;
use Symfony\Component\Console\Style\SymfonyStyle;
use CommonGateway\CoreBundle\Service\CacheService;

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
        if (isset($data['object']) === true  // Only trigger id we have an object.
            && $data['object']->getEntity()->getReference() !== 'https://larping.nl/character.schema.json' // Make sure we have a larping character.
            && $data['object']->getId() // Make sure the character has an id.
        ) {
            $data['object'] = $this->calculateCharacter($data['object']);
            // Let return data.
            return $data;
        }

        // If we do not have a single character then we are going to do all characters.
        $characterEntity = $this->entityManager->getRepository('App:Entity')->findOneBy(['reference' => 'https://larping.nl/character.schema.json']);

        $characters = $this->entityManager->getRepository('App:ObjectEntity')->findBy(['entity' => $characterEntity]);

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

        $effects = [];
        $stats   = [];
        $notice  = "";

        // Skills
        $skills = $character->getValue('skills');
        $this->logger->debug("Calculating ".count($skills)." skills");
        // ad characters
        foreach ($skills as $skill) {
            foreach ($skill->getValue('effects') as $effect) {
                $stats     = $this->addEffectToStats($stats, $effect, $effects);
                $effects[] = $effect;
            }

            // Check skill requirements.
            foreach ($skill->getValue('requiredSkills') as $requiredSkill) {
                if (in_array($requiredSkill, $skills) === false) {
                    $notice = "The skill ".$skill->getValue('name')." has requirement on the skill ".$requiredSkill->getValue('name')." but this character dosn't seem to have that skill \n".$notice;
                }
            }
        }

        // Events.
        $now    = new DateTime();
        $events = $character->getValue('events');
        $this->logger->debug("calculating ".count($events)." events"))
        foreach ($events as $event) {
            // Todo: Continu if enddate is empty or greater then now
            if (! $event->getValue('endDate') || $event->getValue('endDate') > $now) {
                continue;
            }

            foreach ($event->getValue('effects') as $effect) {
                $stats     = $this->addEffectToStats($stats, $effect, $effects);
                $effects[] = $effect;
            }
        }

        // Conditions.
        $conditions = $character->getValue('conditions');
        $this->logger->debug("calculating ".count($conditions)." conditions");
        foreach ($conditions as $condition) {
            foreach ($condition->getValue('effects') as $effect) {
                $stats     = $this->addEffectToStats($stats, $effect, $effects);
                $effects[] = $effect;
            }
        }

        // Lets warn for invallid characters.
        $rows = [
            "|name|base|value|effects|",
            "|---|---|---|---|",
        ];

        foreach ($stats as $key => $stat) {
            // Lets throw a worning if skills end up beneath 0
            if ((int) $stat['value'] <= 0) {
                $notice = "The stat ".$stat['name']." has a below 0 value of ".$stat['value']." \n".$notice;
            }

            $row = "|".$stat['name']."|".$stat['base']."|".$stat['value']."|".implode(',', $stat['effects'])."|";

            $rows[] = $row;
        }

        $character->setValue('card', implode("\n", $rows));
        $character->setValue('notice', $notice);
        $character->setValue('stats', $stats);

        return $character;

    }//end calculateCharacter()


    private function addEffectToStats(array $stats, ObjectEntity $effect, $effects): array
    {

        // Stackable.
        if ($effect->getValue('positive')) {
        }

        $stat = $effect->getValue('stat');

        // Savety.
        if (!$stat) {
            $this->logger->error("Effect ".$effect->getValue('name')." is not asigned to a stat so wont be calculated");
            return $stats;
        }

            $this->logger->debug("Calculating Effect ".$effect->getValue('name'));
            $this->logger->debug("Effect ".$effect->getValue('name')." targets ".$stat->getValue('name'));

        // Get current value.
        if (isset($stats[$stat->getId()->toString()]["value"])) {
                $this->logger->debug("Adding to existing stat");
            $value = $stats[$stat->getId()->toString()]["value"];
        } else {
                $this->logger->debug("Adding stat to character ");
            $value = $stat->getValue("base");
        }

            $this->logger->debug("Stat ".$stat->getValue('name')." has a current value of ".$value);

            $this->logger->debug("Effect ".$effect->getValue('name')." has a  ".$effect->getValue('modification')." modification of ".$effect->getValue('modifier'));

        // Positive versus negative modifaction.
        if ($effect->getValue('modification') === 'positive') {
            $value             = ($value + $effect->getValue('modifier'));
            $effectDescription = "(+ ".$effect->getValue('modifier').") ".$effect->getValue('name');
        } else {
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
