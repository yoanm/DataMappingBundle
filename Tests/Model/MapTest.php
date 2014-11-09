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
            foreach ($rowData as $slotData) {
                $this->map->add($slotData);
            }
            $this->map->nextRow();
        }

        $this->map
            ->setColumn(0)
            ->setRow(0);

        foreach ($data as $rowData) {
            foreach ($rowData as $slotData) {
                $this->assertSame($this->map->get(), $slotData);
                $this->map->nextColumn();
            }
            $this->map->nextRow();
        }
    }

    /**
     * @dataProvider getBaseData
     *
     * @param mixed[] $data
     */
    public function testSetPosition($data)
    {
        foreach ($data as $row=>$rowData) {
            $this->map->setRow($row);
            foreach ($rowData as $column=>$slotData) {
                $this->map
                    ->setColumn($column)
                    ->set($slotData);
            }
        }

        foreach ($data as $row=>$rowData) {
            $this->map->setRow($row);
            foreach ($rowData as $column=>$slotData) {
                $this->map->setColumn($column);
                $this->assertSame($this->map->get(), $slotData);
            }
        }
    }

    /**
     * @abstract test to use add/next(row|column) then set|get and then add/next(row|column) (==> test basic hack for the first cell of the row)
     */
    public function testSetAndNext()
    {
        $firstRow = array(
            0 => array(
                0 => 'A',
                1 => 'B',
                2 => 'C',
                3 => 'D',
            )
        );
        $othersRows = array(
            3 => array(
                0 => 'A',
                4 => 'B',
                5 => 'C',
                7 => 'D',
            ),
            5 => array(
                0 => 'A',
                1 => 'B',
                2 => 'C',
                3 => 'D',
            ),
        );
        $expected = $firstRow + $othersRows;
        $expected[6] = $firstRow[0];

        foreach ($firstRow[0] as $slotData) {
            $this->map->add($slotData);
        }

        $this->map->nextRow();
        $this->map->nextRow();
        $this->map->nextRow();
        foreach ($othersRows[3] as $column=>$slotData) {
            $this->map
                ->setColumn($column)
                ->set($slotData);
        }

        $this->map->setRow(5);
        foreach ($othersRows[5] as $column=>$slotData) {
            $this->map
                ->setColumn($column)
                ->set($slotData);
        }


        $this->map->nextRow();

        foreach ($firstRow[0] as $slotData) {
            $this->map->add($slotData);
        }

        $this->assertSame($this->map->debug(), $expected);
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
                    )
                )
            )
        );
    }
}
