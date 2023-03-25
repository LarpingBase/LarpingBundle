<?php
/**
 * The larping installation service
 *
 * @author  Conduction.nl <info@conduction.nl>
 * @license EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 */

namespace LarpingBase\LarpingBundle\Service;

use App\Entity\Action;
use App\Entity\DashboardCard;
use App\Entity\Endpoint;
use App\Entity\Entity;
use CommonGateway\CoreBundle\Installer\InstallerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;

class InstallationService implements InstallerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * @var SymfonyStyle
     */
    private SymfonyStyle $io;

    /**
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    public const ACTION_HANDLERS = [
        'LarpingBase\LarpingBundle\ActionHandler\StatsHandler'
    ];

    public const SCHEMAS_THAT_SHOULD_HAVE_ENDPOINTS = [
        ['reference' => 'https://larping.nl/test.schema.json','path' => '/ref/test','methods' => []],
    ];

    public function __construct(
        EntityManagerInterface $entityManager,
        ContainerInterface $container
    ) {
        $this->entityManager = $entityManager;
        $this->container = $container;
    }

    /**
     * Set symfony style in order to output to the console
     *
     * @param  SymfonyStyle $io
     * @return self
     */
    public function setStyle(SymfonyStyle $io):self
    {
        $this->io = $io;

        return $this;
    }

    public function install()
    {
        $this->checkDataConsistency();
    }

    public function update()
    {
        $this->checkDataConsistency();
    }

    public function uninstall()
    {
        // Do some cleanup
    }

    public function addActionConfiguration($actionHandler): array
    {
        $defaultConfig = [];

        // What if there are no properties?
        if (!isset($actionHandler->getConfiguration()['properties'])) {
            return $defaultConfig;
        }

        foreach ($actionHandler->getConfiguration()['properties'] as $key => $value) {

            switch ($value['type']) {
            case 'string':
            case 'array':
                $defaultConfig[$key] = $value['example'];
                break;
            case 'object':
                break;
            case 'uuid':
                if (in_array('$ref', $value) 
                    && $entity = $this->entityManager->getRepository('App:Entity')->findOneBy(['reference'=>$value['$ref']])
                ) {
                    $defaultConfig[$key] = $entity->getId()->toString();
                }
                break;
            default:
                // throw error
            }
        }
        return $defaultConfig;
    }

    /**
     * This function creates actions for all the actionHandlers in OpenCatalogi
     *
     * @return void
     */
    public function addActions(): void
    {
        $actionHandlers = $this::ACTION_HANDLERS;
        (isset($this->io)?$this->io->writeln(['','<info>Looking for actions</info>']):'');

        foreach ($actionHandlers as $handler) {
            $actionHandler = $this->container->get($handler);

            if ($this->entityManager->getRepository('App:Action')->findOneBy(['class' => get_class($actionHandler)])) {

                (isset($this->io) ? $this->io->writeln(['Action found for ' . $handler]) : '');
                continue;
            }

            if (!$schema = $actionHandler->getConfiguration()) {
                continue;
            }

            $defaultConfig = $this->addActionConfiguration($actionHandler);



            $action = new Action($actionHandler);
            $action->setListens(['commongateway.object.pre.create','commongateway.object.pre.update']);
            $action->setConfiguration($defaultConfig);

            $this->entityManager->persist($action);

            (isset($this->io) ? $this->io->writeln(['Action created for ' . $handler]) : '');
        }
    }

    private function createEndpoints($objectsThatShouldHaveEndpoints): array
    {
        $endpointRepository = $this->entityManager->getRepository('App:Endpoint');
        $endpoints = [];
        foreach($objectsThatShouldHaveEndpoints as $objectThatShouldHaveEndpoint) {
            $entity = $this->entityManager->getRepository('App:Entity')->findOneBy(['reference' => $objectThatShouldHaveEndpoint['reference']]);
            if ($entity instanceof Entity && !$endpointRepository->findOneBy(['name' => $entity->getName()])) {
                $endpoint = new Endpoint($entity, null, [$objectThatShouldHaveEndpoint['path'], $objectThatShouldHaveEndpoint['methods']]);

                $this->entityManager->persist($endpoint);
                $this->entityManager->flush();
                $endpoints[] = $endpoint;
            }
        }
        (isset($this->io) ? $this->io->writeln(count($endpoints).' Endpoints Created'): '');

        return $endpoints;
    }

    public function checkDataConsistency()
    {

        // Lets create some genneric dashboard cards
        $objectsThatShouldHaveCards = ['https://larping.nl/character.schema.json','https://larping.nl/skill.schema.json'];

        foreach($objectsThatShouldHaveCards as $object){
            (isset($this->io)?$this->io->writeln('Looking for a dashboard card for: '.$object):'');
            $entity = $this->entityManager->getRepository('App:Entity')->findOneBy(['reference'=>$object]);
            if(!$dashboardCard = $this->entityManager->getRepository('App:DashboardCard')->findOneBy(['entityId'=>$entity->getId()])
            ) {
                $dashboardCard = New DashboardCard();
                $dashboardCard->setType('schema');
                $dashboardCard->setEntity('App:Entity');
                $dashboardCard->setObject('App:Entity');
                $dashboardCard->setName($entity->getName());
                $dashboardCard->setDescription($entity->getDescription());
                $dashboardCard->setEntityId($entity->getId());
                $dashboardCard->setOrdering(1);
                $this->entityManager->persist($dashboardCard);
                (isset($this->io) ?$this->io->writeln('Dashboard card created'):'');
                continue;
            }
            (isset($this->io)?$this->io->writeln('Dashboard card found'):'');
        }

        // Let create some endpoints
        $this->createEndpoints($this::SCHEMAS_THAT_SHOULD_HAVE_ENDPOINTS);

        // aanmaken van Actions
        $this->addActions();


        $this->entityManager->flush();


    }
}
