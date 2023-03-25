<?php
/**
 * The larping extension for symfony
 *
 * @author  Conduction.nl <info@conduction.nl>
 * @license EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 */

namespace LarpingBase\LarpingBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class LarpingExtension extends Extension
{


    /**
     * The default symfony load class
     *
     * @param  array            $configs   The configuration
     * @param  ContainerBuilder $container The container
     *
     * @return void
     *
     * @SuppressWarnings("unused") Required by symfony
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../../Resources/config')
        );
        $loader->load('services.yaml');

    }//end load()


}//end class
