<?php

namespace LarpingBase\LarpingBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use LarpingBase\LarpingBundle\Service\LarpingService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CalculateCharactersCommand extends Command
{
    protected static $defaultName = 'larping:calculate:characters';
    private LarpingService $larpingService;
    private EntityManagerInterface $entityManager;

    public function __construct(LarpingService $larpingService, EntityManagerInterface $entityManager)
    {
        $this->larpingService = $larpingService;
        $this->entityManager = $entityManager;
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

        $characterEntity = $this->entityManager->getRepository('App:Entity')->findBy(['entity'=>'https://larping.nl/character.schema.json']);

        if(!$characterEntity){
            $io->error("No entity for characters found");
           return 1;
        }
        $characters = $this->entityManager->getRepository('App:ObjectEntity')->findBy(['entity'=>$characterEntity]);

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

        return 0;
    }
}
