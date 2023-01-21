<?php

namespace LarpingBase\LarpingBundle\ActionHandler;

use CommonGateway\PetStoreBundle\Service\LarpingService;

class StatsHandler
{
    private LarpingService $larpingService;

    /*
     * Set default conditions
     */
    public const DEFAULT_CONDITIONS = [
        '==' => ["\$ref","https://larping.nl/character.schema.json"]
    ];

    /*
     * Set default listens
     */
    public const DEFAULT_LISTENS = [
        ''
    ];

    public function __construct(LarpingService $larpingService)
    {
        $this->larpingService = $larpingService;
    }

    /**
     *  This function returns the requered configuration as a [json-schema](https://json-schema.org/) array.
     *
     * @throws array a [json-schema](https://json-schema.org/) that this  action should comply to
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
    }

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
     */
    public function run(array $data, array $configuration): array
    {
        return $this->larpingService->statsHandler($data, $configuration);
    }
}
