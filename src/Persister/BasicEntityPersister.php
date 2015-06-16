<?php

namespace RAPL\RAPL\Persister;

use RAPL\RAPL\Connection\ConnectionInterface;
use RAPL\RAPL\EntityManagerInterface;
use RAPL\RAPL\Mapping\ClassMetadata;
use RAPL\RAPL\Mapping\Route;
use RAPL\RAPL\Routing\AbstractRouter;
use RAPL\RAPL\Routing\RouterInterface;
use RAPL\RAPL\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Response;
use RAPL\RAPL\Routing\Query;

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

        $serializerPlugin = $manager->getSerializerPlugin();

        $this->serializer = new Serializer($classMetadata, $manager->getUnitOfWork(), $manager->getMetadataFactory(), $serializerPlugin);
        $this->router     = $router;
    }

    /**
     * Loads an entity by a list of field conditions.
     *
     * @param array       $conditions The conditions by which to load the entity.
     * @param object|null $entity     The entity to load the data into. If not specified, a new entity is created.
     * @param string      $type       Entity type, either 'resource' or 'collection'
     *
     * @return object|null The loaded and managed entity instance or NULL if the entity can not be found.
     */
    public function load(array $conditions = array(), $entity = null, $type = 'collection')
    {
        $uri     = $this->getUri($conditions);
        $route   = $this->getRoute($conditions);
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

        if($response->getStatusCode() === 204){
            return array();
        }

        //echo $response->getBody();

        $entities = $this->serializer->deserialize(
            $response->getBody(true),
            $route->returnsCollection(),
            $route->getEnvelopes()
        );

        return $entities ? $entities : null;
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
        if (isset($conditions['route'])) {
            $routeName = 'findByRoutes_'.$conditions['route'];
            unset($conditions['route']);

            $route = $this->router->chooseRoute($this->classMetadata, $routeName);
            $uri = $this->getSpecificUri($routeName, $conditions);
        } else {
            $uri      = $this->getUri($conditions, $orderBy, $limit, $offset);
            $route    = $this->getRoute($conditions, $orderBy, $limit, $offset);
        }

        $request  = $this->connection->createRequest('GET', $uri);
        $response = $this->connection->sendRequest($request);

        if($response->getStatusCode() === 204){
            return array();
        }
        //echo $response->getBody();die;

        return $this->serializer->deserialize(
            $response->getBody(),
            $route->returnsCollection(),
            $route->getEnvelopes()
        );
    }

    /**
     * Returns an URI based on a set of criteria
     *
     * @param array    $conditions
     * @param array    $orderBy
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return string
     */
    private function getUri(array $conditions, array $orderBy = array(), $limit = null, $offset = null)
    {
        return $this->router->generate($this->classMetadata, $conditions, $orderBy, $limit, $offset);
    }

    /**
     * Returns an URI based on a set of criteria
     *
     * @param array    $conditions
     * @param array    $orderBy
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return string
     */
    private function getSpecificUri($query, array $conditions, array $orderBy = array(), $limit = null, $offset = null)
    {
        return $this->router->generateSpecific($this->classMetadata, $query, $conditions, $orderBy, $limit, $offset);
    }

    /**
     * @param array    $conditions
     * @param array    $orderBy
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return \RAPL\RAPL\Mapping\Route
     */
    private function getRoute(array $conditions, array $orderBy = array(), $limit = null, $offset = null)
    {
        return $this->router->getRoute($this->classMetadata, $conditions, $orderBy, $limit, $offset);
    }

    /**
     * Saves an entity.
     *
     * @param array       $conditions
     * @param object|null $entity     The entity to save. If not specified, nothing is sent.
     *
     * @return object|null The loaded and managed entity instance or NULL if the entity can not be found.
     */
    public function save(array $conditions, $entity = null)
    {
        //NEW
        $query = $query = new Query($conditions);

        $route = $this->router->chooseRoute($this->classMetadata, 'creation');
        $uri = $this->getSpecificUri('creation', $conditions);

        //$json = json_encode($entity->toArray());
        //var_dump($json);die;

        $request = $this->connection->createRequest('POST', $uri, ['json' => $entity->toArray($this->classMetadata)]);
        //var_dump($request->getHeader('Content-Type'));
        //echo $request->getBody();die;

        //var_dump($entity->toArray($this->classMetadata));die;

        //try {
            $response = $this->connection->sendRequest($request);
        /*} catch (ClientEr $e) {
            if ($e->getResponse()->getStatusCode() == 404) {
                return null;
            } else {
                throw $e;
            }
        }*/

            return $this->serializer->deserialize(
                    $response->getBody(),
                    $route->returnsCollection(),
                    $route->getEnvelopes(),
                    $entity
            );
    }

    public function remove(array $conditions, $entity = null)
    {
        //NEW
        $query = new Query($conditions);

        $route = $this->router->chooseRoute($this->classMetadata, 'remove');
        $uri = $this->getSpecificUri('remove', ['id' => $entity->getId()]);

        //$json = json_encode($entity->toArray());
        //var_dump($json);die;

        $request = $this->connection->createRequest('DELETE', $uri, ['json' => $entity->toArray($this->classMetadata)]);
        //var_dump($request->getHeader('Content-Type'));
        //echo $request->getBody();die;

        //var_dump($entity->toArray($this->classMetadata));die;

        //try {
        $response = $this->connection->sendRequest($request);

        //echo $response->getBody();die;
        /*} catch (ClientEr $e) {
         if ($e->getResponse()->getStatusCode() == 404) {
         return null;
         } else {
         throw $e;
         }
        }*/

        return $response->json();

        /*return $this->serializer->deserialize(
                $response->getBody(),
                $route->returnsCollection(),
                $route->getEnvelopes()
        );*/
    }

    public function merge(array $conditions, $entity = null)
    {
    	//NEW
    	$query = new Query($conditions);

        $conditions['id'] = $entity->getId();

    	$route = $this->router->chooseRoute($this->classMetadata, 'update');
    	$uri = $this->getSpecificUri('update', $conditions);

    	//$json = json_encode($entity->toArray());
    	//var_dump($json);die;

    	$request = $this->connection->createRequest('PUT', $uri, ['json' => $entity->toArray($this->classMetadata)]);
    	//var_dump($request->getHeader('Content-Type'));
    	//echo $request->getBody();die;

        //var_dump($entity->toArray($this->classMetadata));die;

    	//try {
    	$response = $this->connection->sendRequest($request);

    	//echo $response->getBody();die;
    	/*} catch (ClientEr $e) {
    	if ($e->getResponse()->getStatusCode() == 404) {
    	return null;
    	} else {
    	throw $e;
    	}
    	}*/

    	return $this->serializer->deserialize(
                    $response->getBody(),
                    $route->returnsCollection(),
                    $route->getEnvelopes(),
                    $entity
            );
    }

    public function performAlternative(array $conditions, $entity = null)
    {
        $routeName = $conditions['route'];
        unset($conditions['route']);

        if (isset($conditions['json'])) {
            $json = $conditions['json'];
            unset($conditions['json']);
        }

        $query = $query = new Query($conditions);

        if ($this->classMetadata->hasAlternativeRoute($routeName)) {
            /* @var $route Route */
            $route = $this->classMetadata->getAlternativeRoute($routeName);
        }

        $path = AbstractRouter::buildPath($route->getPattern(), $query);
        $queryString = AbstractRouter::buildQueryString($query);
        if (empty($queryString)) {
            $uri = $path;
        } else {
            $uri = $path . '?' . $queryString;
        }

        if (isset($json)) {
            $request = $this->connection->createRequest($route->getEnvelopes()['method'], $uri, ['json' => $json]);
        } else {
            $request = $this->connection->createRequest($route->getEnvelopes()['method'], $uri);
        }

        $response = $this->connection->sendRequest($request);

        if(isset($conditions['returnObject'])){
            if($response->getStatusCode() === 204){
                return array();
            }
            return $this->serializer->deserialize(
                $response->getBody(),
                $route->returnsCollection(),
                $route->getEnvelopes()
            );
        }
        else{
            return $response;
        }
    }
}