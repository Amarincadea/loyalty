<?php

namespace Login\LoginBundle\Entity;

/**
 * Loyalty
 */
class Loyalty
{
    /**
     * @var string
     */
    private $tmId;

    /**
     * @var string
     */
    private $amount;

    /**
     * @var integer
     */
    private $loyaltyYear;

    /**
     * @var string
     */
    private $loyaltyDate;

    /**
     * @var integer
     */
    private $id;


    /**
     * Set tmId
     *
     * @param string $tmId
     *
     * @return Loyalty
     */
    public function setTmId($tmId)
    {
        $this->tmId = $tmId;

        return $this;
    }

    /**
     * Get tmId
     *
     * @return string
     */
    public function getTmId()
    {
        return $this->tmId;
    }

    /**
     * Set amount
     *
     * @param string $amount
     *
     * @return Loyalty
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return string
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set loyaltyYear
     *
     * @param integer $loyaltyYear
     *
     * @return Loyalty
     */
    public function setLoyaltyYear($loyaltyYear)
    {
        $this->loyaltyYear = $loyaltyYear;

        return $this;
    }

    /**
     * Get loyaltyYear
     *
     * @return integer
     */
    public function getLoyaltyYear()
    {
        return $this->loyaltyYear;
    }

    /**
     * Set loyaltyDate
     *
     * @param string $loyaltyDate
     *
     * @return Loyalty
     */
    public function setLoyaltyDate($loyaltyDate)
    {
        $this->loyaltyDate = $loyaltyDate;

        return $this;
    }

    /**
     * Get loyaltyDate
     *
     * @return string
     */
    public function getLoyaltyDate()
    {
        return $this->loyaltyDate;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
}
