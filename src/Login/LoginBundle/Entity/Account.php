<?php

namespace Login\LoginBundle\Entity;

/**
 * Account
 */
class Account
{
    /**
     * @var string
     */
    private $tmId;

    /**
     * @var string
     */
    private $accountBalance;

    /**
     * @var integer
     */
    private $accountId;


    /**
     * Set tmId
     *
     * @param string $tmId
     *
     * @return Account
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
     * Set accountBalance
     *
     * @param string $accountBalance
     *
     * @return Account
     */
    public function setAccountBalance($accountBalance)
    {
        $this->accountBalance = $accountBalance;

        return $this;
    }

    /**
     * Get accountBalance
     *
     * @return string
     */
    public function getAccountBalance()
    {
        return $this->accountBalance;
    }

    /**
     * Get accountId
     *
     * @return integer
     */
    public function getAccountId()
    {
        return $this->accountId;
    }
}
