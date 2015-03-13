<?php

namespace RAPL\RAPL\Mapping;

class Route
{
    /**
     * @var string
     */
    private $pattern = '';

    /**
     * @var boolean
     */
    private $returnsCollection;

    /**
     * @var array
     */
    private $envelopes = array();

    /**
     * @param string $pattern   The pattern used to build a URI
     * @param bool   $returnsCollection
     * @param array  $envelopes Envelopes in which the result data is wrapped
     */
    public function __construct($pattern, $returnsCollection = true, $envelopes = array())
    {
        $this->pattern           = $pattern;
        $this->returnsCollection = $returnsCollection;
        $this->envelopes         = $envelopes;
    }

    /**
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * @return bool
     */
    public function returnsCollection()
    {
        return $this->returnsCollection;
    }

    /**
     * @return array
     */
    public function getEnvelopes()
    {
        return $this->envelopes;
    }
}
