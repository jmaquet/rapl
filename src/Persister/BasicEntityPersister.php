<?php

namespace RAPL\RAPL\Persister;

use Guzzle\Http\Exception\ClientErrorResponseException;
use RAPL\RAPL\Connection\ConnectionInterface;
use RAPL\RAPL\EntityManagerInterface;
use RAPL\RAPL\Mapping\ClassMetadata;
use RAPL\RAPL\Routing\RouterInterface;
use RAPL\RAPL\Serializer\Serializer;

class BasicEntityPersister implements EntityPersister
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @var ConnectionInterface
     */
    private $connection;

    /**
     * @var ClassMetadata
     */
    private $classMetadata;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @param EntityManagerInterface $manager
     * @param ClassMetadata          $classMetadata
     * @param RouterInterface        $router
     */
    public function __construct(EntityManagerInterface $manager, ClassMetadata $classMetadata, RouterInterface $router)
    {
        $this->manager       = $manager;
        $this->connection    = $manager->getConnection();
        $this->classMetadata = $classMetadata;

        $this->serializer = new Serializer($manager, $classMetadata);
        $this->router     = $router;
    }

    /**
     * Loads an entity by a list of field conditions.
     *
     * @param array       $conditions The conditions by which to load the entity.
     * @param object|null $entity   The entity to load the data into. If not specified, a new entity is created.
     * @param string      $type     Entity type, either 'resource' or 'collection'
     *
     * @return object|null The loaded and managed entity instance or NULL if the entity can not be found.
     */
    public function load(array $conditions, $entity = null, $type = 'collection')
    {
        $uri     = $this->getUri($conditions);
        $request = $this->connection->createRequest('GET', $uri);

        try {
            $response = $this->connection->sendRequest($request);
        } catch (ClientErrorResponseException $e) {
            if ($e->getResponse()->getStatusCode() == 404) {
                return null;
            } else {
                throw $e;
            }
        }

        $entities = $this->serializer->deserialize($response->getBody(true), $type);

        return $entities ? $entities[0] : null;
    }

    /**
     * Loads an entity by identifier.
     *
     * @param array       $identifier The entity identifier.
     * @param object|null $entity     The entity to load the data into. If not specified, a new entity is created.
     *
     * @return object|null The loaded and managed entity instance or NULL if the entity can not be found.
     */
    public function loadById(array $identifier, $entity = null)
    {
        return $this->load($identifier, $entity, 'resource');
    }

    /**
     * Loads a list of entities by a list of field conditions.
     *
     * @param array      $conditions
     * @param array|null $orderBy
     * @param int|null   $limit
     * @param int|null   $offset
     *
     * @return array
     */
    public function loadAll(array $conditions = array(), array $orderBy = array(), $limit = null, $offset = null)
    {
        $uri      = $this->getUri($conditions, $orderBy, $limit, $offset);
        $request  = $this->connection->createRequest('GET', $uri);
        $response = $this->connection->sendRequest($request);

        return $this->serializer->deserialize($response->getBody(true));
    }

    /**
     * Returns an URI based on a set of criteria
     *
     * @param array $conditions
     * @param array $orderBy
     * @param null  $limit
     * @param null  $offset
     *
     * @return string
     */
    private function getUri(array $conditions, array $orderBy = array(), $limit = null, $offset = null)
    {
        return $this->router->generate($this->classMetadata, $conditions, $orderBy, $limit, $offset);
    }
}
