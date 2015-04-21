<?php

namespace RAPL\RAPL;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use RAPL\RAPL\Persister\EntityPersister;

class EntityRepository
{
    /**
     * @var EntityPersister
     */
    protected $persister;

    /**
     * @var ClassMetadata
     */
    protected $classMetadata;

    /**
     * @param EntityPersister $persister
     * @param ClassMetadata   $classMetadata
     */
    public function __construct(EntityPersister $persister, ClassMetadata $classMetadata)
    {
        $this->persister     = $persister;
        $this->classMetadata = $classMetadata;
    }

    /**
     * Finds an object by its primary key / identifier.
     *
     * @param mixed $id The identifier.
     *
     * @return object The object.
     */
    public function find($id, array $conditions = array())
    {
    	$conditions['id'] = $id;
        return $this->persister->loadById($conditions);
    }

    /**
     * Finds all objects in the repository.
     *
     * @return array The objects.
     */
    public function findAll(array $conditions = array())
    {
        return $this->findBy($conditions);
    }

    /**
     * Finds objects by a set of criteria.
     *
     * Optionally sorting and limiting details can be passed. An implementation may throw
     * an UnexpectedValueException if certain values of the sorting or limiting details are
     * not supported.
     *
     * @param array    $criteria
     * @param array    $orderBy
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return array The objects.
     *
     * @throws \UnexpectedValueException
     */
    public function findBy(array $criteria, array $orderBy = array(), $limit = null, $offset = null)
    {
        return $this->persister->loadAll($criteria, $orderBy, $limit, $offset);
    }

    /**
     * Finds a single object by a set of criteria.
     *
     * @param array $criteria The criteria.
     *
     * @return object The object.
     */
    public function findOneBy(array $criteria)
    {
        $results = $this->findBy($criteria);

        return isset($results[0]) ? $results[0] : null;
    }

    /**
     * Returns the class name of the object managed by the repository.
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->classMetadata->getName();
    }
}
