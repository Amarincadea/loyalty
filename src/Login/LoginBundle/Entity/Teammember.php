<?php

namespace Login\LoginBundle\Entity;

/**
 * Teammember
 */
class Teammember
{
    /**
     * @var string
     */
    private $itNo;

    /**
     * @var string
     */
    private $tmName;

    /**
     * @var string
     */
    private $tmDoj;

    /**
     * @var string
     */
    private $tmEmail;

    /**
     * @var string
     */
    private $tmDesignation;

    /**
     * @var string
     */
    private $tmDepartment;

    /**
     * @var integer
     */
    private $tmId;


    /**
     * Set itNo
     *
     * @param string $itNo
     *
     * @return Teammember
     */
    public function setItNo($itNo)
    {
        $this->itNo = $itNo;

        return $this;
    }

    /**
     * Get itNo
     *
     * @return string
     */
    public function getItNo()
    {
        return $this->itNo;
    }

    /**
     * Set tmName
     *
     * @param string $tmName
     *
     * @return Teammember
     */
    public function setTmName($tmName)
    {
        $this->tmName = $tmName;

        return $this;
    }

    /**
     * Get tmName
     *
     * @return string
     */
    public function getTmName()
    {
        return $this->tmName;
    }

    /**
     * Set tmDoj
     *
     * @param string $tmDoj
     *
     * @return Teammember
     */
    public function setTmDoj($tmDoj)
    {
        $this->tmDoj = $tmDoj;

        return $this;
    }

    /**
     * Get tmDoj
     *
     * @return string
     */
    public function getTmDoj()
    {
        return $this->tmDoj;
    }

    /**
     * Set tmEmail
     *
     * @param string $tmEmail
     *
     * @return Teammember
     */
    public function setTmEmail($tmEmail)
    {
        $this->tmEmail = $tmEmail;

        return $this;
    }

    /**
     * Get tmEmail
     *
     * @return string
     */
    public function getTmEmail()
    {
        return $this->tmEmail;
    }

    /**
     * Set tmDesignation
     *
     * @param string $tmDesignation
     *
     * @return Teammember
     */
    public function setTmDesignation($tmDesignation)
    {
        $this->tmDesignation = $tmDesignation;

        return $this;
    }

    /**
     * Get tmDesignation
     *
     * @return string
     */
    public function getTmDesignation()
    {
        return $this->tmDesignation;
    }

    /**
     * Set tmDepartment
     *
     * @param string $tmDepartment
     *
     * @return Teammember
     */
    public function setTmDepartment($tmDepartment)
    {
        $this->tmDepartment = $tmDepartment;

        return $this;
    }

    /**
     * Get tmDepartment
     *
     * @return string
     */
    public function getTmDepartment()
    {
        return $this->tmDepartment;
    }

    /**
     * Get tmId
     *
     * @return integer
     */
    public function getTmId()
    {
        return $this->tmId;
    }
}
