<?php

namespace RAPL\RAPL\Persister;

interface EntityPersister
{
    /**
     * Loads an entity by a list of field conditions.
     *
     * @param array       $conditions The conditions by which to load the entity.
     * @param object|null $entity     The entity to load the data into. If not specified, a new entity is created.
     *
     * @return object|null The loaded and managed entity instance or NULL if the entity can not be found.
     */
    public function load(array $conditions = array(), $entity = null);

    /**
     * Loads an entity by identifier.
     *
     * @param array       $identifier The entity identifier.
     * @param object|null $entity     The entity to load the data into. If not specified, a new entity is created.
     *
     * @return object|null The loaded and managed entity instance or NULL if the entity can not be found.
     */
    public function loadById(array $identifier, $entity = null);

    /**
     * Loads a list of entities by a list of field conditions.
     *
     * @param array    $conditions
     * @param array    $orderBy
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return array
     */
    public function loadAll(array $conditions = array(), array $orderBy = array(), $limit = null, $offset = null);

    //NEW
    public function save(array $conditions, $entity = null);

    public function remove(array $conditions, $entity = null);

    public function merge(array $conditions, $entity = null);

    public function performAlternative(array $conditions, $entity = null);
}
