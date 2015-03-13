<?php

namespace RAPL\RAPL\Serializer;

interface SerializerInterface
{
    /**
     * Deserializes serialized data
     *
     * @param string  $data
     * @param boolean $isCollection
     * @param array   $envelopes
     *
     * @return mixed
     */
    public function deserialize($data, $isCollection, array $envelopes);
}
