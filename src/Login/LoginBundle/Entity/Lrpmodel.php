<?php

namespace Login\LoginBundle\Entity;

/**
 * Lrpmodel
 */
class Lrpmodel
{
    /**
     * @var integer
     */
    private $lrpRewardPercentage;

    /**
     * @var integer
     */
    private $minPercentage;

    /**
     * @var integer
     */
    private $maxPercentage;

    /**
     * @var integer
     */
    private $year;

    /**
     * @var integer
     */
    private $lrpId;


    /**
     * Set lrpRewardPercentage
     *
     * @param integer $lrpRewardPercentage
     *
     * @return Lrpmodel
     */
    public function setLrpRewardPercentage($lrpRewardPercentage)
    {
        $this->lrpRewardPercentage = $lrpRewardPercentage;

        return $this;
    }

    /**
     * Get lrpRewardPercentage
     *
     * @return integer
     */
    public function getLrpRewardPercentage()
    {
        return $this->lrpRewardPercentage;
    }

    /**
     * Set minPercentage
     *
     * @param integer $minPercentage
     *
     * @return Lrpmodel
     */
    public function setMinPercentage($minPercentage)
    {
        $this->minPercentage = $minPercentage;

        return $this;
    }

    /**
     * Get minPercentage
     *
     * @return integer
     */
    public function getMinPercentage()
    {
        return $this->minPercentage;
    }

    /**
     * Set maxPercentage
     *
     * @param integer $maxPercentage
     *
     * @return Lrpmodel
     */
    public function setMaxPercentage($maxPercentage)
    {
        $this->maxPercentage = $maxPercentage;

        return $this;
    }

    /**
     * Get maxPercentage
     *
     * @return integer
     */
    public function getMaxPercentage()
    {
        return $this->maxPercentage;
    }

    /**
     * Set year
     *
     * @param integer $year
     *
     * @return Lrpmodel
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Get year
     *
     * @return integer
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Get lrpId
     *
     * @return integer
     */
    public function getLrpId()
    {
        return $this->lrpId;
    }
}
