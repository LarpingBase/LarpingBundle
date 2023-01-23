<?php

namespace CommonGateway\CoreBundle\Command;

use Doctrine\ORM\EntityManager;
use LarpingBase\LarpingBundle\Service\LarpingService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CalculateCharactersCommand extends Command
{
    protected static $defaultName = 'larping:calculate:characters';
    private LarpingService $larpingService;
    private EntityManager $entityManager;

    public function __construct(LarpingService $larpingService, EntityManager $entityManager)
    {
        $this->larpingService = $larpingService;
        $this->$entityManager = $entityManager;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('This command removes outdated objects from the cache')
            ->setHelp('This command allows you to run further installation an configuration actions afther installing a plugin');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        //$this->cacheService->setStyle(new SymfonyStyle($input, $output));
        $io = new SymfonyStyle($input, $output);
        $characters = $this->entityManager->getRepository('App:ObjectEntity')->findBy(['entity.reference'=>'https://larping.nl/character.schema.json']);

        $io->title('Calculating all characters');
        $io->note('Found '.count($characters).' characters');
        foreach ($characters as $character){
            $io->section('Calculating '.$character->getName);
            $character = $this->larpingService->calculateCharacter($character);
            $this->entityManager->persist($character);

            // Build a nice table
            $headers = ['stat','value','modifiers'];
            $rows = [];
            foreach($character->getValue('stats') as $key => $stat){
                $row = [
                    $stat['name'],
                    $stat['value'],
                    implode(',',$stat['effects'])
                ];
                $rows[] = $row;
            }

            $io->table($headers, $rows);
        }

        $io->note('Saving result to database');
        $this->entityManager->flush();
        $io->success('Al done!');

        return $this->cacheService->cleanup();
    }
}
