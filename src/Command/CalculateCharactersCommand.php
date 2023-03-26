<?php
/**
 * A command to calculate the stats of al characters
 *
 * @author  Conduction.nl <info@conduction.nl>
 * @license EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 */

namespace LarpingBase\LarpingBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use LarpingBase\LarpingBundle\Service\LarpingService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use CommonGateway\CoreBundle\Service\CacheService;

class CalculateCharactersCommand extends Command
{

    /**
     * The symfony required default name
     *
     * @var string
     */
    protected static $defaultName = 'larping:calculate:characters';

    /**
     * The larpings service
     *
     * @var LarpingService
     */
    private LarpingService $larpingService;

    /**
     * The entity manager
     *
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;


    /**
     * @param LarpingService         $larpingService The larping service
     * @param EntityManagerInterface $entityManager  The entity manager
     * @param CacheService           $cacheService   The cache service
     */
    public function __construct(
        LarpingService $larpingService,
        EntityManagerInterface $entityManager,
        CacheService $cacheService
    ) {
        $this->larpingService = $larpingService;
        $this->entityManager  = $entityManager;
        $this->cacheService   = $cacheService;
        parent::__construct();

    }//end __construct()


    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setDescription('This command calculated character stats')
            ->setHelp('Trun this command to update all or a single character stats');

    }//end configure()


    /**
     * @param InputInterface  $input  The input
     * @param OutputInterface $output The output
     *
     * @return int
     *
     * @throws \Exception
     *
     * @SuppressWarnings("unused") Required by symfony
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // $this->cacheService->setStyle(new SymfonyStyle($input, $output));
        $style = new SymfonyStyle($input, $output);

        $characterEntity = $this->entityManager
            ->getRepository('App:Entity')
            ->findBy(['reference' => 'https://larping.nl/character.schema.json']);

        if ($characterEntity === null) {
            $style->error("No entity for characters found");
            return 1;
        }

        $characters = $this->entityManager
            ->getRepository('App:ObjectEntity')
            ->findBy(['entity' => $characterEntity]);

        $style->title('Calculating all characters');
        $style->note('Found '.count($characters).' characters');
        foreach ($characters as $character) {
            $style->section('Calculating '.$character->getName());
            $character = $this->larpingService->calculateCharacter($character);
            $this->entityManager->persist($character);

            // Build a nice table.
            $headers = [
                'stat',
                'base',
                'current',
                'modifiers',
            ];
            $rows    = [];
            foreach ($character->getValue('stats') as $key => $stat) {
                $row    = [
                    $stat['name'],
                    $stat['base'],
                    $stat['value'],
                    implode(',', $stat['effects']),
                ];
                $rows[] = $row;
            }

            $style->table($headers, $rows);

            $style->info("The simplyfied character card");
            $style->note($character->getValue('card'));

            $style->info("Any notices for this character");
            $style->note($character->getValue('notice'));
        }//end foreach

        $style->note('Saving result to database');
        $this->entityManager->flush();
        $style->success('Al done!');

        return $this->cacheService->warmup();

    }//end execute()


}//end class
