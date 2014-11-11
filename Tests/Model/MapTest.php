<?php
namespace Yoanm\DataMappingBundle\Tests\Model;

use Yoanm\DataMappingBundle\Model\Map;
use Yoanm\DataMappingBundle\Tests\ProphecyTestCase;

class MapTest extends ProphecyTestCase
{
    /**
     * @var Map
     */
    protected $map;

    protected function setup()
    {
        parent::setup();

        $this->map = new Map();
    }

    /**
     * @dataProvider getBaseData
     *
     * @param mixed[] $data
     */
    public function testSimpleAdd($data)
    {
        foreach ($data as $rowData) {
            $this->map->row();
            foreach ($rowData as $slotData) {
                $this->map
                    ->slot($slotData);
            }
        }

        $this->map->go(0, 0);

        foreach ($data as $rowData) {
            foreach ($rowData as $slotData) {
                $this->assertSame($this->map->get(), $slotData);
                $this->map->goColumn();
            }
            $this->map->goRow();
            $this->map->goColumn(0);
        }
    }


    public function getBaseData()
    {
        return array(
            array(
                array(
                    0 => array(
                        0 => "A",
                        4 => "B",
                        5 => "C",
                        10 => "D",
                    ),
                    2 => array(
                        2 => "E",
                        3 => "F",
                        4 => "G",
                        6 => "H",
                    ),
                    3 => array(
                        2 => 2,
                        3 => 5.3,
                        4 => -2,
                        6 => 0,
                    )
                )
            )
        );
    }
}
