<?php

namespace SapeRt\Api\Param;

class Page
{
    const PAGE_NUM  = 'page_num';
    const PAGE_SIZE = 'page_size';
    protected $page_num;
    protected $page_size;
    protected $order_by;

    public function __construct($page_num, $page_size, $order_by = null)
    {
        $this->page_num  = $page_num;
        $this->page_size = $page_size;
        $this->order_by  = $order_by;
    }

    public function toArray(): array
    {
        return array(
            self::PAGE_NUM  => $this->page_num,
            self::PAGE_SIZE => $this->page_size,
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
    public function setOrderBy($order_by): self
    {
        $this->order_by = $order_by;

        return $this;
    }
}
