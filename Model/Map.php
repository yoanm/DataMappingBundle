<?php
namespace Yoanm\DataMappingBundle\Model;


class Map
{
    /**
     * @var array
     */
    private $map = array();
    /**
     * @var int
     */
    private $column = null;
    /**
     * @var int
     */
    private $row = null;
    
    /**** GET/SET content helpers for current slot ***/

    /**
     * @abstract set current slot content or a slot object
     *
     * @throws \BadMethodCallException if slot position is not valid
     * @throws \InvalidArgumentException is $content is not a string, a numeric or Slot object
     *
     * @param string|numeric|Slot $content
     *
     * @return Map
     */
    public function set($content)
    {

        $this->exist(true);

        $isSlotObj = (is_object($content) && content instanceof Slot);

        if(!is_numeric($content) && !is_string($content) && !$isSlotObj)
        {
            throw new \InvalidArgumentException(sprintf('$content must be a string, a numeric or a Slot object, \'%s\' given', gettype($content)));
        }

        if (!$isSlotObj) {
            $content = new Slot($content);
        }
        
        $this->map[$this->getRow()][$this->getColumn()] = $content;

        return $this;
    }


    

    /**
     * @abstract unset current slot content
     *
     * @throws \BadMethodCallException if slot position is not valid
     *
     * @return Map
     */
    public function reset()
    {

        $this->exist(true);

        unset($this->map[$this->getRow()][$this->getColumn()]);

        return $this;
    }

    /**
     * @throws \BadMethodCallException if row or column are not defined
     *
     * @param bool $throw if true and slot position is not define, an exception will be throw
     *
     * @return Map
     */
    public function exist($throw=false)
    {
        if ( $throw === false) {
            try {
                $this->exist(true);
                return true;

            } catch(\BadMethodCallException $e) {
                return false;
            }
        }
        $this
            ->checkRowDefined()
            ->checkColumnDefined();

        return true;
    }

    /**
     *
     * @throws \BadMethodCallException if slot position is not valid
     *
     * @return bool
     */
    public function isDefined()
    {
        $this->exist(true);

        $column = $this->getColumn();
        $row    = $this->getRow();

        return (array_key_exists($row, $this->map) && array_key_exists($column, $this->map[$row]));
    }

    /**
     * @abstract return the current Slot
     *
     * @throws \BadMethodCallException if slot position is not valid
     *
     * @return Slot
     */
    public function get()
    {
        $this->exist(true);

        if(!$this->isDefined()) {
            return false;
        }

        return $this->map[$this->getRow()][$this->getColumn()];
    }



    /**** slot/row management helpers ***/

    /**
     * @abstract helper function set position on next slot
     *
     * @param null|string|numeric|slot $content content for the slot if defined
     *
     * @throws \InvalidArgumentException is $content is not null and not a string, a numeric or Slot object
     *
     * @return Map
     */
    public function slot($content=null)
    {
        $this->checkRowDefined();
        if ($this->column !== null) {
            $this->goColumn('+1');
        } else {
            $this->setColumn(0);
        }
        if (null !== $content) {
            $this->set($content);
        }
        return $this;

    }

    /**
     * @abstract helper function to go to next row, first column.
     *
     * @return Map
     */
    public function row()
    {
        if ($this->row !== null) {
            $this->goRow('+1');
        } else {
            $this->setRow(0);
        }
        $this->column = null;

        return $this;
    }

    /**** navigation helpers ****/

    /**
     * @abstract helper function to navigate on the map
     *
     * @throws \InvalidArgumentException if column or row are not good
     *
     * @param int|string        $column the column number (from 0) or a string starting with + or - sign followed by an integer
     * @param null|int|string   $row the row number (from 0) or a string starting with + or - sign followed by an integer
     *
     * @return Map
     */
    public function go($column, $row=null)
    {
        $this->goColumn($column);

        if (null !== $row) {
            $this->goRow($row);
        }
        return $this;

    }

    /**
     * @abstract helper to navigate on the row columns
     *
     * @throws \InvalidArgumentException if column is not good
     *
     * @param null|int|string $column the column number (from 0) or a string starting with + or - sign followed by an integer. default is +1
     *
     * @return Map
     */
    public function goColumn($column='+1')
    {
        $exception = false;
        if (is_string($column)) {
            $sign = $column[0];
            if ($sign === '+' || $sign === '-') {
                $number = substr($column, 1);
                if ((string)((int)$number) !== $number) {
                    $exception = true;
                } else {
                    $column = $this->getColumn() + (int)$column;
                }
            } else {
                $exception = true;
            }
        } elseif (!is_int($column) && (string)((int)$column) !== $column) {
            $exception = true;
        }
        if($exception === true) {
            throw new \InvalidArgumentException(sprintf('column must be an integer or a string starting with + or - sign followed by an integer, \'%s\' given', $column));
        }

        return $this->setColumn($column);
    }

    /**
     * @abstract helper to navigate on the row columns
     *
     * @throws \InvalidArgumentException if  row is not good
     *
     * @param null|int|string   $row the row number (from 0) or a string starting with + or - sign followed by an integer. default is +1
     *
     * @return Map
     */
    public function goRow($row='+1')
    {
        $exception = false;
        if (is_string($row)) {
            $sign = $row[0];
            if ($sign === '+' || $sign === '-') {
                $number = substr($row, 1);
                if ((string)((int)$number) !== $number) {
                    $exception = true;
                } else {
                    $row = $this->getRow() + (int) $row;
                }
            } else {
                $exception = true;
            }
        } elseif (!is_int($row) && (string)((int)$row) !== $row) {
            $exception = true;
        }
        if($exception === true) {
            throw new \InvalidArgumentException(sprintf('row must be an integer or a string starting with + or - sign followed by an integer, \'%s\' given', $row));
        }

        return $this->setRow($row);
    }

    /**
     * @return int
     */
    public function getRow()
    {
        return $this->row;
    }

    /**
     * @return int
     */
    public function getColumn()
    {
        return $this->column;
    }


    /**** debug ****/

    public function debug()
    {
        return $this->map;
    }

    /**** private ****/

    /**
     * @param int $row
     *
     * @return Map
     */
    private function setRow($row)
    {
        $this->row = abs((int)$row);
        return $this;

    }

    /**
     * @param int $column
     *
     * @return Map
     */
    private function setColumn($column)
    {
        $this->column = abs((int)$column);
        return $this;

    }



    /**
     * @throws \BadMethodCallException if row is not defined
     *
     * @return Map
     */
    private function checkRowDefined()
    {
        if ($this->row === null) {
            throw new \BadMethodCallException('no row set');
        }

        return $this;
    }

    /**
     * @throws \BadMethodCallException if column is not defined
     *
     * @return Map
     */
    private function checkColumnDefined()
    {
        if ($this->column === null) {
            throw new \BadMethodCallException('no column set');
        }

        return $this;
    }
}
