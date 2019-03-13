<?php

namespace SapeRt\Api\Param;

class Page
{
    protected $page_num;
    protected $page_size;
    protected $order_by;

    public function __construct($page_num, $page_size, $order_by = null)
    {
        $this->page_num  = $page_num;
        $this->page_size = $page_size;
        $this->order_by  = $order_by;
    }

    public function toArray()
    {
        return array(
            'page_num'  => $this->page_num,
            'page_size' => $this->page_size,
        );
    }

    /**
     * @return mixed
     */
    public function getOrderBy()
    {
        return $this->order_by;
    }

    /**
     * @param mixed $order_by
     *
     * @return $this
     */
    public function setOrderBy($order_by)
    {
        $this->order_by = $order_by;

        return $this;
    }
}
