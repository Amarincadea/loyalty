<?php

namespace Login\LoginBundle\Entity;

/**
 * Department
 */
class Department
{
    /**
     * @var string
     */
    private $departmentName;

    /**
     * @var integer
     */
    private $departmentId;


    /**
     * Set departmentName
     *
     * @param string $departmentName
     *
     * @return Department
     */
    public function setDepartmentName($departmentName)
    {
        $this->departmentName = $departmentName;

        return $this;
    }

    /**
     * Get departmentName
     *
     * @return string
     */
    public function getDepartmentName()
    {
        return $this->departmentName;
    }

    /**
     * Get departmentId
     *
     * @return integer
     */
    public function getDepartmentId()
    {
        return $this->departmentId;
    }
}
