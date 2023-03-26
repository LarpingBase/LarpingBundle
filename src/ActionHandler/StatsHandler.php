<?php
/**
 * An action handler for checking stats
 *
 * @author  Conduction.nl <info@conduction.nl>
 * @license EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 */

namespace LarpingBase\LarpingBundle\ActionHandler;

use CommonGateway\CoreBundle\ActionHandler\ActionHandlerInterface;
use LarpingBase\LarpingBundle\Service\LarpingService;

class StatsHandler implements ActionHandlerInterface
{

    /**
     * Get the coure larping service
     *
     * @var LarpingService
     */
    private LarpingService $larpingService;

    /**
     * Set default conditions
     */
    public const DEFAULT_CONDITIONS = [
        '==' => [
            "_self.schema.ref",
            "https://larping.nl/character.schema.json",
        ],
    ];

    /**
     * Set default listens
     */
    public const DEFAULT_LISTENS = [
        'commongateway.object.pre.create',
        'commongateway.object.pre.update',
    ];


    /**
     * The constructor for this class
     *
     * @param LarpingService $larpingService The larping service
     */
    public function __construct(LarpingService $larpingService)
    {
        $this->larpingService = $larpingService;

    }//end __construct()


    /**
     *  This function returns the  configuration as a [json-schema](https://json-schema.org/) array.
     *
     * @return array a [json-schema](https://json-schema.org/) that this  action should comply to
     */
    public function getConfiguration(): array
    {
        return [
            '$id'         => 'https://example.com/person.schema.json',
            '$schema'     => 'https://json-schema.org/draft/2020-12/schema',
            'title'       => 'Stats Action',
            'description' => 'This handler calculates the stats for characters',
            'required'    => [],
            'properties'  => [],
        ];

    }//end getConfiguration()


    /**
     * This function runs the service.
     *
     * @param array $data          The data from the call
     * @param array $configuration The configuration of the action
     *
     * @throws GatewayException
     * @throws CacheException
     * @throws InvalidArgumentException
     * @throws ComponentException
     *
     * @return array
     *
     * @SuppressWarnings("unused") Handlers ara strict implementations
     */
    public function run(array $data, array $configuration): array
    {
        return $this->larpingService->statsHandler($data);

    }//end run()


}//end class
