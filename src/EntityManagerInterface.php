<?php

namespace RAPL\RAPL;

use Danone\BoinsiderBundle\RAPL\Plugin\SerializerPlugin;
use RAPL\RAPL\Connection\ConnectionInterface;

interface EntityManagerInterface
{
    /**
     * @return ConnectionInterface
     */
    public function getConnection();

    /**
     * @return Configuration
     */
    public function getConfiguration();

    /**
     * @return UnitOfWork
     */
    public function getUnitOfWork();

    /**
     * @return SerializerPlugin
     */
    public function getSerializerPlugin();
}
