<?php
namespace Yoanm\DataMappingBundle\Model;


class Map
{
    private $map = array();
    private $column = 0;
    private $row = 0;

    private $isFirstAddOfRow = true;

    public function set($content)
    {
        $this->map[$this->getRow()][$this->getColumn()] = $content;
        $this->isFirstAddOfRow = false;
    }

    /**
     * @param $content
     *
     * @return Map
     */
    public function add($content)
    {
        if (!$this->isFirstAddOfRow) {
            $this->nextcolumn();
        }
        $this->isFirstAddOfRow = false;
        $this->set($content);

        return $this;
    }

    /**
     * @return string|bool
     */
    public function get()
    {
        if($this->currentSlotIsEmpty()) {
            return false;
        }

        return $this->map[$this->getRow()][$this->getColumn()];
    }

    /**
     * @param int $row
     *
     * @return Map
     */
    public function setRow($row)
    {
        $this->row = abs((int)$row);
        return $this;

    }

    /**
     * @param int$column
     *
     * @return Map
     */
    public function setColumn($column)
    {
        $this->column = abs((int)$column);
        return $this;

    }

    /**
     * @return int
     */
    public function getColumn()
    {
        return (int)$this->column;
    }

    /**
     * @return int
     */
    public function getRow()
    {
        return $this->row;
    }

    /**
     * @return Map
     */
    public function nextColumn()
    {
        $this->column++;

        return $this;
    }

    /**
     * @param bool $keepColumn
     *
     * @return Map
     */
    public function nextRow($keepColumn = false)
    {
        $this->row++;
        if (true !== $keepColumn) {
            $this->column = 0;
            $this->isFirstAddOfRow = true;
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function currentSlotIsEmpty()
    {
        $column = $this->getColumn();
        $row    = $this->getRow();

        return (!array_key_exists($row, $this->map) || !array_key_exists($column, $this->map[$row]));
    }

    public function debug()
    {
        return $this->map;
    }
}
