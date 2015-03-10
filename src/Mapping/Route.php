<?php

namespace RAPL\RAPL\Mapping;

class Route
{
    /**
     * @var string
     */
    private $pattern = '';

    /**
     * @var array
     */
    private $envelopes = array();

    /**
     * @param string $pattern   The pattern used to build a URI
     * @param array  $envelopes Envelopes in which the result data is wrapped
     */
    public function __construct($pattern, $envelopes = array())
    {
        $this->pattern   = $pattern;
        $this->envelopes = $envelopes;
    }

    /**
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * @return array
     */
    public function getEnvelopes()
    {
        return $this->envelopes;
    }
}
