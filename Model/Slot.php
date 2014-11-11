<?php
/**
 * Created by PhpStorm.
 * User: yoanm
 * Date: 11/11/14
 * Time: 12:43
 */

namespace Yoanm\DataMappingBundle\Model;


class Slot {
    /**
     * @var string
     */
    private $content;

    public function __construct($content=null)
    {
        if (null !== $content) {
            $this->content = $content;
        }
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param $content
     *
     * @return Slot
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }
}
