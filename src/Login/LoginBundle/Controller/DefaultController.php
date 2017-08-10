<?php

namespace Login\LoginBundle\Controller;

use Login\LoginBundle\Entity\Account;
use Login\LoginBundle\Entity\Department;
use Login\LoginBundle\Entity\Designation;
use Login\LoginBundle\Entity\Loyalty;
use Login\LoginBundle\Entity\Lrpmodel;
use Login\LoginBundle\Entity\Optin;
use Login\LoginBundle\Entity\Teammember;
use Login\LoginBundle\Entity\Transaction;
use Login\LoginBundle\Entity\User;
use Login\LoginBundle\Entity\Withdraw;
use Login\LoginBundle\LoginLoginBundle;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use function Sodium\add;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Login\LoginBundle\Model\Login;



class DefaultController extends Controller
{
    /**
     * @Route("/", name="login")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('LoginLoginBundle:User');
        $session = new Session();
        $session-> get('login');
        if($request->getMethod()=='POST')
        {
            $session->clear();
            $username=$request->get('email');
            $password=$request->get('pwd');
            $remember=$request->get('remember');
            $role=$request->get('role');
            $user =$repository->findOneBy(
                array(
                    'userName'=>$username,
                    'userPassword'=>$password,
                    'userRole'=>$role
                ));
            $role_all =$repository->findBy(
                array(
                    'userName'=>$username,
                ));
            if ($user)
            {
                if($remember=='remember-me')
                {
                    $login = new Login();
                    $login->setUsername($username);
                    $login->setPassword($password);
                    $login->setRole($role);
                    $session->set('login',$login);
                }
                $userrole=$user->getUserRole();
                switch($userrole)

                {
                    case 'Admin':
                        return $this->render('LoginLoginBundle:Default:admin.html.twig',
                            array(
                                'name' => $user->getUserName(),
                                'role' => $user->getUserRole(),
                                'userRole'=>$role,
                                'all_roles' => $role_all
                            ));
                        break;

                    case 'Super Admin':
                        $id=$this->getITID();

                        $em = $this->getDoctrine()->getManager();
                        $repository = $em->getRepository('LoginLoginBundle:Teammember');
                        $tm = $repository->findOneBy(array('itNo' => $id));

                        $department = $tm->getTmDepartment();
                        $name = $tm->getTmName();
                        $optin_details = $this->getDoctrine()
                            ->getRepository('LoginLoginBundle:Optin')
                            ->findBy(array('dhStatus'=>'Pending','tmDesignation'=> array('Head','Director')));

                        $optin_pending_count = intval(count($optin_details));

                        $optin_approved = $this->getDoctrine()
                            ->getRepository('LoginLoginBundle:Optin')
                            ->findBy(array('dhStatus'=>'Approved','tmDesignation'=> array('Head','Director')));
                        $optin_approved_count = intval(count($optin_approved));


                        $optin_rejected = $this->getDoctrine()
                            ->getRepository('LoginLoginBundle:Optin')
                            ->findBy(array('dhStatus'=>'Rejected','tmDesignation'=> array('Head','Director')));
                        $optin_rejected_count = intval(count($optin_rejected));

                        $payout_pending = $this->getDoctrine()
                            ->getRepository('LoginLoginBundle:Withdraw')
                            ->findBy(array('dhStatus'=>'Pending','tmDesignation'=> array('Head','Director')));
                        $pay_pending = intval(count($payout_pending));

                        $payout_approved = $this->getDoctrine()
                            ->getRepository('LoginLoginBundle:Withdraw')
                            ->findBy(array('dhStatus'=>'Approved','tmDesignation'=> array('Head','Director')));
                        $pay_approved = intval(count($payout_approved));

                        $payout_rejected = $this->getDoctrine()
                            ->getRepository('LoginLoginBundle:Withdraw')
                            ->findBy(array('dhStatus'=>'Rejected','tmDesignation'=> array('Head','Director')));
                        $pay_rejected = intval(count($payout_rejected));
                        return $this->render('LoginLoginBundle:Default:super_admin.html.twig',
                            array(
                                'name' => $user->getUserName(),
                                'role' => $user->getUserRole(),
                                'tm_name' => $name,
                                'userRole'=>$role,
                                'all_roles' => $role_all,
                                'opt_pending' => $optin_pending_count,
                                'opt_approved' => $optin_approved_count,
                                'opt_rejected' => $optin_rejected_count,
                                'pay_pending' => $pay_pending,
                                'pay_approved' => $pay_approved,
                                'pay_rejected' => $pay_rejected
                            ));
                        break;

                    case 'Finance Manager':
                        $payout_pending = $this->getDoctrine()
                            ->getRepository('LoginLoginBundle:Withdraw')
                            ->findBy(array('dhStatus'=>'Approved','hrStatus'=>'Approved','fmStatus'=>'Pending'));
                        $pay_pending = intval(count($payout_pending));

                        $payout_approved = $this->getDoctrine()
                            ->getRepository('LoginLoginBundle:Withdraw')
                            ->findBy(array('dhStatus'=>'Approved','hrStatus'=>'Approved','fmStatus'=>'Approved'));
                        $pay_approved = intval(count($payout_approved));

                        $payout_onhold = $this->getDoctrine()
                            ->getRepository('LoginLoginBundle:Withdraw')
                            ->findBy(array('dhStatus'=>'Approved','hrStatus'=>'Approved','fmStatus'=>'Onhold'));
                        $pay_onhold = intval(count($payout_onhold));

                        $payout_paid = $this->getDoctrine()
                            ->getRepository('LoginLoginBundle:Withdraw')
                            ->findBy(array('dhStatus'=>'Approved','hrStatus'=>'Approved','fmStatus'=>'Paid'));
                        $pay_paid = intval(count($payout_paid));
                        return $this->render('LoginLoginBundle:Default:finance_manager.html.twig',
                            array(
                                'name' => $user->getUserName(),
                                'role' => $user->getUserRole(),
                                'all_roles' => $role_all,
                                'userRole'=>$role,
                                'pay_pending' => $pay_pending,
                                'pay_approved' => $pay_approved,
                                'pay_onhold' => $pay_onhold,
                                'pay_paid' => $pay_paid
                            ));
                        break;

                    case 'HR Manager':
                        $optin_details = $this->getDoctrine()
                            ->getRepository('LoginLoginBundle:Optin')
                            ->findBy(array('dhStatus'=>'Approved','hrStatus'=>'Pending'));
                        $optin_pending_count = intval(count($optin_details));


                        $optin_approved = $this->getDoctrine()
                            ->getRepository('LoginLoginBundle:Optin')
                            ->findBy(array('dhStatus'=>'Approved','hrStatus'=>'Approved'));
                        $optin_approved_count = intval(count($optin_approved));


                        $optin_rejected = $this->getDoctrine()
                            ->getRepository('LoginLoginBundle:Optin')
                            ->findBy(array('dhStatus'=>'Approved','hrStatus'=>'Rejected'));
                        $optin_rejected_count = intval(count($optin_rejected));

                        $payout_pending = $this->getDoctrine()
                            ->getRepository('LoginLoginBundle:Withdraw')
                            ->findBy(array('dhStatus'=>'Approved','hrStatus'=>'Pending'));
                        $pay_pending = intval(count($payout_pending));

                        $payout_approved = $this->getDoctrine()
                            ->getRepository('LoginLoginBundle:Withdraw')
                            ->findBy(array('dhStatus'=>'Approved','hrStatus'=>'Approved'));
                        $pay_approved = intval(count($payout_approved));

                        $payout_rejected = $this->getDoctrine()
                            ->getRepository('LoginLoginBundle:Withdraw')
                            ->findBy(array('dhStatus'=>'Approved','hrStatus'=>'Rejected'));
                        $pay_rejected = intval(count($payout_rejected));
                        return $this->render('LoginLoginBundle:Default:hr_manager.html.twig',
                            array(
                                'name' => $user->getUserName(),
                                'role' => $user->getUserRole(),
                                'userRole'=>$role,
                                'all_roles' => $role_all,
                                'opt_pending' => $optin_pending_count,
                                'opt_approved' => $optin_approved_count,
                                'opt_rejected' => $optin_rejected_count,
                                'pay_pending' => $pay_pending,
                                'pay_approved' => $pay_approved,
                                'pay_rejected' => $pay_rejected
                            ));
                        break;

                    case 'Department Head':
                        $id=$this->getITID();

                        $em = $this->getDoctrine()->getManager();
                        $repository = $em->getRepository('LoginLoginBundle:Teammember');
                        $tm = $repository->findOneBy(array('itNo' => $id));

                        $department = $tm->getTmDepartment();


                        $criteria = new \Doctrine\Common\Collections\Criteria();
                        $criteria->where($criteria->expr()->eq('dhStatus', 'Pending'))
                            ->andwhere($criteria->expr()->eq('tmDepartment', $department))
                            ->andwhere($criteria->expr()->notIn('tmDesignation', array('Head','Director')));
                        $optin_details = $this->getDoctrine()
                            ->getRepository('LoginLoginBundle:Optin')->matching($criteria);
                        $optin_pending_count = intval(count($optin_details));

                        $criteria = new \Doctrine\Common\Collections\Criteria();
                        $criteria->where($criteria->expr()->eq('dhStatus', 'Approved'))
                            ->andwhere($criteria->expr()->eq('tmDepartment', $department))
                            ->andwhere($criteria->expr()->notIn('tmDesignation', array('Head','Director')));
                        $optin_approved = $this->getDoctrine()
                            ->getRepository('LoginLoginBundle:Optin')->matching($criteria);
                        $optin_approved_count = intval(count($optin_approved));


                        $criteria = new \Doctrine\Common\Collections\Criteria();
                        $criteria->where($criteria->expr()->eq('dhStatus', 'Rejected'))
                            ->andwhere($criteria->expr()->eq('tmDepartment', $department))
                            ->andwhere($criteria->expr()->notIn('tmDesignation', array('Head','Director')));
                        $optin_rejected = $this->getDoctrine()
                            ->getRepository('LoginLoginBundle:Optin')->matching($criteria);
                        $optin_rejected_count = intval(count($optin_rejected));


                        $criteria = new \Doctrine\Common\Collections\Criteria();
                        $criteria->where($criteria->expr()->eq('dhStatus', 'Pending'))
                            ->andwhere($criteria->expr()->eq('tmDepartment', $department))
                            ->andwhere($criteria->expr()->notIn('tmDesignation', array('Head','Director')));
                        $payout_pending = $this->getDoctrine()
                            ->getRepository('LoginLoginBundle:Withdraw')->matching($criteria);
                        $pay_pending = intval(count($payout_pending));

                        $criteria = new \Doctrine\Common\Collections\Criteria();
                        $criteria->where($criteria->expr()->eq('dhStatus', 'Approved'))
                            ->andwhere($criteria->expr()->eq('tmDepartment', $department))
                            ->andwhere($criteria->expr()->notIn('tmDesignation', array('Head','Director')));
                        $payout_approved = $this->getDoctrine()
                            ->getRepository('LoginLoginBundle:Withdraw')->matching($criteria);
                        $pay_approved = intval(count($payout_approved));

                        $criteria = new \Doctrine\Common\Collections\Criteria();
                        $criteria->where($criteria->expr()->eq('dhStatus', 'Rejected'))
                            ->andwhere($criteria->expr()->eq('tmDepartment', $department))
                            ->andwhere($criteria->expr()->notIn('tmDesignation', array('Head','Director')));
                        $payout_rejected = $this->getDoctrine()
                            ->getRepository('LoginLoginBundle:Withdraw')->matching($criteria);
                        $pay_rejected = intval(count($payout_rejected));


                        return $this->render('LoginLoginBundle:Default:department_head.html.twig',
                            array(
                                'name' => $user->getUserName(),
                                'role' => $user->getUserRole(),
                                'userRole'=>$role,
                                'all_roles' => $role_all,
                                'opt_pending' => $optin_pending_count,
                                'opt_approved' => $optin_approved_count,
                                'opt_rejected' => $optin_rejected_count,
                                'pay_pending' => $pay_pending,
                                'pay_approved' => $pay_approved,
                                'pay_rejected' => $pay_rejected
                            ));
                        break;

                    default:
                        $tm_id = $this->getITID();
                        $em = $this->getDoctrine()->getManager();
                        $update = $em->getRepository('LoginLoginBundle:Teammember')->findOneBy(array('itNo'=>$tm_id));
                        /** @var $update Teammember */
                        $get_doj= $update->getTmDoj();

                        $curr_date = date("Y-m-d");
                        $diff = $this->get_dateDifference($curr_date, $get_doj, '%a');


                        if($diff<1460)//before loyalty starts
                        {

                            $date=date_create("$get_doj");
                            date_add($date,date_interval_create_from_date_string("1460 days"));
                            $activation_date = date_format($date,"Y-m-d");
                            $activation_date_formated = date_format($date,"d-m-Y");

                            $optin_details = $this->getDoctrine()
                                ->getRepository('LoginLoginBundle:Optin')
                                ->findOneBy(array('tmId'=>$tm_id));

                            return $this->render('LoginLoginBundle:Default:before_loyalty.html.twig',
                                array(
                                    'activation_date' => $activation_date,
                                    'activation_date_formatted'=>$activation_date_formated,
                                    'optin_data'=>$optin_details,
                                    'userRole'=>$role,
                                    'name' => $user->getUserName(),
                                    'all_roles' => $role_all
                                ));
                        }
                        if(($diff>=1460) && ($diff<1825))//Running fourth year
                        {
                            if($diff>=1460)
                            {
                                $em = $this->getDoctrine()->getManager();
                                $update = $em->getRepository('LoginLoginBundle:Loyalty')->findOneBy(array('tmId'=>$tm_id,'loyaltyYear'=>4));
                                $num = sizeof($update);
                                if($num==0)//if not
                                {
                                    $em = $this->getDoctrine()->getManager();
                                    $update = $em->getRepository('LoginLoginBundle:Teammember')->findOneBy(array('itNo' => $tm_id));
                                    /** @var $update Teammember */
                                    $tm_designation = $update->getTmDesignation();
                                    $em->flush();

                                    $repository = $em->getRepository('LoginLoginBundle:Designation');
                                    $account_balance = $repository->findOneBy(array('designation' => $tm_designation));
                                    $base_amount = $account_balance->getBase();

                                    //updaing main balance
                                    $em = $this->getDoctrine()->getManager();
                                    $update = $em->getRepository('LoginLoginBundle:Account')->findOneBy(array('tmId' => $tm_id));
                                    /** @var $update Account */
                                    $update->setAccountBalance($base_amount);
                                    $em->flush();


                                    //adding new entry in loyalty for 5th year
                                    $loyalty = new Loyalty();
                                    $loyalty->setTmId($tm_id);
                                    $loyalty->setLoyaltyYear('4');
                                    $loyalty->setAmount($base_amount);
                                    $OI_time = date('Y-m-d H:i:s');
                                    $loyalty->setLoyaltyDate($OI_time);

                                    //adding new entry in to transactions
                                    $transaction = new Transaction();
                                    $transaction->setTmId($tm_id);
                                    $transaction->setTrType('Deposited');
                                    $transaction->setTrAmount($base_amount);
                                    $transaction->setTrDate($OI_time);


                                    $em = $this->getDoctrine()->getManager();
                                    $em->persist($loyalty);
                                    $em->persist($transaction);
                                    $em->flush();
                                }
                            }
                            $current_balance = $this->getDoctrine()
                                ->getRepository('LoginLoginBundle:Account')
                                ->findOneBy(array('tmId'=>$tm_id));
                            /** @var $current_balance Account */
                            $format_balance= number_format($current_balance->getAccountBalance());
                            $bal = intval($current_balance->getAccountBalance());
                            $loyalty = $bal*25/100;
                            $loyalty_add = number_format($loyalty);
                            $new_bal = $bal+$loyalty;
                            $new_bal = number_format($new_bal);

                            $optin_details = $this->getDoctrine()
                                ->getRepository('LoginLoginBundle:Optin')
                                ->findOneBy(array('tmId'=>$tm_id));

                            $date=date_create("$get_doj");
                            date_add($date,date_interval_create_from_date_string("1825 days"));
                            $loyaty_date = date_format($date,"Y-m-d");
                            $loyalty_date_formated = date_format($date,"d-m-Y");

                            return $this->render('LoginLoginBundle:Default:fourth_year.html.twig',
                                array(
                                    'loyalty'=>$loyalty_add,
                                    'full_bal'=>$new_bal,
                                    'optin_data'=>$optin_details,
                                    'balance' => $format_balance,
                                    'loyalty_date'=>$loyaty_date,
                                    'loyalty_date_formatted'=>$loyalty_date_formated,
                                    'userRole'=>$role,
                                    'name' => $user->getUserName(),
                                    'all_roles' => $role_all
                                ));
                        }
                        if(($diff>=1825) && ($diff<2190))//Running fifth year
                        {

                            if ($diff>=1825)//Running 5th year and last month
                            {
                                //checking for 5th year entry
                                $em = $this->getDoctrine()->getManager();
                                $update = $em->getRepository('LoginLoginBundle:Loyalty')->findOneBy(array('tmId'=>$tm_id,'loyaltyYear'=>5));
                                $num = sizeof($update);
                                if($num==0)//if not
                                {
                                    $em = $this->getDoctrine()->getManager();
                                    $update = $em->getRepository('LoginLoginBundle:Teammember')->findOneBy(array('itNo'=>$tm_id));
                                    /** @var $update Teammember */
                                    $tm_designation = $update->getTmDesignation();
                                    $em->flush();


                                    //fetching balance
                                    $em = $this->getDoctrine()->getManager();
                                    $update = $em->getRepository('LoginLoginBundle:Account')->findOneBy(array('tmId'=>$tm_id));
                                    /** @var $update Account */

                                    $bal = intval($update->getAccountBalance());
                                    $loyalty_add = $bal*25/100;
                                    $new_bal = $bal+$loyalty_add;

                                    //adding new entry in loyalty for 5th year
                                    $loyalty = new Loyalty();
                                    $loyalty->setTmId($tm_id);
                                    $loyalty->setLoyaltyYear('5');
                                    $loyalty->setAmount($loyalty_add);
                                    $OI_time=date('Y-m-d H:i:s');
                                    $loyalty->setLoyaltyDate($OI_time);

                                    //adding new entry in to transactions
                                    $transaction = new Transaction();
                                    $transaction->setTmId($tm_id);
                                    $transaction->setTrType('Deposited');
                                    $transaction->setTrAmount($loyalty_add);
                                    $transaction->setTrDate($OI_time);


                                    $em=$this->getDoctrine()->getManager();
                                    $em->persist($loyalty);
                                    $em->persist($transaction);
                                    $em->flush();

                                    //updaing main balance
                                    $em = $this->getDoctrine()->getManager();
                                    $update = $em->getRepository('LoginLoginBundle:Account')->findOneBy(array('tmId'=>$tm_id));
                                    /** @var $update Account */
                                    $update->setAccountBalance($new_bal);
                                    $em->flush();
                                }
                            }
                            $account_balance = $this->getDoctrine()
                                ->getRepository('LoginLoginBundle:Account')
                                ->findOneBy(array('tmId'=>$tm_id));
                            /** @var $account_balance Account */
                            $format_balance= number_format($account_balance->getAccountBalance());
                            $bal = intval($account_balance->getAccountBalance());
                            $loyalty_6th = $bal*50/100;
                            $loyalty_add_6th = number_format($loyalty_6th);
                            $new_bal_6th = $bal+$loyalty_6th;
                            $new_bal_6th = number_format($new_bal_6th);

                            $optin_details = $this->getDoctrine()
                                ->getRepository('LoginLoginBundle:Optin')
                                ->findOneBy(array('tmId'=>$tm_id));

                            $date=date_create("$get_doj");
                            date_add($date,date_interval_create_from_date_string("2190 days"));
                            $loyaty_date = date_format($date,"Y-m-d");
                            $loyalty_date_formated = date_format($date,"d-m-Y");

                            $curr_month = date("m");

                            $em=$this->getDoctrine()->getManager();
                            $repository = $em->getRepository('LoginLoginBundle:Withdraw');
                            $new_payout =$repository->findBy(array('tmId'=>$tm_id,'wtYear'=>6));

                            $payout_count = sizeof($new_payout);

                            return $this->render('LoginLoginBundle:Default:fifth_year.html.twig',
                                array(
                                    'payout_count'=>$payout_count,
                                    'month'=>$curr_month,
                                    'diff'=>$diff,
                                    'loyalty'=>$loyalty_add_6th,
                                    'full_bal'=>$new_bal_6th,
                                    'optin_data'=>$optin_details,
                                    'balance' => $format_balance,
                                    'loyalty_date'=>$loyaty_date,
                                    'loyalty_date_formatted'=>$loyalty_date_formated,
                                    'userRole'=>$role,
                                    'name' => $user->getUserName(),
                                    'all_roles' => $role_all
                                ));

                        }
                        if(($diff>=2190) && ($diff<2555))//Running sixth year
                        {
                            if ($diff>=2190)//Running 6th year and last month
                            {
                                //checking for 6th year entry
                                $em = $this->getDoctrine()->getManager();
                                $update = $em->getRepository('LoginLoginBundle:Loyalty')->findOneBy(array('tmId'=>$tm_id,'loyaltyYear'=>6));
                                $num = sizeof($update);
                                if($num==0)//if not
                                {
                                    //fetching balance
                                    $em = $this->getDoctrine()->getManager();
                                    $update = $em->getRepository('LoginLoginBundle:Account')->findOneBy(array('tmId'=>$tm_id));
                                    /** @var $update Account */

                                    $bal = intval($update->getAccountBalance());
                                    $loyalty_add = $bal*50/100;
                                    $new_bal = $bal+$loyalty_add;

                                    //adding new entry in loyalty for 6th year
                                    $loyalty = new Loyalty();
                                    $loyalty->setTmId($tm_id);
                                    $loyalty->setLoyaltyYear('6');
                                    $loyalty->setAmount($loyalty_add);
                                    $OI_time=date('Y-m-d H:i:s');
                                    $loyalty->setLoyaltyDate($OI_time);

                                    //adding new entry in to transactions
                                    $transaction = new Transaction();
                                    $transaction->setTmId($tm_id);
                                    $transaction->setTrType('Deposited');
                                    $transaction->setTrAmount($loyalty_add);
                                    $transaction->setTrDate($OI_time);


                                    $em=$this->getDoctrine()->getManager();
                                    $em->persist($loyalty);
                                    $em->persist($transaction);
                                    $em->flush();

                                    //updaing main balance
                                    $em = $this->getDoctrine()->getManager();
                                    $update = $em->getRepository('LoginLoginBundle:Account')->findOneBy(array('tmId'=>$tm_id));
                                    /** @var $update Account */
                                    $update->setAccountBalance($new_bal);
                                    $em->flush();
                                }
                            }


                            $current_balance = $this->getDoctrine()
                                ->getRepository('LoginLoginBundle:Account')
                                ->findOneBy(array('tmId'=>$tm_id));
                            /** @var $current_balance Account */
                            $format_balance= number_format($current_balance->getAccountBalance());
                            $bal = intval($current_balance->getAccountBalance());
                            $loyalty = $bal*60/100;
                            $loyalty_add = number_format($loyalty);
                            $new_bal = $bal+$loyalty;
                            $new_bal = number_format($new_bal);


                            $optin_details = $this->getDoctrine()
                                ->getRepository('LoginLoginBundle:Optin')
                                ->findOneBy(array('tmId'=>$tm_id));

                            $date=date_create("$get_doj");
                            date_add($date,date_interval_create_from_date_string("2555 days"));
                            $loyaty_date = date_format($date,"Y-m-d");
                            $loyalty_date_formated = date_format($date,"d-m-Y");


                            $curr_month = date("m");

                            $em=$this->getDoctrine()->getManager();
                            $repository = $em->getRepository('LoginLoginBundle:Withdraw');
                            $new_payout =$repository->findBy(array('tmId'=>$tm_id,'wtYear'=>7));

                            $payout_count = sizeof($new_payout);


                            return $this->render('LoginLoginBundle:Default:sixth_year.html.twig',
                                array(
                                    'payout_count'=>$payout_count,
                                    'month'=>$curr_month,
                                    'diff'=>$diff,
                                    'loyalty'=>$loyalty_add,
                                    'full_bal'=>$new_bal,
                                    'optin_data'=>$optin_details,
                                    'balance' => $format_balance,
                                    'loyalty_date'=>$loyaty_date,
                                    'loyalty_date_formatted'=>$loyalty_date_formated,
                                    'userRole'=>$role,
                                    'name' => $user->getUserName(),
                                    'all_roles' => $role_all
                                ));


                        }
                        if(($diff>=2555) && ($diff<2920))//Running seventh year
                        {
                            if ($diff>=2555)//Running 7th year and last month
                            {
                                //checking for 7th year entry
                                $em = $this->getDoctrine()->getManager();
                                $update = $em->getRepository('LoginLoginBundle:Loyalty')->findOneBy(array('tmId'=>$tm_id,'loyaltyYear'=>7));
                                $num = sizeof($update);
                                if($num==0)//if not
                                {
                                    //fetching balance
                                    $em = $this->getDoctrine()->getManager();
                                    $update = $em->getRepository('LoginLoginBundle:Account')->findOneBy(array('tmId'=>$tm_id));
                                    /** @var $update Account */

                                    $bal = intval($update->getAccountBalance());
                                    $loyalty_add = $bal*60/100;
                                    $new_bal = $bal+$loyalty_add;

                                    //adding new entry in loyalty for 7th year
                                    $loyalty = new Loyalty();
                                    $loyalty->setTmId($tm_id);
                                    $loyalty->setLoyaltyYear('7');
                                    $loyalty->setAmount($loyalty_add);
                                    $OI_time=date('Y-m-d H:i:s');
                                    $loyalty->setLoyaltyDate($OI_time);

                                    //adding new entry in to transactions
                                    $transaction = new Transaction();
                                    $transaction->setTmId($tm_id);
                                    $transaction->setTrType('Deposited');
                                    $transaction->setTrAmount($loyalty_add);
                                    $transaction->setTrDate($OI_time);


                                    $em=$this->getDoctrine()->getManager();
                                    $em->persist($loyalty);
                                    $em->persist($transaction);
                                    $em->flush();

                                    //updaing main balance
                                    $em = $this->getDoctrine()->getManager();
                                    $update = $em->getRepository('LoginLoginBundle:Account')->findOneBy(array('tmId'=>$tm_id));
                                    /** @var $update Account */
                                    $update->setAccountBalance($new_bal);
                                    $em->flush();
                                }
                            }

                            $current_balance = $this->getDoctrine()
                                ->getRepository('LoginLoginBundle:Account')
                                ->findOneBy(array('tmId'=>$tm_id));
                            /** @var $current_balance Account */
                            $format_balance= number_format($current_balance->getAccountBalance());
                            $bal = intval($current_balance->getAccountBalance());
                            $loyalty = $bal*70/100;
                            $loyalty_add = number_format($loyalty);
                            $new_bal = $bal+$loyalty;
                            $new_bal = number_format($new_bal);


                            $optin_details = $this->getDoctrine()
                                ->getRepository('LoginLoginBundle:Optin')
                                ->findOneBy(array('tmId'=>$tm_id));

                            $date=date_create("$get_doj");
                            date_add($date,date_interval_create_from_date_string("2920 days"));
                            $loyaty_date = date_format($date,"Y-m-d");
                            $loyalty_date_formated = date_format($date,"d-m-Y");

                            $curr_month = date("m");

                            $em=$this->getDoctrine()->getManager();
                            $repository = $em->getRepository('LoginLoginBundle:Withdraw');
                            $new_payout =$repository->findBy(array('tmId'=>$tm_id,'wtYear'=>8));

                            $payout_count = sizeof($new_payout);

                            return $this->render('LoginLoginBundle:Default:seventh_year.html.twig',
                                array(
                                    'payout_count'=>$payout_count,
                                    'month'=>$curr_month,
                                    'diff'=>$diff,
                                    'loyalty'=>$loyalty_add,
                                    'full_bal'=>$new_bal,
                                    'optin_data'=>$optin_details,
                                    'balance' => $format_balance,
                                    'loyalty_date'=>$loyaty_date,
                                    'loyalty_date_formatted'=>$loyalty_date_formated,
                                    'userRole'=>$role,
                                    'name' => $user->getUserName(),
                                    'all_roles' => $role_all
                                ));
                        }
                        if(($diff>=2920) && ($diff<3285))//Running eighth year
                        {

                            if ($diff>=2920)//Running 8th year and last month
                            {
                                //checking for 8th year entry
                                $em = $this->getDoctrine()->getManager();
                                $update = $em->getRepository('LoginLoginBundle:Loyalty')->findOneBy(array('tmId'=>$tm_id,'loyaltyYear'=>8));
                                $num = sizeof($update);
                                if($num==0)//if not
                                {
                                    //fetching balance
                                    $em = $this->getDoctrine()->getManager();
                                    $update = $em->getRepository('LoginLoginBundle:Account')->findOneBy(array('tmId'=>$tm_id));
                                    /** @var $update Account */

                                    $bal = intval($update->getAccountBalance());
                                    $loyalty_add = $bal*70/100;
                                    $new_bal = $bal+$loyalty_add;

                                    //adding new entry in loyalty for 8th year
                                    $loyalty = new Loyalty();
                                    $loyalty->setTmId($tm_id);
                                    $loyalty->setLoyaltyYear('8');
                                    $loyalty->setAmount($loyalty_add);
                                    $OI_time=date('Y-m-d H:i:s');
                                    $loyalty->setLoyaltyDate($OI_time);

                                    //adding new entry in to transactions
                                    $transaction = new Transaction();
                                    $transaction->setTmId($tm_id);
                                    $transaction->setTrType('Deposited');
                                    $transaction->setTrAmount($loyalty_add);
                                    $transaction->setTrDate($OI_time);


                                    $em=$this->getDoctrine()->getManager();
                                    $em->persist($loyalty);
                                    $em->persist($transaction);
                                    $em->flush();

                                    //updaing main balance
                                    $em = $this->getDoctrine()->getManager();
                                    $update = $em->getRepository('LoginLoginBundle:Account')->findOneBy(array('tmId'=>$tm_id));
                                    /** @var $update Account */
                                    $update->setAccountBalance($new_bal);
                                    $em->flush();
                                }
                            }

                            $current_balance = $this->getDoctrine()
                                ->getRepository('LoginLoginBundle:Account')
                                ->findOneBy(array('tmId'=>$tm_id));
                            /** @var $current_balance Account */
                            $format_balance= number_format($current_balance->getAccountBalance());
                            $bal = intval($current_balance->getAccountBalance());
                            $loyalty = $bal*80/100;
                            $loyalty_add = number_format($loyalty);
                            $new_bal = $bal+$loyalty;
                            $new_bal = number_format($new_bal);


                            $optin_details = $this->getDoctrine()
                                ->getRepository('LoginLoginBundle:Optin')
                                ->findOneBy(array('tmId'=>$tm_id));

                            $date=date_create("$get_doj");
                            date_add($date,date_interval_create_from_date_string("3285 days"));
                            $loyaty_date = date_format($date,"Y-m-d");
                            $loyalty_date_formated = date_format($date,"d-m-Y");


                            $curr_month = date("m");

                            $em=$this->getDoctrine()->getManager();
                            $repository = $em->getRepository('LoginLoginBundle:Withdraw');
                            $new_payout =$repository->findBy(array('tmId'=>$tm_id,'wtYear'=>9));

                            $payout_count = sizeof($new_payout);

                            return $this->render('LoginLoginBundle:Default:eight_year.html.twig',
                                array(
                                    'payout_count'=>$payout_count,
                                    'month'=>$curr_month,
                                    'diff'=>$diff,
                                    'loyalty'=>$loyalty_add,
                                    'full_bal'=>$new_bal,
                                    'optin_data'=>$optin_details,
                                    'balance' => $format_balance,
                                    'loyalty_date'=>$loyaty_date,
                                    'loyalty_date_formatted'=>$loyalty_date_formated,
                                    'userRole'=>$role,
                                    'name' => $user->getUserName(),
                                    'all_roles' => $role_all
                                    ));
                        }
                        if(($diff>=3285) && ($diff<3650))//Running ninth year
                        {
                            if ($diff>=3285)//Running 9th year and last month
                            {
                                //checking for 9th year entry
                                $em = $this->getDoctrine()->getManager();
                                $update = $em->getRepository('LoginLoginBundle:Loyalty')->findOneBy(array('tmId'=>$tm_id,'loyaltyYear'=>9));
                                $num = sizeof($update);
                                if($num==0)//if not
                                {
                                    //fetching balance
                                    $em = $this->getDoctrine()->getManager();
                                    $update = $em->getRepository('LoginLoginBundle:Account')->findOneBy(array('tmId'=>$tm_id));
                                    /** @var $update Account */

                                    $bal = intval($update->getAccountBalance());
                                    $loyalty_add = $bal*80/100;
                                    $new_bal = $bal+$loyalty_add;

                                    //adding new entry in loyalty for 9th year
                                    $loyalty = new Loyalty();
                                    $loyalty->setTmId($tm_id);
                                    $loyalty->setLoyaltyYear('9');
                                    $loyalty->setAmount($loyalty_add);
                                    $OI_time=date('Y-m-d H:i:s');
                                    $loyalty->setLoyaltyDate($OI_time);

                                    //adding new entry in to transactions
                                    $transaction = new Transaction();
                                    $transaction->setTmId($tm_id);
                                    $transaction->setTrType('Deposited');
                                    $transaction->setTrAmount($loyalty_add);
                                    $transaction->setTrDate($OI_time);


                                    $em=$this->getDoctrine()->getManager();
                                    $em->persist($loyalty);
                                    $em->persist($transaction);
                                    $em->flush();

                                    //updaing main balance
                                    $em = $this->getDoctrine()->getManager();
                                    $update = $em->getRepository('LoginLoginBundle:Account')->findOneBy(array('tmId'=>$tm_id));
                                    /** @var $update Account */
                                    $update->setAccountBalance($new_bal);
                                    $em->flush();
                                }
                            }

                            $current_balance = $this->getDoctrine()
                                ->getRepository('LoginLoginBundle:Account')
                                ->findOneBy(array('tmId'=>$tm_id));
                            /** @var $current_balance Account */
                            $format_balance= number_format($current_balance->getAccountBalance());
                            $bal = intval($current_balance->getAccountBalance());
                            $loyalty = $bal*90/100;
                            $loyalty_add = number_format($loyalty);
                            $new_bal = $bal+$loyalty;
                            $new_bal = number_format($new_bal);


                            $optin_details = $this->getDoctrine()
                                ->getRepository('LoginLoginBundle:Optin')
                                ->findOneBy(array('tmId'=>$tm_id));

                            $date=date_create("$get_doj");
                            date_add($date,date_interval_create_from_date_string("3650 days"));
                            $loyaty_date = date_format($date,"Y-m-d");
                            $loyalty_date_formated = date_format($date,"d-m-Y");


                            $curr_month = date("m");

                            $em=$this->getDoctrine()->getManager();
                            $repository = $em->getRepository('LoginLoginBundle:Withdraw');
                            $new_payout =$repository->findBy(array('tmId'=>$tm_id,'wtYear'=>10));

                            $payout_count = sizeof($new_payout);

                            return $this->render('LoginLoginBundle:Default:ninth_year.html.twig',
                                array(
                                    'payout_count'=>$payout_count,
                                    'month'=>$curr_month,
                                    'diff'=>$diff,
                                    'loyalty'=>$loyalty_add,
                                    'full_bal'=>$new_bal,
                                    'optin_data'=>$optin_details,
                                    'balance' => $format_balance,
                                    'loyalty_date'=>$loyaty_date,
                                    'loyalty_date_formatted'=>$loyalty_date_formated,
                                    'userRole'=>$role,
                                    'name' => $user->getUserName(),
                                    'all_roles' => $role_all
                                ));

                        }
                        if(($diff>=3650) && ($diff<4015))//Running tenth year
                        {

                            if ($diff>=3650)//Running 10th year and last month
                            {
                                //checking for 9th year entry
                                $em = $this->getDoctrine()->getManager();
                                $update = $em->getRepository('LoginLoginBundle:Loyalty')->findOneBy(array('tmId'=>$tm_id,'loyaltyYear'=>10));
                                $num = sizeof($update);
                                if($num==0)//if not
                                {
                                    //fetching balance
                                    $em = $this->getDoctrine()->getManager();
                                    $update = $em->getRepository('LoginLoginBundle:Account')->findOneBy(array('tmId'=>$tm_id));
                                    /** @var $update Account */

                                    $bal = intval($update->getAccountBalance());
                                    $loyalty_add = $bal*90/100;
                                    $new_bal = $bal+$loyalty_add;

                                    //adding new entry in loyalty for 10th year
                                    $loyalty = new Loyalty();
                                    $loyalty->setTmId($tm_id);
                                    $loyalty->setLoyaltyYear('10');
                                    $loyalty->setAmount($loyalty_add);
                                    $OI_time=date('Y-m-d H:i:s');
                                    $loyalty->setLoyaltyDate($OI_time);

                                    //adding new entry in to transactions
                                    $transaction = new Transaction();
                                    $transaction->setTmId($tm_id);
                                    $transaction->setTrType('Deposited');
                                    $transaction->setTrAmount($loyalty_add);
                                    $transaction->setTrDate($OI_time);


                                    $em=$this->getDoctrine()->getManager();
                                    $em->persist($loyalty);
                                    $em->persist($transaction);
                                    $em->flush();

                                    //updaing main balance
                                    $em = $this->getDoctrine()->getManager();
                                    $update = $em->getRepository('LoginLoginBundle:Account')->findOneBy(array('tmId'=>$tm_id));
                                    /** @var $update Account */
                                    $update->setAccountBalance($new_bal);
                                    $em->flush();
                                }
                            }

                            $current_balance = $this->getDoctrine()
                                ->getRepository('LoginLoginBundle:Account')
                                ->findOneBy(array('tmId'=>$tm_id));
                            /** @var $current_balance Account */
                            $format_balance= number_format($current_balance->getAccountBalance());
                            $bal = intval($current_balance->getAccountBalance());
                            $loyalty = $bal*100/100;
                            $loyalty_add = number_format($loyalty);
                            $new_bal = $bal+$loyalty;
                            $new_bal = number_format($new_bal);


                            $optin_details = $this->getDoctrine()
                                ->getRepository('LoginLoginBundle:Optin')
                                ->findOneBy(array('tmId'=>$tm_id));

                            $date=date_create("$get_doj");
                            date_add($date,date_interval_create_from_date_string("4015 days"));
                            $loyaty_date = date_format($date,"Y-m-d");
                            $loyalty_date_formated = date_format($date,"d-m-Y");


                            $curr_month = date("m");

                            $em=$this->getDoctrine()->getManager();
                            $repository = $em->getRepository('LoginLoginBundle:Withdraw');
                            $new_payout =$repository->findBy(array('tmId'=>$tm_id,'wtYear'=>11));

                            $payout_count = sizeof($new_payout);

                            return $this->render('LoginLoginBundle:Default:tenth_year.html.twig',
                                array(
                                    'payout_count'=>$payout_count,
                                    'month'=>$curr_month,
                                    'diff'=>$diff,
                                    'loyalty'=>$loyalty_add,
                                    'full_bal'=>$new_bal,
                                    'optin_data'=>$optin_details,
                                    'balance' => $format_balance,
                                    'loyalty_date'=>$loyaty_date,
                                    'loyalty_date_formatted'=>$loyalty_date_formated,
                                    'userRole'=>$role,
                                    'all_roles' => $role_all
                                ));


                        }
                        if($diff>4015)//Running eleventh year
                        {
                                //checking for 11th year entry
                                $em = $this->getDoctrine()->getManager();
                                $update = $em->getRepository('LoginLoginBundle:Loyalty')->findOneBy(array('tmId'=>$tm_id,'loyaltyYear'=>11));
                                $num = sizeof($update);
                                if($num==0)//if not
                                {
                                    //fetching balance
                                    $em = $this->getDoctrine()->getManager();
                                    $update = $em->getRepository('LoginLoginBundle:Account')->findOneBy(array('tmId'=>$tm_id));
                                    /** @var $update Account */

                                    $bal = intval($update->getAccountBalance());
                                    $loyalty_add = $bal*100/100;
                                    $new_bal = $bal+$loyalty_add;

                                    //adding new entry in loyalty for 11th year
                                    $loyalty = new Loyalty();
                                    $loyalty->setTmId($tm_id);
                                    $loyalty->setLoyaltyYear('11');
                                    $loyalty->setAmount($loyalty_add);
                                    $OI_time=date('Y-m-d H:i:s');
                                    $loyalty->setLoyaltyDate($OI_time);

                                    //adding new entry in to transactions
                                    $transaction = new Transaction();
                                    $transaction->setTmId($tm_id);
                                    $transaction->setTrType('Deposited');
                                    $transaction->setTrAmount($loyalty_add);
                                    $transaction->setTrDate($OI_time);


                                    $em=$this->getDoctrine()->getManager();
                                    $em->persist($loyalty);
                                    $em->persist($transaction);
                                    $em->flush();

                                    //updaing main balance
                                    $em = $this->getDoctrine()->getManager();
                                    $update = $em->getRepository('LoginLoginBundle:Account')->findOneBy(array('tmId'=>$tm_id));
                                    /** @var $update Account */
                                    $update->setAccountBalance($new_bal);
                                    $em->flush();
                                }

                            $current_balance = $this->getDoctrine()
                                ->getRepository('LoginLoginBundle:Account')
                                ->findOneBy(array('tmId'=>$tm_id));
                            /** @var $current_balance Account */
                            $format_balance= number_format($current_balance->getAccountBalance());



                            return $this->render('LoginLoginBundle:Default:eleventh_year.html.twig',
                                array(
                                    'balance' => $format_balance,
                                    'userRole'=>$role,
                                    'all_roles' => $role_all
                                ));

                        }

                }
            }
            else
            {
                $department_details = $this->getDoctrine()
                    ->getRepository('LoginLoginBundle:Department')
                    ->findAll();


                $designation_details = $this->getDoctrine()
                    ->getRepository('LoginLoginBundle:Designation')
                    ->findAll();
                return $this->render('LoginLoginBundle:Default:index.html.twig',array('department_details'=>$department_details,'designation_details'=>$designation_details,'name' => 'Invalid Credentials'));
            }
        }
        else
        {
            if($session->has('login'))
            {
                $login = $session->get('login');
                $username= $login->getUsername();
                $password = $login->getPassword();
                $role= $login->getRole();
                $user =$repository->findOneBy(
                    array(
                        'userName'=>$username,
                        'userPassword'=>$password,
                        'userRole'=>$role
                    ));
                $role_all =$repository->findBy(
                    array(
                        'userName'=>$username,
                    ));
                if($user)
                {
                    $userrole=$user->getUserRole();
                    switch($userrole)
                    {
                        case 'Admin':
                            return $this->render('LoginLoginBundle:Default:admin.html.twig',
                                array(
                                    'name' => $user->getUserName(),
                                    'role' => $user->getUserRole(),
                                    'userRole'=>$role,
                                    'all_roles' => $role_all
                                ));
                            break;

                        case 'Super Admin':
                            $id=$this->getITID();

                            $em = $this->getDoctrine()->getManager();
                            $repository = $em->getRepository('LoginLoginBundle:Teammember');
                            $tm = $repository->findOneBy(array('itNo' => $id));

                            $department = $tm->getTmDepartment();
                            $optin_details = $this->getDoctrine()
                                ->getRepository('LoginLoginBundle:Optin')
                                ->findBy(array('dhStatus'=>'Pending','tmDesignation'=> array('Head','Director')));

                            $optin_pending_count = intval(count($optin_details));

                            $optin_approved = $this->getDoctrine()
                                ->getRepository('LoginLoginBundle:Optin')
                                ->findBy(array('dhStatus'=>'Approved','tmDesignation'=> array('Head','Director')));
                            $optin_approved_count = intval(count($optin_approved));


                            $optin_rejected = $this->getDoctrine()
                                ->getRepository('LoginLoginBundle:Optin')
                                ->findBy(array('dhStatus'=>'Rejected','tmDesignation'=> array('Head','Director')));
                            $optin_rejected_count = intval(count($optin_rejected));

                            $payout_pending = $this->getDoctrine()
                                ->getRepository('LoginLoginBundle:Withdraw')
                                ->findBy(array('dhStatus'=>'Pending','tmDesignation'=> array('Head','Director')));
                            $pay_pending = intval(count($payout_pending));

                            $payout_approved = $this->getDoctrine()
                                ->getRepository('LoginLoginBundle:Withdraw')
                                ->findBy(array('dhStatus'=>'Approved','tmDesignation'=> array('Head','Director')));
                            $pay_approved = intval(count($payout_approved));

                            $payout_rejected = $this->getDoctrine()
                                ->getRepository('LoginLoginBundle:Withdraw')
                                ->findBy(array('dhStatus'=>'Rejected','tmDesignation'=> array('Head','Director')));
                            $pay_rejected = intval(count($payout_rejected));
                            return $this->render('LoginLoginBundle:Default:super_admin.html.twig',
                                array(
                                    'name' => $user->getUserName(),
                                    'role' => $user->getUserRole(),
                                    'userRole'=>$role,
                                    'all_roles' => $role_all,
                                    'opt_pending' => $optin_pending_count,
                                    'opt_approved' => $optin_approved_count,
                                    'opt_rejected' => $optin_rejected_count,
                                    'pay_pending' => $pay_pending,
                                    'pay_approved' => $pay_approved,
                                    'pay_rejected' => $pay_rejected
                                ));
                            break;

                        case 'Finance Manager':
                            $payout_pending = $this->getDoctrine()
                                ->getRepository('LoginLoginBundle:Withdraw')
                                ->findBy(array('dhStatus'=>'Approved','hrStatus'=>'Approved','fmStatus'=>'Pending'));
                            $pay_pending = intval(count($payout_pending));

                            $payout_approved = $this->getDoctrine()
                                ->getRepository('LoginLoginBundle:Withdraw')
                                ->findBy(array('dhStatus'=>'Approved','hrStatus'=>'Approved','fmStatus'=>'Approved'));
                            $pay_approved = intval(count($payout_approved));

                            $payout_onhold = $this->getDoctrine()
                                ->getRepository('LoginLoginBundle:Withdraw')
                                ->findBy(array('dhStatus'=>'Approved','hrStatus'=>'Approved','fmStatus'=>'Onhold'));
                            $pay_onhold = intval(count($payout_onhold));

                            $payout_paid = $this->getDoctrine()
                                ->getRepository('LoginLoginBundle:Withdraw')
                                ->findBy(array('dhStatus'=>'Approved','hrStatus'=>'Approved','fmStatus'=>'Paid'));
                            $pay_paid = intval(count($payout_paid));
                            return $this->render('LoginLoginBundle:Default:finance_manager.html.twig',
                                array(
                                    'name' => $user->getUserName(),
                                    'role' => $user->getUserRole(),
                                    'all_roles' => $role_all,
                                    'userRole'=>$role,
                                    'pay_pending' => $pay_pending,
                                    'pay_approved' => $pay_approved,
                                    'pay_onhold' => $pay_onhold,
                                    'pay_paid' => $pay_paid
                                ));
                            break;

                        case 'HR Manager':

                            $optin_details = $this->getDoctrine()
                                ->getRepository('LoginLoginBundle:Optin')
                                ->findBy(array('dhStatus'=>'Approved','hrStatus'=>'Pending'));
                            $optin_pending_count = intval(count($optin_details));


                            $optin_approved = $this->getDoctrine()
                                ->getRepository('LoginLoginBundle:Optin')
                                ->findBy(array('dhStatus'=>'Approved','hrStatus'=>'Approved'));
                            $optin_approved_count = intval(count($optin_approved));


                            $optin_rejected = $this->getDoctrine()
                                ->getRepository('LoginLoginBundle:Optin')
                                ->findBy(array('dhStatus'=>'Approved','hrStatus'=>'Rejected'));
                            $optin_rejected_count = intval(count($optin_rejected));

                            $payout_pending = $this->getDoctrine()
                                ->getRepository('LoginLoginBundle:Withdraw')
                                ->findBy(array('dhStatus'=>'Approved','hrStatus'=>'Pending'));
                            $pay_pending = intval(count($payout_pending));

                            $payout_approved = $this->getDoctrine()
                                ->getRepository('LoginLoginBundle:Withdraw')
                                ->findBy(array('dhStatus'=>'Approved','hrStatus'=>'Approved'));
                            $pay_approved = intval(count($payout_approved));

                            $payout_rejected = $this->getDoctrine()
                                ->getRepository('LoginLoginBundle:Withdraw')
                                ->findBy(array('dhStatus'=>'Approved','hrStatus'=>'Rejected'));
                            $pay_rejected = intval(count($payout_rejected));
                            return $this->render('LoginLoginBundle:Default:hr_manager.html.twig',
                                array(
                                    'name' => $user->getUserName(),
                                    'role' => $user->getUserRole(),
                                    'userRole'=>$role,
                                    'all_roles' => $role_all,
                                    'opt_pending' => $optin_pending_count,
                                    'opt_approved' => $optin_approved_count,
                                    'opt_rejected' => $optin_rejected_count,
                                    'pay_pending' => $pay_pending,
                                    'pay_approved' => $pay_rejected,
                                    'pay_rejected' => $pay_rejected
                                ));
                            break;

                        case 'Department Head':
                            $id=$this->getITID();

                            $em = $this->getDoctrine()->getManager();
                            $repository = $em->getRepository('LoginLoginBundle:Teammember');
                            $tm = $repository->findOneBy(array('itNo' => $id));

                            $department = $tm->getTmDepartment();
                            $name = $tm->getTmName();
                            $criteria = new \Doctrine\Common\Collections\Criteria();
                            $criteria->where($criteria->expr()->eq('dhStatus', 'Pending'))
                                ->andwhere($criteria->expr()->eq('tmDepartment', $department))
                                ->andwhere($criteria->expr()->notIn('tmDesignation', array('Head','Director')));
                            $optin_details = $this->getDoctrine()
                                ->getRepository('LoginLoginBundle:Optin')->matching($criteria);
                            $optin_pending_count = intval(count($optin_details));

                            $criteria = new \Doctrine\Common\Collections\Criteria();
                            $criteria->where($criteria->expr()->eq('dhStatus', 'Approved'))
                                ->andwhere($criteria->expr()->eq('tmDepartment', $department))
                                ->andwhere($criteria->expr()->notIn('tmDesignation', array('Head','Director')));
                            $optin_approved = $this->getDoctrine()
                                ->getRepository('LoginLoginBundle:Optin')->matching($criteria);
                            $optin_approved_count = intval(count($optin_approved));


                            $criteria = new \Doctrine\Common\Collections\Criteria();
                            $criteria->where($criteria->expr()->eq('dhStatus', 'Rejected'))
                                ->andwhere($criteria->expr()->eq('tmDepartment', $department))
                                ->andwhere($criteria->expr()->notIn('tmDesignation', array('Head','Director')));
                            $optin_rejected = $this->getDoctrine()
                                ->getRepository('LoginLoginBundle:Optin')->matching($criteria);
                            $optin_rejected_count = intval(count($optin_rejected));

                            $criteria = new \Doctrine\Common\Collections\Criteria();
                            $criteria->where($criteria->expr()->eq('dhStatus', 'Pending'))
                                ->andwhere($criteria->expr()->eq('tmDepartment', $department))
                                ->andwhere($criteria->expr()->notIn('tmDesignation', array('Head','Director')));
                            $payout_pending = $this->getDoctrine()
                                ->getRepository('LoginLoginBundle:Withdraw')->matching($criteria);
                            $pay_pending = intval(count($payout_pending));

                            $criteria = new \Doctrine\Common\Collections\Criteria();
                            $criteria->where($criteria->expr()->eq('dhStatus', 'Approved'))
                                ->andwhere($criteria->expr()->eq('tmDepartment', $department))
                                ->andwhere($criteria->expr()->notIn('tmDesignation', array('Head','Director')));
                            $payout_approved = $this->getDoctrine()
                                ->getRepository('LoginLoginBundle:Withdraw')->matching($criteria);
                            $pay_approved = intval(count($payout_approved));

                            $criteria = new \Doctrine\Common\Collections\Criteria();
                            $criteria->where($criteria->expr()->eq('dhStatus', 'Rejected'))
                                ->andwhere($criteria->expr()->eq('tmDepartment', $department))
                                ->andwhere($criteria->expr()->notIn('tmDesignation', array('Head','Director')));
                            $payout_rejected = $this->getDoctrine()
                                ->getRepository('LoginLoginBundle:Withdraw')->matching($criteria);
                            $pay_rejected = intval(count($payout_rejected));

                            return $this->render('LoginLoginBundle:Default:department_head.html.twig',
                                array(
                                    'name' => $user->getUserName(),
                                    'role' => $user->getUserRole(),
                                    'userRole'=>$role,
                                    'tm_name' => $name,
                                    'all_roles' => $role_all,
                                    'opt_pending' => $optin_pending_count,
                                    'opt_approved' => $optin_approved_count,
                                    'opt_rejected' => $optin_rejected_count,
                                    'pay_pending' => $pay_pending,
                                    'pay_approved' => $pay_approved,
                                    'pay_rejected' => $pay_rejected
                                ));
                            break;

                        default:
                            $tm_id = $this->getITID();
                            $em = $this->getDoctrine()->getManager();
                            $update = $em->getRepository('LoginLoginBundle:Teammember')->findOneBy(array('itNo'=>$tm_id));
                            /** @var $update Teammember */
                            $get_doj= $update->getTmDoj();

                            $curr_date = date("Y-m-d");
                            $diff = $this->get_dateDifference($curr_date, $get_doj, '%a');



                            if($diff<1460)//before loyalty starts
                            {

                                $date=date_create("$get_doj");
                                date_add($date,date_interval_create_from_date_string("1460 days"));
                                $activation_date = date_format($date,"Y-m-d");
                                $activation_date_formated = date_format($date,"d-m-Y");

                                $optin_details = $this->getDoctrine()
                                    ->getRepository('LoginLoginBundle:Optin')
                                    ->findOneBy(array('tmId'=>$tm_id));

                                return $this->render('LoginLoginBundle:Default:before_loyalty.html.twig',
                                    array(
                                        'activation_date' => $activation_date,
                                        'activation_date_formatted'=>$activation_date_formated,
                                        'optin_data'=>$optin_details,
                                        'userRole'=>$role,
                                        'name' => $user->getUserName(),
                                        'all_roles' => $role_all
                                    ));
                            }
                            if(($diff>=1460) && ($diff<1825))//Running fourth year
                            {
                                if($diff>=1460)
                                {
                                    $em = $this->getDoctrine()->getManager();
                                    $update = $em->getRepository('LoginLoginBundle:Loyalty')->findOneBy(array('tmId'=>$tm_id,'loyaltyYear'=>4));
                                    $num = sizeof($update);
                                    if($num==0)//if not
                                    {
                                        $em = $this->getDoctrine()->getManager();
                                        $update = $em->getRepository('LoginLoginBundle:Teammember')->findOneBy(array('itNo' => $tm_id));
                                        /** @var $update Teammember */
                                        $tm_designation = $update->getTmDesignation();
                                        $em->flush();

                                        $repository = $em->getRepository('LoginLoginBundle:Designation');
                                        $account_balance = $repository->findOneBy(array('designation' => $tm_designation));
                                        $base_amount = $account_balance->getBase();

                                        //updaing main balance
                                        $em = $this->getDoctrine()->getManager();
                                        $update = $em->getRepository('LoginLoginBundle:Account')->findOneBy(array('tmId' => $tm_id));
                                        /** @var $update Account */
                                        $update->setAccountBalance($base_amount);
                                        $em->flush();


                                        //adding new entry in loyalty for 5th year
                                        $loyalty = new Loyalty();
                                        $loyalty->setTmId($tm_id);
                                        $loyalty->setLoyaltyYear('4');
                                        $loyalty->setAmount($base_amount);
                                        $OI_time = date('Y-m-d H:i:s');
                                        $loyalty->setLoyaltyDate($OI_time);

                                        //adding new entry in to transactions
                                        $transaction = new Transaction();
                                        $transaction->setTmId($tm_id);
                                        $transaction->setTrType('Deposited');
                                        $transaction->setTrAmount($base_amount);
                                        $transaction->setTrDate($OI_time);


                                        $em = $this->getDoctrine()->getManager();
                                        $em->persist($loyalty);
                                        $em->persist($transaction);
                                        $em->flush();
                                    }
                                }
                                $current_balance = $this->getDoctrine()
                                    ->getRepository('LoginLoginBundle:Account')
                                    ->findOneBy(array('tmId'=>$tm_id));
                                /** @var $current_balance Account */
                                $format_balance= number_format($current_balance->getAccountBalance());
                                $bal = intval($current_balance->getAccountBalance());
                                $loyalty = $bal*25/100;
                                $loyalty_add = number_format($loyalty);
                                $new_bal = $bal+$loyalty;
                                $new_bal = number_format($new_bal);

                                $optin_details = $this->getDoctrine()
                                    ->getRepository('LoginLoginBundle:Optin')
                                    ->findOneBy(array('tmId'=>$tm_id));

                                $date=date_create("$get_doj");
                                date_add($date,date_interval_create_from_date_string("1825 days"));
                                $loyaty_date = date_format($date,"Y-m-d");
                                $loyalty_date_formated = date_format($date,"d-m-Y");

                                return $this->render('LoginLoginBundle:Default:fourth_year.html.twig',
                                    array(
                                        'loyalty'=>$loyalty_add,
                                        'full_bal'=>$new_bal,
                                        'optin_data'=>$optin_details,
                                        'balance' => $format_balance,
                                        'loyalty_date'=>$loyaty_date,
                                        'loyalty_date_formatted'=>$loyalty_date_formated,
                                        'userRole'=>$role,
                                        'name' => $user->getUserName(),
                                        'all_roles' => $role_all
                                    ));
                            }
                            if(($diff>=1825) && ($diff<2190))//Running fifth year
                            {

                                if ($diff>=1825)//Running 5th year and last month
                                {
                                    //checking for 5th year entry
                                    $em = $this->getDoctrine()->getManager();
                                    $update = $em->getRepository('LoginLoginBundle:Loyalty')->findOneBy(array('tmId'=>$tm_id,'loyaltyYear'=>5));
                                    $num = sizeof($update);
                                    if($num==0)//if not
                                    {
                                        $em = $this->getDoctrine()->getManager();
                                        $update = $em->getRepository('LoginLoginBundle:Teammember')->findOneBy(array('itNo'=>$tm_id));
                                        /** @var $update Teammember */
                                        $tm_designation = $update->getTmDesignation();
                                        $em->flush();


                                        //fetching balance
                                        $em = $this->getDoctrine()->getManager();
                                        $update = $em->getRepository('LoginLoginBundle:Account')->findOneBy(array('tmId'=>$tm_id));
                                        /** @var $update Account */

                                        $bal = intval($update->getAccountBalance());
                                        $loyalty_add = $bal*25/100;
                                        $new_bal = $bal+$loyalty_add;

                                        //adding new entry in loyalty for 5th year
                                        $loyalty = new Loyalty();
                                        $loyalty->setTmId($tm_id);
                                        $loyalty->setLoyaltyYear('5');
                                        $loyalty->setAmount($loyalty_add);
                                        $OI_time=date('Y-m-d H:i:s');
                                        $loyalty->setLoyaltyDate($OI_time);

                                        //adding new entry in to transactions
                                        $transaction = new Transaction();
                                        $transaction->setTmId($tm_id);
                                        $transaction->setTrType('Deposited');
                                        $transaction->setTrAmount($loyalty_add);
                                        $transaction->setTrDate($OI_time);


                                        $em=$this->getDoctrine()->getManager();
                                        $em->persist($loyalty);
                                        $em->persist($transaction);
                                        $em->flush();

                                        //updaing main balance
                                        $em = $this->getDoctrine()->getManager();
                                        $update = $em->getRepository('LoginLoginBundle:Account')->findOneBy(array('tmId'=>$tm_id));
                                        /** @var $update Account */
                                        $update->setAccountBalance($new_bal);
                                        $em->flush();
                                    }
                                }
                                $account_balance = $this->getDoctrine()
                                    ->getRepository('LoginLoginBundle:Account')
                                    ->findOneBy(array('tmId'=>$tm_id));
                                /** @var $account_balance Account */
                                $format_balance= number_format($account_balance->getAccountBalance());
                                $bal = intval($account_balance->getAccountBalance());
                                $loyalty_6th = $bal*50/100;
                                $loyalty_add_6th = number_format($loyalty_6th);
                                $new_bal_6th = $bal+$loyalty_6th;
                                $new_bal_6th = number_format($new_bal_6th);

                                $optin_details = $this->getDoctrine()
                                    ->getRepository('LoginLoginBundle:Optin')
                                    ->findOneBy(array('tmId'=>$tm_id));

                                $date=date_create("$get_doj");
                                date_add($date,date_interval_create_from_date_string("2190 days"));
                                $loyaty_date = date_format($date,"Y-m-d");
                                $loyalty_date_formated = date_format($date,"d-m-Y");

                                $curr_month = date("m");

                                $em=$this->getDoctrine()->getManager();
                                $repository = $em->getRepository('LoginLoginBundle:Withdraw');
                                $new_payout =$repository->findBy(array('tmId'=>$tm_id,'wtYear'=>6));

                                $payout_count = sizeof($new_payout);

                                return $this->render('LoginLoginBundle:Default:fifth_year.html.twig',
                                    array(
                                        'payout_count'=>$payout_count,
                                        'month'=>$curr_month,
                                        'diff'=>$diff,
                                        'loyalty'=>$loyalty_add_6th,
                                        'full_bal'=>$new_bal_6th,
                                        'optin_data'=>$optin_details,
                                        'balance' => $format_balance,
                                        'loyalty_date'=>$loyaty_date,
                                        'loyalty_date_formatted'=>$loyalty_date_formated,
                                        'userRole'=>$role,
                                        'name' => $user->getUserName(),
                                        'all_roles' => $role_all
                                    ));

                            }
                            if(($diff>=2190) && ($diff<2555))//Running sixth year
                            {
                                if ($diff>=2190)//Running 6th year and last month
                                {
                                    //checking for 6th year entry
                                    $em = $this->getDoctrine()->getManager();
                                    $update = $em->getRepository('LoginLoginBundle:Loyalty')->findOneBy(array('tmId'=>$tm_id,'loyaltyYear'=>6));
                                    $num = sizeof($update);
                                    if($num==0)//if not
                                    {
                                        //fetching balance
                                        $em = $this->getDoctrine()->getManager();
                                        $update = $em->getRepository('LoginLoginBundle:Account')->findOneBy(array('tmId'=>$tm_id));
                                        /** @var $update Account */

                                        $bal = intval($update->getAccountBalance());
                                        $loyalty_add = $bal*50/100;
                                        $new_bal = $bal+$loyalty_add;

                                        //adding new entry in loyalty for 6th year
                                        $loyalty = new Loyalty();
                                        $loyalty->setTmId($tm_id);
                                        $loyalty->setLoyaltyYear('6');
                                        $loyalty->setAmount($loyalty_add);
                                        $OI_time=date('Y-m-d H:i:s');
                                        $loyalty->setLoyaltyDate($OI_time);

                                        //adding new entry in to transactions
                                        $transaction = new Transaction();
                                        $transaction->setTmId($tm_id);
                                        $transaction->setTrType('Deposited');
                                        $transaction->setTrAmount($loyalty_add);
                                        $transaction->setTrDate($OI_time);


                                        $em=$this->getDoctrine()->getManager();
                                        $em->persist($loyalty);
                                        $em->persist($transaction);
                                        $em->flush();

                                        //updaing main balance
                                        $em = $this->getDoctrine()->getManager();
                                        $update = $em->getRepository('LoginLoginBundle:Account')->findOneBy(array('tmId'=>$tm_id));
                                        /** @var $update Account */
                                        $update->setAccountBalance($new_bal);
                                        $em->flush();
                                    }
                                }


                                $current_balance = $this->getDoctrine()
                                    ->getRepository('LoginLoginBundle:Account')
                                    ->findOneBy(array('tmId'=>$tm_id));
                                /** @var $current_balance Account */
                                $format_balance= number_format($current_balance->getAccountBalance());
                                $bal = intval($current_balance->getAccountBalance());
                                $loyalty = $bal*60/100;
                                $loyalty_add = number_format($loyalty);
                                $new_bal = $bal+$loyalty;
                                $new_bal = number_format($new_bal);


                                $optin_details = $this->getDoctrine()
                                    ->getRepository('LoginLoginBundle:Optin')
                                    ->findOneBy(array('tmId'=>$tm_id));

                                $date=date_create("$get_doj");
                                date_add($date,date_interval_create_from_date_string("2555 days"));
                                $loyaty_date = date_format($date,"Y-m-d");
                                $loyalty_date_formated = date_format($date,"d-m-Y");


                                $curr_month = date("m");

                                $em=$this->getDoctrine()->getManager();
                                $repository = $em->getRepository('LoginLoginBundle:Withdraw');
                                $new_payout =$repository->findBy(array('tmId'=>$tm_id,'wtYear'=>7));

                                $payout_count = sizeof($new_payout);


                                return $this->render('LoginLoginBundle:Default:sixth_year.html.twig',
                                    array(
                                        'payout_count'=>$payout_count,
                                        'month'=>$curr_month,
                                        'diff'=>$diff,
                                        'loyalty'=>$loyalty_add,
                                        'full_bal'=>$new_bal,
                                        'optin_data'=>$optin_details,
                                        'balance' => $format_balance,
                                        'loyalty_date'=>$loyaty_date,
                                        'loyalty_date_formatted'=>$loyalty_date_formated,
                                        'userRole'=>$role,
                                        'name' => $user->getUserName(),
                                        'all_roles' => $role_all
                                    ));


                            }
                            if(($diff>=2555) && ($diff<2920))//Running seventh year
                            {
                                if ($diff>=2555)//Running 7th year and last month
                                {
                                    //checking for 7th year entry
                                    $em = $this->getDoctrine()->getManager();
                                    $update = $em->getRepository('LoginLoginBundle:Loyalty')->findOneBy(array('tmId'=>$tm_id,'loyaltyYear'=>7));
                                    $num = sizeof($update);
                                    if($num==0)//if not
                                    {
                                        //fetching balance
                                        $em = $this->getDoctrine()->getManager();
                                        $update = $em->getRepository('LoginLoginBundle:Account')->findOneBy(array('tmId'=>$tm_id));
                                        /** @var $update Account */

                                        $bal = intval($update->getAccountBalance());
                                        $loyalty_add = $bal*60/100;
                                        $new_bal = $bal+$loyalty_add;

                                        //adding new entry in loyalty for 7th year
                                        $loyalty = new Loyalty();
                                        $loyalty->setTmId($tm_id);
                                        $loyalty->setLoyaltyYear('7');
                                        $loyalty->setAmount($loyalty_add);
                                        $OI_time=date('Y-m-d H:i:s');
                                        $loyalty->setLoyaltyDate($OI_time);

                                        //adding new entry in to transactions
                                        $transaction = new Transaction();
                                        $transaction->setTmId($tm_id);
                                        $transaction->setTrType('Deposited');
                                        $transaction->setTrAmount($loyalty_add);
                                        $transaction->setTrDate($OI_time);


                                        $em=$this->getDoctrine()->getManager();
                                        $em->persist($loyalty);
                                        $em->persist($transaction);
                                        $em->flush();

                                        //updaing main balance
                                        $em = $this->getDoctrine()->getManager();
                                        $update = $em->getRepository('LoginLoginBundle:Account')->findOneBy(array('tmId'=>$tm_id));
                                        /** @var $update Account */
                                        $update->setAccountBalance($new_bal);
                                        $em->flush();
                                    }
                                }

                                $current_balance = $this->getDoctrine()
                                    ->getRepository('LoginLoginBundle:Account')
                                    ->findOneBy(array('tmId'=>$tm_id));
                                /** @var $current_balance Account */
                                $format_balance= number_format($current_balance->getAccountBalance());
                                $bal = intval($current_balance->getAccountBalance());
                                $loyalty = $bal*70/100;
                                $loyalty_add = number_format($loyalty);
                                $new_bal = $bal+$loyalty;
                                $new_bal = number_format($new_bal);


                                $optin_details = $this->getDoctrine()
                                    ->getRepository('LoginLoginBundle:Optin')
                                    ->findOneBy(array('tmId'=>$tm_id));

                                $date=date_create("$get_doj");
                                date_add($date,date_interval_create_from_date_string("2920 days"));
                                $loyaty_date = date_format($date,"Y-m-d");
                                $loyalty_date_formated = date_format($date,"d-m-Y");

                                $curr_month = date("m");

                                $em=$this->getDoctrine()->getManager();
                                $repository = $em->getRepository('LoginLoginBundle:Withdraw');
                                $new_payout =$repository->findBy(array('tmId'=>$tm_id,'wtYear'=>8));

                                $payout_count = sizeof($new_payout);

                                return $this->render('LoginLoginBundle:Default:seventh_year.html.twig',
                                    array(
                                        'payout_count'=>$payout_count,
                                        'month'=>$curr_month,
                                        'diff'=>$diff,
                                        'loyalty'=>$loyalty_add,
                                        'full_bal'=>$new_bal,
                                        'optin_data'=>$optin_details,
                                        'balance' => $format_balance,
                                        'loyalty_date'=>$loyaty_date,
                                        'loyalty_date_formatted'=>$loyalty_date_formated,
                                        'userRole'=>$role,
                                        'name' => $user->getUserName(),
                                        'all_roles' => $role_all
                                    ));
                            }
                            if(($diff>=2920) && ($diff<3285))//Running eighth year
                            {

                                if ($diff>=2920)//Running 8th year and last month
                                {
                                    //checking for 8th year entry
                                    $em = $this->getDoctrine()->getManager();
                                    $update = $em->getRepository('LoginLoginBundle:Loyalty')->findOneBy(array('tmId'=>$tm_id,'loyaltyYear'=>8));
                                    $num = sizeof($update);
                                    if($num==0)//if not
                                    {
                                        //fetching balance
                                        $em = $this->getDoctrine()->getManager();
                                        $update = $em->getRepository('LoginLoginBundle:Account')->findOneBy(array('tmId'=>$tm_id));
                                        /** @var $update Account */

                                        $bal = intval($update->getAccountBalance());
                                        $loyalty_add = $bal*70/100;
                                        $new_bal = $bal+$loyalty_add;

                                        //adding new entry in loyalty for 8th year
                                        $loyalty = new Loyalty();
                                        $loyalty->setTmId($tm_id);
                                        $loyalty->setLoyaltyYear('8');
                                        $loyalty->setAmount($loyalty_add);
                                        $OI_time=date('Y-m-d H:i:s');
                                        $loyalty->setLoyaltyDate($OI_time);

                                        //adding new entry in to transactions
                                        $transaction = new Transaction();
                                        $transaction->setTmId($tm_id);
                                        $transaction->setTrType('Deposited');
                                        $transaction->setTrAmount($loyalty_add);
                                        $transaction->setTrDate($OI_time);


                                        $em=$this->getDoctrine()->getManager();
                                        $em->persist($loyalty);
                                        $em->persist($transaction);
                                        $em->flush();

                                        //updaing main balance
                                        $em = $this->getDoctrine()->getManager();
                                        $update = $em->getRepository('LoginLoginBundle:Account')->findOneBy(array('tmId'=>$tm_id));
                                        /** @var $update Account */
                                        $update->setAccountBalance($new_bal);
                                        $em->flush();
                                    }
                                }

                                $current_balance = $this->getDoctrine()
                                    ->getRepository('LoginLoginBundle:Account')
                                    ->findOneBy(array('tmId'=>$tm_id));
                                /** @var $current_balance Account */
                                $format_balance= number_format($current_balance->getAccountBalance());
                                $bal = intval($current_balance->getAccountBalance());
                                $loyalty = $bal*80/100;
                                $loyalty_add = number_format($loyalty);
                                $new_bal = $bal+$loyalty;
                                $new_bal = number_format($new_bal);


                                $optin_details = $this->getDoctrine()
                                    ->getRepository('LoginLoginBundle:Optin')
                                    ->findOneBy(array('tmId'=>$tm_id));

                                $date=date_create("$get_doj");
                                date_add($date,date_interval_create_from_date_string("3285 days"));
                                $loyaty_date = date_format($date,"Y-m-d");
                                $loyalty_date_formated = date_format($date,"d-m-Y");


                                $curr_month = date("m");

                                $em=$this->getDoctrine()->getManager();
                                $repository = $em->getRepository('LoginLoginBundle:Withdraw');
                                $new_payout =$repository->findBy(array('tmId'=>$tm_id,'wtYear'=>9));

                                $payout_count = sizeof($new_payout);

                                return $this->render('LoginLoginBundle:Default:eight_year.html.twig',
                                    array(
                                        'payout_count'=>$payout_count,
                                        'month'=>$curr_month,
                                        'diff'=>$diff,
                                        'loyalty'=>$loyalty_add,
                                        'full_bal'=>$new_bal,
                                        'optin_data'=>$optin_details,
                                        'balance' => $format_balance,
                                        'loyalty_date'=>$loyaty_date,
                                        'loyalty_date_formatted'=>$loyalty_date_formated,
                                        'userRole'=>$role,
                                        'name' => $user->getUserName(),
                                        'all_roles' => $role_all
                                    ));
                            }
                            if(($diff>=3285) && ($diff<3650))//Running ninth year
                            {
                                if ($diff>=3285)//Running 9th year and last month
                                {
                                    //checking for 9th year entry
                                    $em = $this->getDoctrine()->getManager();
                                    $update = $em->getRepository('LoginLoginBundle:Loyalty')->findOneBy(array('tmId'=>$tm_id,'loyaltyYear'=>9));
                                    $num = sizeof($update);
                                    if($num==0)//if not
                                    {
                                        //fetching balance
                                        $em = $this->getDoctrine()->getManager();
                                        $update = $em->getRepository('LoginLoginBundle:Account')->findOneBy(array('tmId'=>$tm_id));
                                        /** @var $update Account */

                                        $bal = intval($update->getAccountBalance());
                                        $loyalty_add = $bal*80/100;
                                        $new_bal = $bal+$loyalty_add;

                                        //adding new entry in loyalty for 9th year
                                        $loyalty = new Loyalty();
                                        $loyalty->setTmId($tm_id);
                                        $loyalty->setLoyaltyYear('9');
                                        $loyalty->setAmount($loyalty_add);
                                        $OI_time=date('Y-m-d H:i:s');
                                        $loyalty->setLoyaltyDate($OI_time);

                                        //adding new entry in to transactions
                                        $transaction = new Transaction();
                                        $transaction->setTmId($tm_id);
                                        $transaction->setTrType('Deposited');
                                        $transaction->setTrAmount($loyalty_add);
                                        $transaction->setTrDate($OI_time);


                                        $em=$this->getDoctrine()->getManager();
                                        $em->persist($loyalty);
                                        $em->persist($transaction);
                                        $em->flush();

                                        //updaing main balance
                                        $em = $this->getDoctrine()->getManager();
                                        $update = $em->getRepository('LoginLoginBundle:Account')->findOneBy(array('tmId'=>$tm_id));
                                        /** @var $update Account */
                                        $update->setAccountBalance($new_bal);
                                        $em->flush();
                                    }
                                }

                                $current_balance = $this->getDoctrine()
                                    ->getRepository('LoginLoginBundle:Account')
                                    ->findOneBy(array('tmId'=>$tm_id));
                                /** @var $current_balance Account */
                                $format_balance= number_format($current_balance->getAccountBalance());
                                $bal = intval($current_balance->getAccountBalance());
                                $loyalty = $bal*90/100;
                                $loyalty_add = number_format($loyalty);
                                $new_bal = $bal+$loyalty;
                                $new_bal = number_format($new_bal);


                                $optin_details = $this->getDoctrine()
                                    ->getRepository('LoginLoginBundle:Optin')
                                    ->findOneBy(array('tmId'=>$tm_id));

                                $date=date_create("$get_doj");
                                date_add($date,date_interval_create_from_date_string("3650 days"));
                                $loyaty_date = date_format($date,"Y-m-d");
                                $loyalty_date_formated = date_format($date,"d-m-Y");


                                $curr_month = date("m");

                                $em=$this->getDoctrine()->getManager();
                                $repository = $em->getRepository('LoginLoginBundle:Withdraw');
                                $new_payout =$repository->findBy(array('tmId'=>$tm_id,'wtYear'=>10));

                                $payout_count = sizeof($new_payout);

                                return $this->render('LoginLoginBundle:Default:ninth_year.html.twig',
                                    array(
                                        'payout_count'=>$payout_count,
                                        'month'=>$curr_month,
                                        'diff'=>$diff,
                                        'loyalty'=>$loyalty_add,
                                        'full_bal'=>$new_bal,
                                        'optin_data'=>$optin_details,
                                        'balance' => $format_balance,
                                        'loyalty_date'=>$loyaty_date,
                                        'loyalty_date_formatted'=>$loyalty_date_formated,
                                        'userRole'=>$role,
                                        'name' => $user->getUserName(),
                                        'all_roles' => $role_all
                                    ));

                            }
                            if(($diff>=3650) && ($diff<4015))//Running tenth year
                            {

                                if ($diff>=3650)//Running 10th year and last month
                                {
                                    //checking for 9th year entry
                                    $em = $this->getDoctrine()->getManager();
                                    $update = $em->getRepository('LoginLoginBundle:Loyalty')->findOneBy(array('tmId'=>$tm_id,'loyaltyYear'=>10));
                                    $num = sizeof($update);
                                    if($num==0)//if not
                                    {
                                        //fetching balance
                                        $em = $this->getDoctrine()->getManager();
                                        $update = $em->getRepository('LoginLoginBundle:Account')->findOneBy(array('tmId'=>$tm_id));
                                        /** @var $update Account */

                                        $bal = intval($update->getAccountBalance());
                                        $loyalty_add = $bal*90/100;
                                        $new_bal = $bal+$loyalty_add;

                                        //adding new entry in loyalty for 10th year
                                        $loyalty = new Loyalty();
                                        $loyalty->setTmId($tm_id);
                                        $loyalty->setLoyaltyYear('10');
                                        $loyalty->setAmount($loyalty_add);
                                        $OI_time=date('Y-m-d H:i:s');
                                        $loyalty->setLoyaltyDate($OI_time);

                                        //adding new entry in to transactions
                                        $transaction = new Transaction();
                                        $transaction->setTmId($tm_id);
                                        $transaction->setTrType('Deposited');
                                        $transaction->setTrAmount($loyalty_add);
                                        $transaction->setTrDate($OI_time);


                                        $em=$this->getDoctrine()->getManager();
                                        $em->persist($loyalty);
                                        $em->persist($transaction);
                                        $em->flush();

                                        //updaing main balance
                                        $em = $this->getDoctrine()->getManager();
                                        $update = $em->getRepository('LoginLoginBundle:Account')->findOneBy(array('tmId'=>$tm_id));
                                        /** @var $update Account */
                                        $update->setAccountBalance($new_bal);
                                        $em->flush();
                                    }
                                }

                                $current_balance = $this->getDoctrine()
                                    ->getRepository('LoginLoginBundle:Account')
                                    ->findOneBy(array('tmId'=>$tm_id));
                                /** @var $current_balance Account */
                                $format_balance= number_format($current_balance->getAccountBalance());
                                $bal = intval($current_balance->getAccountBalance());
                                $loyalty = $bal*100/100;
                                $loyalty_add = number_format($loyalty);
                                $new_bal = $bal+$loyalty;
                                $new_bal = number_format($new_bal);


                                $optin_details = $this->getDoctrine()
                                    ->getRepository('LoginLoginBundle:Optin')
                                    ->findOneBy(array('tmId'=>$tm_id));

                                $date=date_create("$get_doj");
                                date_add($date,date_interval_create_from_date_string("4015 days"));
                                $loyaty_date = date_format($date,"Y-m-d");
                                $loyalty_date_formated = date_format($date,"d-m-Y");


                                $curr_month = date("m");

                                $em=$this->getDoctrine()->getManager();
                                $repository = $em->getRepository('LoginLoginBundle:Withdraw');
                                $new_payout =$repository->findBy(array('tmId'=>$tm_id,'wtYear'=>11));

                                $payout_count = sizeof($new_payout);

                                return $this->render('LoginLoginBundle:Default:tenth_year.html.twig',
                                    array(
                                        'payout_count'=>$payout_count,
                                        'month'=>$curr_month,
                                        'diff'=>$diff,
                                        'loyalty'=>$loyalty_add,
                                        'full_bal'=>$new_bal,
                                        'optin_data'=>$optin_details,
                                        'balance' => $format_balance,
                                        'loyalty_date'=>$loyaty_date,
                                        'loyalty_date_formatted'=>$loyalty_date_formated,
                                        'userRole'=>$role,
                                        'name' => $user->getUserName(),
                                        'all_roles' => $role_all
                                    ));


                            }
                            if($diff>4015)//Running eleventh year
                            {
                                //checking for 11th year entry
                                $em = $this->getDoctrine()->getManager();
                                $update = $em->getRepository('LoginLoginBundle:Loyalty')->findOneBy(array('tmId'=>$tm_id,'loyaltyYear'=>11));
                                $num = sizeof($update);
                                if($num==0)//if not
                                {
                                    //fetching balance
                                    $em = $this->getDoctrine()->getManager();
                                    $update = $em->getRepository('LoginLoginBundle:Account')->findOneBy(array('tmId'=>$tm_id));
                                    /** @var $update Account */

                                    $bal = intval($update->getAccountBalance());
                                    $loyalty_add = $bal*100/100;
                                    $new_bal = $bal+$loyalty_add;

                                    //adding new entry in loyalty for 11th year
                                    $loyalty = new Loyalty();
                                    $loyalty->setTmId($tm_id);
                                    $loyalty->setLoyaltyYear('11');
                                    $loyalty->setAmount($loyalty_add);
                                    $OI_time=date('Y-m-d H:i:s');
                                    $loyalty->setLoyaltyDate($OI_time);

                                    //adding new entry in to transactions
                                    $transaction = new Transaction();
                                    $transaction->setTmId($tm_id);
                                    $transaction->setTrType('Deposited');
                                    $transaction->setTrAmount($loyalty_add);
                                    $transaction->setTrDate($OI_time);


                                    $em=$this->getDoctrine()->getManager();
                                    $em->persist($loyalty);
                                    $em->persist($transaction);
                                    $em->flush();

                                    //updaing main balance
                                    $em = $this->getDoctrine()->getManager();
                                    $update = $em->getRepository('LoginLoginBundle:Account')->findOneBy(array('tmId'=>$tm_id));
                                    /** @var $update Account */
                                    $update->setAccountBalance($new_bal);
                                    $em->flush();
                                }

                                $current_balance = $this->getDoctrine()
                                    ->getRepository('LoginLoginBundle:Account')
                                    ->findOneBy(array('tmId'=>$tm_id));
                                /** @var $current_balance Account */
                                $format_balance= number_format($current_balance->getAccountBalance());



                                return $this->render('LoginLoginBundle:Default:eleventh_year.html.twig',
                                    array(
                                        'balance' => $format_balance,
                                        'userRole'=>$role,
                                        'name' => $user->getUserName(),
                                        'all_roles' => $role_all
                                    ));

                            }
                    }
                }
            }

            $department_details = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Department')
                ->findAll();

            $designation_details = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Designation')
                ->findAll();
            return $this->render('LoginLoginBundle:Default:index.html.twig',array('designation_details'=>$designation_details,'department_details'=>$department_details));

        }
    }
    public function logged()
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('LoginLoginBundle:User');
        $session = new Session();
        $session-> get('login');
        if($session->has('login'))
        {
            $login = $session->get('login');
            $username = $login->getUsername();
            $password = $login->getPassword();
            $user = $repository->findOneBy(array('userName' => $username, 'userPassword' => $password));
            return true;
        }else {
            return false;
        }
    }
    public function getITID()
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('LoginLoginBundle:User');
        $session = new Session();
        $session-> get('login');

        if($session->has('login')) {
            $login = $session->get('login');
            $username = $login->getUsername();
            $password = $login->getPassword();
            $user = $repository->findOneBy(array('userName' => $username, 'userPassword' => $password));
           return $user->getTeamId();
        }
    }
    public function get_dateDifference($get_doj , $curr_date , $differenceFormat = '%a' )
    {
        $datetime1 = date_create($get_doj);
        $datetime2 = date_create($curr_date);
        $interval = date_diff($datetime1, $datetime2);
        return $interval->format($differenceFormat);
    }
    /**
     * @Route("/logout", name="logout")
     */
    public function logoutAction(Request $request){
        $session = new Session();
        $session-> get('login');
        $session->clear();
        $department_details = $this->getDoctrine()
            ->getRepository('LoginLoginBundle:Department')
            ->findAll();

        $designation_details = $this->getDoctrine()
            ->getRepository('LoginLoginBundle:Designation')
            ->findAll();
        return $this->render('LoginLoginBundle:Default:index.html.twig',array('designation_details'=>$designation_details,'department_details'=>$department_details));


    }
    /**
     * @Route("/place_optin", name="place_optin")
     */
    public function place_optinAction(Request $request)
    {
       if($request->getMethod() == 'POST')
       {
           $tm_id=$request->get('tmid');
           $tm_name=$request->get('tmname');
           $tm_email=$request->get('email');
           $tm_designation=$request->get('designation');
           $tm_department=$request->get('department');


           $em=$this->getDoctrine()->getManager();
           $repository = $em->getRepository('LoginLoginBundle:Optin');
           $new_optin =$repository->findBy(array('tmId'=>$tm_id));

           if($new_optin)
           {
               $department_details = $this->getDoctrine()
                   ->getRepository('LoginLoginBundle:Department')
                   ->findAll();

               $designation_details = $this->getDoctrine()
                   ->getRepository('LoginLoginBundle:Designation')
                   ->findAll();
               return $this->render('LoginLoginBundle:Default:index.html.twig',array('department_details'=>$department_details,'designation_details'=>$designation_details,'failure' => 'Duplicate Request'));

           }
           else
           {
               $optin = new Optin();
               $optin->setTmId($tm_id);
               $optin->setTmName($tm_name);
               $optin->setTmDesignation($tm_designation);
               $optin->setTmDepartment($tm_department);
               $optin->setDhStatus('Pending');
               $optin->setHrStatus('Pending');
               $optin->setDhComment('Not yet reviewed');
               $optin->setHrComment('Not yet reviewed');
               $optin->setDhApprDate(' ');
               $optin->setHrApprDate(' ');
               $optin->setOptActivation(0);

               $OI_time=date('Y-m-d H:i:s');
               $optin->setOiDate($OI_time);



               $teammember = new Teammember();
               $teammember->setItNo($tm_id);
               $teammember->setTmName($tm_name);
               $teammember->setTmEmail($tm_email);
               $teammember->setTmDesignation($tm_designation);
               $teammember->setTmDepartment($tm_department);


               $em=$this->getDoctrine()->getManager();
               $em->persist($optin);
               $em->persist($teammember);

               $em->flush();



               $department_details = $this->getDoctrine()
                   ->getRepository('LoginLoginBundle:Department')
                   ->findAll();

               $designation_details = $this->getDoctrine()
                   ->getRepository('LoginLoginBundle:Designation')
                   ->findAll();
               return $this->render('LoginLoginBundle:Default:index.html.twig',array('department_details'=>$department_details,'designation_details'=>$designation_details,'success' => 'Request Accepted'));
           }
       }
    }
    /**
     * @Route("/dh_optin", name="dh_optin")
     */
    public function dh_optinAction(Request $request)
    {
        if($this->logged()==true)
        {
            $id=$this->getITID();

            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository('LoginLoginBundle:Teammember');
            $tm = $repository->findOneBy(array('itNo' => $id));

            $department = $tm->getTmDepartment();

            $criteria = new \Doctrine\Common\Collections\Criteria();
            $criteria->where($criteria->expr()->eq('dhStatus', 'Pending'))
                ->andwhere($criteria->expr()->eq('tmDepartment', $department))
                ->andwhere($criteria->expr()->notIn('tmDesignation', array('Head','Director')));
            $optin_details = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Optin')->matching($criteria);


            $criteria = new \Doctrine\Common\Collections\Criteria();
            $criteria->where($criteria->expr()->eq('dhStatus', 'Approved'))
                ->andwhere($criteria->expr()->eq('tmDepartment', $department))
                ->andwhere($criteria->expr()->notIn('tmDesignation', array('Head','Director')));
            $optin_approved = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Optin')->matching($criteria);


            $criteria = new \Doctrine\Common\Collections\Criteria();
            $criteria->where($criteria->expr()->eq('dhStatus', 'Rejected'))
                ->andwhere($criteria->expr()->eq('tmDepartment', $department))
                ->andwhere($criteria->expr()->notIn('tmDesignation', array('Head','Director')));
            $optin_rejected = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Optin')->matching($criteria);

                return $this->render('LoginLoginBundle:Default:dh_optin.html.twig',array('optin_rejected'=>$optin_rejected,'optin_approved'=>$optin_approved,'optin_details'=>$optin_details));
        }
        else
        {
            $department_details = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Department')
                ->findAll();
            $designation_details = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Designation')
                ->findAll();
            return $this->render('LoginLoginBundle:Default:index.html.twig',array('designation_details'=>$designation_details,'department_details'=>$department_details));

        }
    }
    /**
     * @Route("/super_optin", name="super_optin")
     */
    public function super_optinAction(Request $request)
    {
        if($this->logged()==true)
        {
            $id=$this->getITID();

            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository('LoginLoginBundle:Teammember');
            $tm = $repository->findOneBy(array('itNo' => $id));


            $department = $tm->getTmDepartment();
            $optin_details = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Optin')
                ->findBy(array('dhStatus'=>'Pending','tmDesignation'=> array('Head','Director')));

            $optin_approved = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Optin')
                ->findBy(array('dhStatus'=>'Approved','tmDesignation'=> array('Head','Director')));

            $optin_rejected = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Optin')
                ->findBy(array('dhStatus'=>'Rejected','tmDesignation'=> array('Head','Director')));

            return $this->render('LoginLoginBundle:Default:dh_optin.html.twig',array('optin_rejected'=>$optin_rejected,'optin_approved'=>$optin_approved,'optin_details'=>$optin_details));
        }
        else
        {
            $department_details = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Department')
                ->findAll();
            $designation_details = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Designation')
                ->findAll();
            return $this->render('LoginLoginBundle:Default:index.html.twig',array('designation_details'=>$designation_details,'department_details'=>$department_details));

        }
    }
    /**
     * @Route("/dh_payout", name="dh_payout")
     */
    public function dh_payoutAction(Request $request)
    {
        if($this->logged()==true)
        {
            $id=$this->getITID();

            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository('LoginLoginBundle:Teammember');
            $tm = $repository->findOneBy(array('itNo' => $id));

            $department = $tm->getTmDepartment();

            $criteria = new \Doctrine\Common\Collections\Criteria();
            $criteria->where($criteria->expr()->eq('dhStatus', 'Pending'))
                ->andwhere($criteria->expr()->eq('tmDepartment', $department))
                ->andwhere($criteria->expr()->notIn('tmDesignation', array('Head','Director')));
            $optin_details = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Withdraw')->matching($criteria);

            $criteria = new \Doctrine\Common\Collections\Criteria();
            $criteria->where($criteria->expr()->eq('dhStatus', 'Approved'))
                ->andwhere($criteria->expr()->eq('tmDepartment', $department))
                ->andwhere($criteria->expr()->notIn('tmDesignation', array('Head','Director')));
            $optin_approved = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Withdraw')->matching($criteria);

            $criteria = new \Doctrine\Common\Collections\Criteria();
            $criteria->where($criteria->expr()->eq('dhStatus', 'Rejected'))
                ->andwhere($criteria->expr()->eq('tmDepartment', $department))
                ->andwhere($criteria->expr()->notIn('tmDesignation', array('Head','Director')));
            $optin_rejected = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Withdraw')->matching($criteria);

            return $this->render('LoginLoginBundle:Default:dh_payout.html.twig',array('optin_rejected'=>$optin_rejected,'optin_approved'=>$optin_approved,'optin_details'=>$optin_details));
        }
        else
        {
            $department_details = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Department')
                ->findAll();
            $designation_details = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Designation')
                ->findAll();
            return $this->render('LoginLoginBundle:Default:index.html.twig',array('designation_details'=>$designation_details,'department_details'=>$department_details));

        }
    }
    /**
     * @Route("/super_payout", name="super_payout")
     */
    public function super_payoutAction(Request $request)
    {
        if($this->logged()==true)
        {
            $id=$this->getITID();

            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository('LoginLoginBundle:Teammember');
            $tm = $repository->findOneBy(array('itNo' => $id));

            $department = $tm->getTmDepartment();
            $optin_details = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Withdraw')
                ->findBy(array('dhStatus'=>'Pending','tmDesignation'=> array('Head','Director')));

            $optin_approved = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Withdraw')
                ->findBy(array('dhStatus'=>'Approved','tmDesignation'=> array('Head','Director')));

            $optin_rejected = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Withdraw')
                ->findBy(array('dhStatus'=>'Rejected','tmDesignation'=> array('Head','Director')));

            return $this->render('LoginLoginBundle:Default:super_payout.html.twig',array('optin_rejected'=>$optin_rejected,'optin_approved'=>$optin_approved,'optin_details'=>$optin_details));
        }
        else
        {
            $department_details = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Department')
                ->findAll();
            $designation_details = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Designation')
                ->findAll();
            return $this->render('LoginLoginBundle:Default:index.html.twig',array('designation_details'=>$designation_details,'department_details'=>$department_details));

        }
    }
    /**
     * @Route("/hr_payout", name="hr_payout")
     */
    public function hr_payoutAction(Request $request)
    {
        if($this->logged()==true)
        {

            $optin_details = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Withdraw')
                ->findBy(array('dhStatus'=>'Approved','hrStatus'=>'Pending'));

            $optin_approved = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Withdraw')
                ->findBy(array('dhStatus'=>'Approved','hrStatus'=>'Approved'));

            $optin_rejected = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Withdraw')
                ->findBy(array('dhStatus'=>'Approved','hrStatus'=>'Rejected'));

            return $this->render('LoginLoginBundle:Default:hr_payout.html.twig',array('optin_rejected'=>$optin_rejected,'optin_approved'=>$optin_approved,'optin_details'=>$optin_details));
        }
        else
        {
            $department_details = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Department')
                ->findAll();
            $designation_details = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Designation')
                ->findAll();
            return $this->render('LoginLoginBundle:Default:index.html.twig',array('designation_details'=>$designation_details,'department_details'=>$department_details));

        }
    }
    /**
     * @Route("/fm_payout", name="fm_payout")
     */
    public function fm_payoutAction(Request $request)
    {
        if($this->logged()==true)
        {
            $payout_pending = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Withdraw')
                ->findBy(array('dhStatus'=>'Approved','hrStatus'=>'Approved','fmStatus'=>'Pending'));

            $payout_approved = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Withdraw')
                ->findBy(array('dhStatus'=>'Approved','hrStatus'=>'Approved','fmStatus'=>'Approved'));

            $payout_onhold = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Withdraw')
                ->findBy(array('dhStatus'=>'Approved','hrStatus'=>'Approved','fmStatus'=>'Onhold'));

            $payout_paid = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Withdraw')
                ->findBy(array('dhStatus'=>'Approved','hrStatus'=>'Approved','fmStatus'=>'Paid'));

            return $this->render('LoginLoginBundle:Default:fm_payout.html.twig',array('pending'=>$payout_pending,'approved'=>$payout_approved,'onhold'=>$payout_onhold,'paid'=>$payout_paid));
        }
        else
        {
            $department_details = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Department')
                ->findAll();
            $designation_details = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Designation')
                ->findAll();
            return $this->render('LoginLoginBundle:Default:index.html.twig',array('designation_details'=>$designation_details,'department_details'=>$department_details));

        }
    }
    /**
     * @Route("/hr_optin", name="hr_optin")
     */
    public function hr_optinAction(Request $request)
    {
        if($this->logged()==true)
        {
            $optin_details = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Optin')
                ->findBy(array('dhStatus'=>'Approved','hrStatus'=>'Pending'));

            $optin_approved = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Optin')
                ->findBy(array('dhStatus'=>'Approved','hrStatus'=>'Approved'));

            $optin_rejected = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Optin')
                ->findBy(array('dhStatus'=>'Approved','hrStatus'=>'Rejected'));

            return $this->render('LoginLoginBundle:Default:hr_optin.html.twig',array('optin_rejected'=>$optin_rejected,'optin_approved'=>$optin_approved,'optin_details'=>$optin_details));


        }
        else
        {
            $department_details = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Department')
                ->findAll();
            $designation_details = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Designation')
                ->findAll();
            return $this->render('LoginLoginBundle:Default:index.html.twig',array('designation_details'=>$designation_details,'department_details'=>$department_details));

        }
    }
    /**
     * @Route("/dh_optin_approve/{id}", name="dh_optin_approve")
     */
    public function dh_optin_approveAction($id, Request $request)
    {
        if($this->logged()== true)
        {
            if($request->getMethod() == 'POST')
            {
                $comment = $request->get('comment');
                $status = $request->get('status');
                $em = $this->getDoctrine()->getManager();
                $update = $em->getRepository('LoginLoginBundle:Optin')->find($id);
                /** @var $update Optin */
                $update->setDhStatus($status);
                $update->setDhComment($comment);
                $OI_time = date('Y-m-d H:i:s');
                $update->setDhApprDate($OI_time);
                $em->flush();

                return $this->redirectToRoute('login');
            }


        }
        else{
            return $this->redirectToRoute('login_login_homepage');
        }
    }
    /**
     * @Route("/dh_payout_approve/{id}", name="dh_payout_approve")
     */
    public function dh_payout_approveAction($id, Request $request)
    {
        if($this->logged()== true)
        {
            if($request->getMethod() == 'POST')
            {
                $comment = $request->get('comment');
                $status = $request->get('status');
                $em = $this->getDoctrine()->getManager();
                $update = $em->getRepository('LoginLoginBundle:Withdraw')->find($id);
                /** @var $update Withdraw */
                $update->setDhStatus($status);
                $update->setDhComment($comment);
                $OI_time = date('Y-m-d H:i:s');
                $update->setDhApprovedDate($OI_time);
                $em->flush();

                return $this->redirectToRoute('login');
            }

        }
        else{
            return $this->redirectToRoute('login_login_homepage');
        }
    }
    /**
     * @Route("/hr_payout_approve/{id}", name="hr_payout_approve")
     */
    public function hr_payout_approveAction($id, Request $request)
    {
        if($this->logged()== true)
        {
            if($request->getMethod() == 'POST')
            {
                $comment = $request->get('comment');
                $status = $request->get('status');
                $em = $this->getDoctrine()->getManager();
                $update = $em->getRepository('LoginLoginBundle:Withdraw')->find($id);
                /** @var $update Withdraw */
                $update->setHrStatus($status);
                $update->setHrComment($comment);
                $OI_time = date('Y-m-d H:i:s');
                $update->setHrApprovedDate($OI_time);
                $em->flush();

                return $this->redirectToRoute('login');
            }

        }
        else{
            return $this->redirectToRoute('login_login_homepage');
        }
    }
    /**
     * @Route("/fm_payout_approve/{id}", name="fm_payout_approve")
     */
    public function fm_payout_approveAction($id, Request $request)
    {
        if($this->logged()== true)
        {
                if($request->getMethod() == 'POST')
                {
                    $comment = $request->get('comment');
                    $status = $request->get('status');
                    $em = $this->getDoctrine()->getManager();
                    $update = $em->getRepository('LoginLoginBundle:Withdraw')->find($id);
                    /** @var $update Withdraw */
                    $update->setFmStatus($status);
                    $update->setFmComment($comment);
                    $OI_time = date('Y-m-d H:i:s');
                    $update->setFmApprovedDate($OI_time);
                    $em->flush();

                    return $this->redirectToRoute('login');
                }

        }
        else{
            return $this->redirectToRoute('login_login_homepage');
        }
    }
    /**
     * @Route("/payout_date", name="payout_date")
     */
    public function payout_dateAction(Request $request)
    {
        if($this->logged()== true)
        {
            if($request->getMethod() == 'POST')
            {
                $wr_id=$request->get('wr_id');
                $TM_id= $request->get('TM_id');
                $p_date = $request->get('p_date');

                $em = $this->getDoctrine()->getManager();
                $update = $em->getRepository('LoginLoginBundle:Withdraw')->findOneBy(array('wrId'=>$wr_id));
                /** @var $update Withdraw */
                $update->setPayoutDate($p_date);
                $update->setFmStatus('Onhold');
                $em->flush();

            }

            return $this->redirectToRoute('login');
        }
        else{
            return $this->redirectToRoute('login_login_homepage');
        }
    }
    /**
     * @Route("/fm_payout_pay/{id}", name="fm_payout_pay")
     */
    public function fm_payout_payAction($id, Request $request)
    {
        if($this->logged()== true)
        {
            $em = $this->getDoctrine()->getManager();
            $update = $em->getRepository('LoginLoginBundle:Withdraw')->find($id);
            /** @var $update Withdraw */
            $update->setFmStatus('Paid');
            $OI_time=date('Y-m-d H:i:s');
            $update->setFmApprovedDate($OI_time);
            $em->flush();

            $request_amount = $update->getWrAmount();
            $tm_id = $update->getTmId();

            $em = $this->getDoctrine()->getManager();
            $account = $em->getRepository('LoginLoginBundle:Account')->findOneBy(array('tmId'=>$tm_id));
            /** @var $account Account */

            $base = $account->getAccountBalance();

            $base = $base-$request_amount;
            $account->setAccountBalance($base);

            $transaction = new Transaction();
            $transaction->setTmId($tm_id);
            $transaction->setTrType('Withdrawn');
            $transaction->setTrAmount($request_amount);
            $OI_time=date('Y-m-d H:i:s');
            $transaction->setTrDate($OI_time);


            $em->persist($transaction);
            $em->flush();

            return $this->redirectToRoute('login');

        }
        else{
            return $this->redirectToRoute('login_login_homepage');
        }
    }
    /**
     * @Route("/hr_optin_approve/{id}", name="hr_optin_approve")
     */
    public function hr_optin_approveAction($id, Request $request)
    {
        if($this->logged()== true)
        {
            if($request->getMethod() == 'POST')
            {
                $comment = $request->get('comment');
                $status = $request->get('status');
                $em = $this->getDoctrine()->getManager();
                $update = $em->getRepository('LoginLoginBundle:Optin')->find($id);
                /** @var $update Optin */
                $update->setHrStatus($status);
                $update->setHrComment($comment);
                $OI_time = date('Y-m-d H:i:s');
                $update->setHrApprDate($OI_time);
                $em->flush();
                return $this->redirectToRoute('login');
            }
        }
        else{
            return $this->redirectToRoute('login_login_homepage');
        }
    }
    /**
     * @Route("/activate_account", name="activate_account")
     */
    public function activate_accountAction(Request $request)
    {
        if($this->logged()==true)
        {

            $optin_details = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Optin')
                ->findBy(array('dhStatus'=>'Approved','hrStatus'=>'Approved','optActivation'=>0));

            $num = sizeof($optin_details);
            if($num>0)
            {
                return $this->render('LoginLoginBundle:Default:activate_account.html.twig',array('optin_details'=>$optin_details));
            }
            else
            {
                return $this->render('LoginLoginBundle:Default:activate_account.html.twig',array('optin_error'=>'No Opt-In found','optin_details'=>$optin_details));
            }
        }
        else
        {
            $department_details = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Department')
                ->findAll();
            $designation_details = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Designation')
                ->findAll();
            return $this->render('LoginLoginBundle:Default:index.html.twig',array('designation_details'=>$designation_details,'department_details'=>$department_details));

        }
    }
    /**
     * @Route("/update_doj", name="update_doj")
     */
    public function update_dojAction(Request $request)
    {
        if($this->logged()== true)
        {
            if($request->getMethod() == 'POST')
            {
                $doj= $request->get('doj');
                $tm_id = $request->get('TM_id');

                $em = $this->getDoctrine()->getManager();
                $update = $em->getRepository('LoginLoginBundle:Teammember')->findOneBy(array('itNo'=>$tm_id));
                /** @var $update Teammember */
                $update->setTmDoj($doj);
                $tm_designation = $update->getTmDesignation();
                $em->flush();


                $curr_date = date("Y-m-d");
                $diff = $this->get_dateDifference($doj, $curr_date, '%a');

                $em = $this->getDoctrine()->getManager();
                $update = $em->getRepository('LoginLoginBundle:Optin')->findOneBy(array('tmId'=>$tm_id));
                /** @var $update Optin */
                $update->setOptActivation(1);
                $em->flush();

                $em = $this->getDoctrine()->getManager();
                $update = $em->getRepository('LoginLoginBundle:Account')->findOneBy(array('tmId'=>$tm_id));
                $num = sizeof($update);
                if($num==0)
                {

                    $account = new Account();
                    $account->setTmId($tm_id);
                    $account->setAccountBalance(0);
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($account);
                    $em->flush();
                }

                if($diff>=1460)
                {
                    $repository = $em->getRepository('LoginLoginBundle:Designation');
                    $account_balance =$repository->findOneBy(array('designation'=>$tm_designation));
                    $base_amount = $account_balance->getBase();

                    //updating main balance
                    $em = $this->getDoctrine()->getManager();
                    $update = $em->getRepository('LoginLoginBundle:Account')->findOneBy(array('tmId'=>$tm_id));
                    /** @var $update Account */
                    $update->setAccountBalance($base_amount);
                    $em->flush();
                }

                if ($diff>=1825)//Running 5th year and last month
                {
                    //checking for 5th year entry
                    $em = $this->getDoctrine()->getManager();
                    $update = $em->getRepository('LoginLoginBundle:Loyalty')->findOneBy(array('tmId'=>$tm_id,'loyaltyYear'=>5));
                    $num = sizeof($update);
                    if($num==0)//if not
                    {
                        //fetching balance
                        $em = $this->getDoctrine()->getManager();
                        $update = $em->getRepository('LoginLoginBundle:Account')->findOneBy(array('tmId'=>$tm_id));
                        /** @var $update Account */

                        $bal = intval($update->getAccountBalance());
                        $loyalty_add = $bal*25/100;
                        $new_bal = $bal+$loyalty_add;

                        //adding new entry in loyalty for 5th year
                        $loyalty = new Loyalty();
                        $loyalty->setTmId($tm_id);
                        $loyalty->setLoyaltyYear('5');
                        $loyalty->setAmount($loyalty_add);
                        $OI_time=date('Y-m-d H:i:s');
                        $loyalty->setLoyaltyDate($OI_time);

                        //adding new entry in to transactions
                        $transaction = new Transaction();
                        $transaction->setTmId($tm_id);
                        $transaction->setTrType('Deposited');
                        $transaction->setTrAmount($loyalty_add);
                        $transaction->setTrDate($OI_time);


                        $em=$this->getDoctrine()->getManager();
                        $em->persist($loyalty);
                        $em->persist($transaction);
                        $em->flush();

                        //updating main balance
                        $em = $this->getDoctrine()->getManager();
                        $update = $em->getRepository('LoginLoginBundle:Account')->findOneBy(array('tmId'=>$tm_id));
                        /** @var $update Account */
                        $update->setAccountBalance($new_bal);
                        $em->flush();
                    }
                }

                if ($diff>=2190)//Running 6th year and last month
                {
                    //checking for 6th year entry
                    $em = $this->getDoctrine()->getManager();
                    $update = $em->getRepository('LoginLoginBundle:Loyalty')->findOneBy(array('tmId'=>$tm_id,'loyaltyYear'=>6));
                    $num = sizeof($update);
                    if($num==0)//if not
                    {
                        //fetching balance
                        $em = $this->getDoctrine()->getManager();
                        $update = $em->getRepository('LoginLoginBundle:Account')->findOneBy(array('tmId'=>$tm_id));
                        /** @var $update Account */

                        $bal = intval($update->getAccountBalance());
                        $loyalty_add = $bal*50/100;
                        $new_bal = $bal+$loyalty_add;

                        //adding new entry in loyalty for 6th year
                        $loyalty = new Loyalty();
                        $loyalty->setTmId($tm_id);
                        $loyalty->setLoyaltyYear('6');
                        $loyalty->setAmount($loyalty_add);
                        $OI_time=date('Y-m-d H:i:s');
                        $loyalty->setLoyaltyDate($OI_time);

                        //adding new entry in to transactions
                        $transaction = new Transaction();
                        $transaction->setTmId($tm_id);
                        $transaction->setTrType('Deposited');
                        $transaction->setTrAmount($loyalty_add);
                        $transaction->setTrDate($OI_time);


                        $em=$this->getDoctrine()->getManager();
                        $em->persist($loyalty);
                        $em->persist($transaction);
                        $em->flush();

                        //updaing main balance
                        $em = $this->getDoctrine()->getManager();
                        $update = $em->getRepository('LoginLoginBundle:Account')->findOneBy(array('tmId'=>$tm_id));
                        /** @var $update Account */
                        $update->setAccountBalance($new_bal);
                        $em->flush();
                    }
                }

                if ($diff>=2555)//Running 7th year and last month
                {
                    //checking for 7th year entry
                    $em = $this->getDoctrine()->getManager();
                    $update = $em->getRepository('LoginLoginBundle:Loyalty')->findOneBy(array('tmId'=>$tm_id,'loyaltyYear'=>7));
                    $num = sizeof($update);
                    if($num==0)//if not
                    {
                        //fetching balance
                        $em = $this->getDoctrine()->getManager();
                        $update = $em->getRepository('LoginLoginBundle:Account')->findOneBy(array('tmId'=>$tm_id));
                        /** @var $update Account */

                        $bal = intval($update->getAccountBalance());
                        $loyalty_add = $bal*60/100;
                        $new_bal = $bal+$loyalty_add;

                        //adding new entry in loyalty for 7th year
                        $loyalty = new Loyalty();
                        $loyalty->setTmId($tm_id);
                        $loyalty->setLoyaltyYear('7');
                        $loyalty->setAmount($loyalty_add);
                        $OI_time=date('Y-m-d H:i:s');
                        $loyalty->setLoyaltyDate($OI_time);

                        //adding new entry in to transactions
                        $transaction = new Transaction();
                        $transaction->setTmId($tm_id);
                        $transaction->setTrType('Deposited');
                        $transaction->setTrAmount($loyalty_add);
                        $transaction->setTrDate($OI_time);


                        $em=$this->getDoctrine()->getManager();
                        $em->persist($loyalty);
                        $em->persist($transaction);
                        $em->flush();

                        //updaing main balance
                        $em = $this->getDoctrine()->getManager();
                        $update = $em->getRepository('LoginLoginBundle:Account')->findOneBy(array('tmId'=>$tm_id));
                        /** @var $update Account */
                        $update->setAccountBalance($new_bal);
                        $em->flush();
                    }
                }

                if ($diff>=2920)//Running 8th year and last month
                {
                    //checking for 8th year entry
                    $em = $this->getDoctrine()->getManager();
                    $update = $em->getRepository('LoginLoginBundle:Loyalty')->findOneBy(array('tmId'=>$tm_id,'loyaltyYear'=>8));
                    $num = sizeof($update);
                    if($num==0)//if not
                    {
                        //fetching balance
                        $em = $this->getDoctrine()->getManager();
                        $update = $em->getRepository('LoginLoginBundle:Account')->findOneBy(array('tmId'=>$tm_id));
                        /** @var $update Account */

                        $bal = intval($update->getAccountBalance());
                        $loyalty_add = $bal*70/100;
                        $new_bal = $bal+$loyalty_add;

                        //adding new entry in loyalty for 8th year
                        $loyalty = new Loyalty();
                        $loyalty->setTmId($tm_id);
                        $loyalty->setLoyaltyYear('8');
                        $loyalty->setAmount($loyalty_add);
                        $OI_time=date('Y-m-d H:i:s');
                        $loyalty->setLoyaltyDate($OI_time);

                        //adding new entry in to transactions
                        $transaction = new Transaction();
                        $transaction->setTmId($tm_id);
                        $transaction->setTrType('Deposited');
                        $transaction->setTrAmount($loyalty_add);
                        $transaction->setTrDate($OI_time);


                        $em=$this->getDoctrine()->getManager();
                        $em->persist($loyalty);
                        $em->persist($transaction);
                        $em->flush();

                        //updaing main balance
                        $em = $this->getDoctrine()->getManager();
                        $update = $em->getRepository('LoginLoginBundle:Account')->findOneBy(array('tmId'=>$tm_id));
                        /** @var $update Account */
                        $update->setAccountBalance($new_bal);
                        $em->flush();
                    }
                }

                if ($diff>=3285)//Running 9th year and last month
                {
                    //checking for 9th year entry
                    $em = $this->getDoctrine()->getManager();
                    $update = $em->getRepository('LoginLoginBundle:Loyalty')->findOneBy(array('tmId'=>$tm_id,'loyaltyYear'=>9));
                    $num = sizeof($update);
                    if($num==0)//if not
                    {
                        //fetching balance
                        $em = $this->getDoctrine()->getManager();
                        $update = $em->getRepository('LoginLoginBundle:Account')->findOneBy(array('tmId'=>$tm_id));
                        /** @var $update Account */

                        $bal = intval($update->getAccountBalance());
                        $loyalty_add = $bal*80/100;
                        $new_bal = $bal+$loyalty_add;

                        //adding new entry in loyalty for 9th year
                        $loyalty = new Loyalty();
                        $loyalty->setTmId($tm_id);
                        $loyalty->setLoyaltyYear('9');
                        $loyalty->setAmount($loyalty_add);
                        $OI_time=date('Y-m-d H:i:s');
                        $loyalty->setLoyaltyDate($OI_time);

                        //adding new entry in to transactions
                        $transaction = new Transaction();
                        $transaction->setTmId($tm_id);
                        $transaction->setTrType('Deposited');
                        $transaction->setTrAmount($loyalty_add);
                        $transaction->setTrDate($OI_time);


                        $em=$this->getDoctrine()->getManager();
                        $em->persist($loyalty);
                        $em->persist($transaction);
                        $em->flush();

                        //updaing main balance
                        $em = $this->getDoctrine()->getManager();
                        $update = $em->getRepository('LoginLoginBundle:Account')->findOneBy(array('tmId'=>$tm_id));
                        /** @var $update Account */
                        $update->setAccountBalance($new_bal);
                        $em->flush();
                    }
                }

                if ($diff>=3650)//Running 10th year and last month
                {
                    //checking for 9th year entry
                    $em = $this->getDoctrine()->getManager();
                    $update = $em->getRepository('LoginLoginBundle:Loyalty')->findOneBy(array('tmId'=>$tm_id,'loyaltyYear'=>10));
                    $num = sizeof($update);
                    if($num==0)//if not
                    {
                        //fetching balance
                        $em = $this->getDoctrine()->getManager();
                        $update = $em->getRepository('LoginLoginBundle:Account')->findOneBy(array('tmId'=>$tm_id));
                        /** @var $update Account */

                        $bal = intval($update->getAccountBalance());
                        $loyalty_add = $bal*90/100;
                        $new_bal = $bal+$loyalty_add;

                        //adding new entry in loyalty for 10th year
                        $loyalty = new Loyalty();
                        $loyalty->setTmId($tm_id);
                        $loyalty->setLoyaltyYear('10');
                        $loyalty->setAmount($loyalty_add);
                        $OI_time=date('Y-m-d H:i:s');
                        $loyalty->setLoyaltyDate($OI_time);

                        //adding new entry in to transactions
                        $transaction = new Transaction();
                        $transaction->setTmId($tm_id);
                        $transaction->setTrType('Deposited');
                        $transaction->setTrAmount($loyalty_add);
                        $transaction->setTrDate($OI_time);


                        $em=$this->getDoctrine()->getManager();
                        $em->persist($loyalty);
                        $em->persist($transaction);
                        $em->flush();

                        //updaing main balance
                        $em = $this->getDoctrine()->getManager();
                        $update = $em->getRepository('LoginLoginBundle:Account')->findOneBy(array('tmId'=>$tm_id));
                        /** @var $update Account */
                        $update->setAccountBalance($new_bal);
                        $em->flush();
                    }
                }

                if ($diff>=4015)//Running 11th year and last month
                {
                    //checking for 11th year entry
                    $em = $this->getDoctrine()->getManager();
                    $update = $em->getRepository('LoginLoginBundle:Loyalty')->findOneBy(array('tmId'=>$tm_id,'loyaltyYear'=>11));
                    $num = sizeof($update);
                    if($num==0)//if not
                    {
                        //fetching balance
                        $em = $this->getDoctrine()->getManager();
                        $update = $em->getRepository('LoginLoginBundle:Account')->findOneBy(array('tmId'=>$tm_id));
                        /** @var $update Account */

                        $bal = intval($update->getAccountBalance());
                        $loyalty_add = $bal*100/100;
                        $new_bal = $bal+$loyalty_add;

                        //adding new entry in loyalty for 11th year
                        $loyalty = new Loyalty();
                        $loyalty->setTmId($tm_id);
                        $loyalty->setLoyaltyYear('11');
                        $loyalty->setAmount($loyalty_add);
                        $OI_time=date('Y-m-d H:i:s');
                        $loyalty->setLoyaltyDate($OI_time);

                        //adding new entry in to transactions
                        $transaction = new Transaction();
                        $transaction->setTmId($tm_id);
                        $transaction->setTrType('Deposited');
                        $transaction->setTrAmount($loyalty_add);
                        $transaction->setTrDate($OI_time);


                        $em=$this->getDoctrine()->getManager();
                        $em->persist($loyalty);
                        $em->persist($transaction);
                        $em->flush();

                        //updaing main balance
                        $em = $this->getDoctrine()->getManager();
                        $update = $em->getRepository('LoginLoginBundle:Account')->findOneBy(array('tmId'=>$tm_id));
                        /** @var $update Account */
                        $update->setAccountBalance($new_bal);
                        $em->flush();
                    }
                }
            }

            return $this->redirectToRoute('login');
        }
        else{
            return $this->redirectToRoute('login_login_homepage');
        }
    }
    /**
     * @Route("/profile", name="profile")
     */
    public function profileAction()
    {
        if($this->logged()== true)
        {
            $tm_id=$this->getITID();
            $profile = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Teammember')
                ->findOneBy(array('itNo'=>$tm_id));

            return $this->render('LoginLoginBundle:Default:profile.html.twig',array('profile'=>$profile));

        }
        else{
            return $this->redirectToRoute('login_login_homepage');
        }
    }
    /**
     * @Route("/payout_5th_year", name="payout_5th_year")
     */
    public function payout_5th_yearAction()
    {
        if($this->logged()== true)
        {
            $tm_id=$this->getITID();
            $profile = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Teammember')
                ->findOneBy(array('itNo'=>$tm_id));

            $balance = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Account')
                ->findOneBy(array('tmId'=>$tm_id));

            $balance= $balance->getAccountBalance();

            return $this->render('LoginLoginBundle:Default:payout_5th_year.html.twig',array('balance'=>$balance,'profile'=>$profile));

        }
        else{
            return $this->redirectToRoute('login_login_homepage');
        }
    }
    /**
     * @Route("/payout_6th_year", name="payout_6th_year")
     */
    public function payout_6th_yearAction()
    {
        if($this->logged()== true)
        {
            $tm_id=$this->getITID();
            $profile = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Teammember')
                ->findOneBy(array('itNo'=>$tm_id));

            $balance = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Account')
                ->findOneBy(array('tmId'=>$tm_id));

            $balance= $balance->getAccountBalance();

            return $this->render('LoginLoginBundle:Default:payout_6th_year.html.twig',array('balance'=>$balance,'profile'=>$profile));

        }
        else{
            return $this->redirectToRoute('login_login_homepage');
        }
    }
    /**
     * @Route("/payout_7th_year", name="payout_7th_year")
     */
    public function payout_7th_yearAction()
    {
        if($this->logged()== true)
        {
            $tm_id=$this->getITID();
            $profile = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Teammember')
                ->findOneBy(array('itNo'=>$tm_id));

            $balance = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Account')
                ->findOneBy(array('tmId'=>$tm_id));

            $balance= $balance->getAccountBalance();

            return $this->render('LoginLoginBundle:Default:payout_7th_year.html.twig',array('balance'=>$balance,'profile'=>$profile));

        }
        else{
            return $this->redirectToRoute('login_login_homepage');
        }
    }
    /**
     * @Route("/payout_8th_year", name="payout_8th_year")
     */
    public function payout_8th_yearAction()
    {
        if($this->logged()== true)
        {
            $tm_id=$this->getITID();
            $profile = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Teammember')
                ->findOneBy(array('itNo'=>$tm_id));

            $balance = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Account')
                ->findOneBy(array('tmId'=>$tm_id));

            $balance= $balance->getAccountBalance();

            return $this->render('LoginLoginBundle:Default:payout_8th_year.html.twig',array('balance'=>$balance,'profile'=>$profile));

        }
        else{
            return $this->redirectToRoute('login_login_homepage');
        }
    }
    /**
     * @Route("/payout_9th_year", name="payout_9th_year")
     */
    public function payout_9th_yearAction()
    {
        if($this->logged()== true)
        {
            $tm_id=$this->getITID();
            $profile = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Teammember')
                ->findOneBy(array('itNo'=>$tm_id));

            $balance = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Account')
                ->findOneBy(array('tmId'=>$tm_id));

            $balance= $balance->getAccountBalance();

            return $this->render('LoginLoginBundle:Default:payout_9th_year.html.twig',array('balance'=>$balance,'profile'=>$profile));

        }
        else{
            return $this->redirectToRoute('login_login_homepage');
        }
    }
    /**
     * @Route("/payout_10th_year", name="payout_10th_year")
     */
    public function payout_10th_yearAction()
    {
        if($this->logged()== true)
        {
            $tm_id=$this->getITID();
            $profile = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Teammember')
                ->findOneBy(array('itNo'=>$tm_id));

            $balance = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Account')
                ->findOneBy(array('tmId'=>$tm_id));

            $balance= $balance->getAccountBalance();

            return $this->render('LoginLoginBundle:Default:payout_10th_year.html.twig',array('balance'=>$balance,'profile'=>$profile));

        }
        else{
            return $this->redirectToRoute('login_login_homepage');
        }
    }
    /**
     * @Route("/payout", name="payout")
     */
    public function payoutAction(Request $request)
    {
        if($request->getMethod() == 'POST')
        {
            $tm_id=$request->get('tm_id');
            $percentage = $request->get('per');
            $balance = $request->get('balance');
            $year = $request->get('year');
            $department = $request->get('department');
            $designation = $request->get('designation');

            $loyalty = $balance*$percentage/100;



            $em=$this->getDoctrine()->getManager();
            $repository = $em->getRepository('LoginLoginBundle:Withdraw');
            $new_payout =$repository->findBy(array('tmId'=>$tm_id,'wtYear'=>$year));

            if($new_payout)
            { $tm_id=$this->getITID();
                $profile = $this->getDoctrine()
                    ->getRepository('LoginLoginBundle:Teammember')
                    ->findOneBy(array('itNo'=>$tm_id));

                $balance = $this->getDoctrine()
                    ->getRepository('LoginLoginBundle:Account')
                    ->findOneBy(array('tmId'=>$tm_id));

                $balance= $balance->getAccountBalance();

                return $this->render('LoginLoginBundle:Default:payout_5th_year.html.twig',array('balance'=>$balance,'profile'=>$profile,'error'=>'Sorry you can apply once in a year'));

            }
            else
            {
                $payout = new Withdraw();
                $payout->setTmId($tm_id);
                $payout->setWrAmount($loyalty);
                $payout->setWtYear($year);
                $payout->setTmDesignation($designation);
                $payout->setTmDepartment($department);
                $payout->setDhStatus('Pending');
                $payout->setHrStatus('Pending');
                $payout->setFmStatus('Pending');
                $payout->setPayoutDate('');
                $payout->setDhComment('Not yet reviewed');
                $payout->setHrComment('Not yet reviewed');
                $payout->setFmComment('Not yet updated');


                $OI_time=date('Y-m-d H:i:s');
                $payout->setWrDate($OI_time);


                $em=$this->getDoctrine()->getManager();
                $em->persist($payout);
                $em->flush();

                return $this->redirectToRoute('login_login_homepage');
             }
        }
    }
    /**
     * @Route("/budget_year", name="budget_year")
     */
    public function budget_yearAction()
    {
        if($this->logged()== true)
        {
            $em = $this->getDoctrine()->getManager();
            $update = $em->getRepository('LoginLoginBundle:Teammember')->findAll();
            /** @var $update Teammember */


            return $this->render('LoginLoginBundle:Default:budget_year.html.twig',
                array(
                    'tm' => $update,

                ));

        }
        else{
            return $this->redirectToRoute('login_login_homepage');
        }
    }
    /**
     * @Route("/payout_status_5th", name="payout_status_5th")
     */
    public function payout_status_5thAction()
    {
        if($this->logged()== true)
        {
            $tm_id=$this->getITID();
            $budget_year = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Withdraw')
                ->findOneBy(array('tmId'=>$tm_id,'wtYear'=>6));
            return $this->render('LoginLoginBundle:Default:payout_status.html.twig',array('budget'=>$budget_year));
        }
        else{
            return $this->redirectToRoute('login_login_homepage');
        }
    }
    /**
     * @Route("/payout_status_6th", name="payout_status_6th")
     */
    public function payout_status_6thAction()
    {
        if($this->logged()== true)
        {
            $tm_id=$this->getITID();
            $budget_year = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Withdraw')
                ->findOneBy(array('tmId'=>$tm_id,'wtYear'=>7));
            return $this->render('LoginLoginBundle:Default:payout_status.html.twig',array('budget'=>$budget_year));
        }
        else{
            return $this->redirectToRoute('login_login_homepage');
        }
    }
    /**
     * @Route("/payout_status_7th", name="payout_status_7th")
     */
    public function payout_status_7thAction()
    {
        if($this->logged()== true)
        {
            $tm_id=$this->getITID();
            $budget_year = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Withdraw')
                ->findOneBy(array('tmId'=>$tm_id,'wtYear'=>8));
            return $this->render('LoginLoginBundle:Default:payout_status.html.twig',array('budget'=>$budget_year));
        }
        else{
            return $this->redirectToRoute('login_login_homepage');
        }
    }
    /**
     * @Route("/payout_status_8th", name="payout_status_8th")
     */
    public function payout_status_8thAction()
    {
        if($this->logged()== true)
        {
            $tm_id=$this->getITID();
            $budget_year = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Withdraw')
                ->findOneBy(array('tmId'=>$tm_id,'wtYear'=>9));
            return $this->render('LoginLoginBundle:Default:payout_status.html.twig',array('budget'=>$budget_year));
        }
        else{
            return $this->redirectToRoute('login_login_homepage');
        }
    }
    /**
     * @Route("/payout_status_9th", name="payout_status_9th")
     */
    public function payout_status_9thAction()
    {
        if($this->logged()== true)
        {
            $tm_id=$this->getITID();
            $budget_year = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Withdraw')
                ->findOneBy(array('tmId'=>$tm_id,'wtYear'=>10));
            return $this->render('LoginLoginBundle:Default:payout_status.html.twig',array('budget'=>$budget_year));
        }
        else{
            return $this->redirectToRoute('login_login_homepage');
        }
    }
    /**
     * @Route("/payout_status_10th", name="payout_status_10th")
     */
    public function payout_status_10thAction()
    {
        if($this->logged()== true)
        {
            $tm_id=$this->getITID();
            $budget_year = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Withdraw')
                ->findOneBy(array('tmId'=>$tm_id,'wtYear'=>11));
            return $this->render('LoginLoginBundle:Default:payout_status.html.twig',array('budget'=>$budget_year));
        }
        else{
            return $this->redirectToRoute('login_login_homepage');
        }
    }
    /**
     * @Route("/dh_account", name="dh_account")
     */
    public function dh_accountAction()
    {
        if($this->logged()== true)
        {
            $account = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Account')
                ->findAll();

            return $this->render('LoginLoginBundle:Default:dh_account.html.twig',array('account'=>$account));

        }
        else{
            return $this->redirectToRoute('login_login_homepage');
        }
    }
    /**
     * @Route("/hr_account", name="hr_account")
     */
    public function hr_accountAction()
    {
        if($this->logged()== true)
        {
            $account = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Account')
                ->findAll();

            return $this->render('LoginLoginBundle:Default:hr_account.html.twig',array('account'=>$account));

        }
        else{
            return $this->redirectToRoute('login_login_homepage');
        }
    }
    /**
     * @Route("/fm_account", name="fm_account")
     */
    public function fm_accountAction()
    {
        if($this->logged()== true)
        {
            $account = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Account')
                ->findAll();

            return $this->render('LoginLoginBundle:Default:fm_account.html.twig',array('account'=>$account));

        }
        else{
            return $this->redirectToRoute('login_login_homepage');
        }
    }
    /**
     * @Route("/department", name="department")
     */
    public function departmentAction()
    {
        if($this->logged()== true)
        {
            $department_details = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Department')
                ->findAll();

            return $this->render('LoginLoginBundle:Default:department.html.twig',array('department'=>$department_details));

        }
        else{
            return $this->redirectToRoute('login_login_homepage');
        }
    }
    /**
     * @Route("/dep_add", name="dep_add")
     */
    public function dep_addAction(Request $request)
    {
        if($this->logged()== true)
        {
            if($request->getMethod()== 'POST')
            {
                $dep_name = $request->get('depname');
                $em = $this->getDoctrine()->getManager();
                $dep = new Department();
                $dep->setDepartmentName($dep_name);
                $em->persist($dep);
                $em->flush();

                $department_details = $this->getDoctrine()
                    ->getRepository('LoginLoginBundle:Department')
                    ->findAll();

                return $this->render('LoginLoginBundle:Default:department.html.twig',array('department'=>$department_details));


            }
        }
        else{
            return $this->redirectToRoute('login_login_homepage');
        }

    }
    /**
     * @Route("/dep_remove/{id}", name="dep_remove")
     */
    public function dep_removeAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $dep = $em->getRepository('LoginLoginBundle:Department')->find($id);
        $em->remove($dep);
        $em->flush();

        $department_details = $this->getDoctrine()
            ->getRepository('LoginLoginBundle:Department')
            ->findAll();

        return $this->render('LoginLoginBundle:Default:department.html.twig',
            array(
                'department'=>$department_details
            ));
    }
    /**
     * @Route("/lrp", name="lrp")
     */
    public function lrpAction()
    {
        if($this->logged()== true)
        {
            $lrp_details = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Lrpmodel')
                ->findAll();

            return $this->render('LoginLoginBundle:Default:lrp.html.twig',array('lrp'=>$lrp_details));

        }
        else{
            return $this->redirectToRoute('login_login_homepage');
        }
    }
    /**
     * @Route("/lrp_edit/{id}", name="lrp_edit")
     */
    public function lrp_editAction($id, Request $request)
    {
        if($this->logged()== true)
        {
            $lrp_list = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Lrpmodel')
                ->find($id);
            return $this->render('LoginLoginBundle:Default:lrp_edit.html.twig',array('lrp'=>$lrp_list));

        }
        else{
            return $this->redirectToRoute('login_login_homepage');
        }
    }
    /**
     * @Route("/lrp_edit_form/{id}", name="lrp_edit_form")
     */
    public function lrp_edit_formAction($id, Request $request)
    {

        if($this->logged()== true)
        {
            $lrp_list = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Lrpmodel')
                ->find($id);
            return $this->render('LoginLoginBundle:Default:lrp_edit.html.twig',array('lrp'=>$lrp_list));
        }
        else{
            return $this->redirectToRoute('login_login_homepage');
        }

    }
    /**
     * @Route("/lrp_edit_save/{id}", name="lrp_edit_save")
     */
    public function lrp_edit_saveAction($id, Request $request)
    {
        if($this->logged()== true)
        {
            if($request->getMethod()== 'POST')
            {

                $loyalty = $request->get('loyalty');
                $min = $request->get('min');
                $max = $request->get('max');

                $em = $this->getDoctrine()->getManager();
                $lrp_update = $em->getRepository('LoginLoginBundle:Lrpmodel')->find($id);
                /** @var $lrp_update Lrpmodel */
                $lrp_update->setLrpRewardPercentage($loyalty);
                $lrp_update->setMaxPercentage($max);
                $lrp_update->setMinPercentage($min);
                $em->flush();

                $lrp_list = $this->getDoctrine()
                    ->getRepository('LoginLoginBundle:Lrpmodel')
                    ->findAll();
                return $this->render('LoginLoginBundle:Default:lrp.html.twig',array('lrp'=>$lrp_list));
            }
        }
        else{
            return $this->redirectToRoute('login_login_homepage');
        }

    }
    /**
     * @Route("/users", name="users")
     */
    public function usersAction()
    {
        if($this->logged()== true)
        {
            $user_details = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Teammember')
                ->findAll();
            return $this->render('LoginLoginBundle:Default:app_users.html.twig',array('user'=>$user_details));
        }
        else{
            return $this->redirectToRoute('login_login_homepage');
        }
    }
    /**
     * @Route("/login_user", name="login_user")
     */
    public function login_userAction()
    {
        if($this->logged()== true)
        {
            $user_details = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:User')
                ->findAll();

            $staff_details = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Teammember')
                ->findAll();
            return $this->render('LoginLoginBundle:Default:login_users.html.twig',
                array(
                    'user_logins'=>$user_details,
                    'staff'=>$staff_details
                ));
        }
        else{
            return $this->redirectToRoute('login_login_homepage');
        }
    }
    /**
     * @Route("/login_user_edit/{id}", name="login_user_edit")
     */
    public function login_user_editAction($id, Request $request)
    {

        if($this->logged()== true)
        {
            $user_list = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:User')
                ->find($id);
            return $this->render('LoginLoginBundle:Default:login_user_edit.html.twig',array('user_list'=>$user_list));
        }
        else{
            return $this->redirectToRoute('login_login_homepage');
        }

    }
    /**
     * @Route("/login_user_edit_save", name="login_user_edit_save")
     */
    public function login_user_edit_saveAction(Request $request)
    {
        if($this->logged()== true)
        {
            if($request->getMethod()== 'POST')
            {
                $staff_id = $request->get('staff_id');
                $email = $request->get('email');
                $role = $request->get('role');

                $em = $this->getDoctrine()->getManager();
                $user_add = new User();
                $user_add->setTeamId($staff_id);
                $user_add->setUserName($email);
                $user_add->setUserPassword('demo');
                $user_add->setUserRole($role);
                $em->persist($user_add);
                $em->flush();

                return $this->redirectToRoute('login_user');

            }
        }
        else{
            return $this->redirectToRoute('login_login_homepage');
        }

    }
    /**
     * @Route("/login_user_remove/{id}", name="login_user_remove")
     */
    public function login_user_removeAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $dep = $em->getRepository('LoginLoginBundle:User')->find($id);
        $em->remove($dep);
        $em->flush();

        return $this->redirectToRoute('login_user');
    }
    /**
     * @Route("/designation", name="designation")
     */
    public function designationAction()
    {
        if($this->logged()== true)
        {
            $designation_details = $this->getDoctrine()
                ->getRepository('LoginLoginBundle:Designation')
                ->findAll();

            return $this->render('LoginLoginBundle:Default:designation.html.twig',array('designation'=>$designation_details));

        }
        else{
            return $this->redirectToRoute('login_login_homepage');
        }
    }
    /**
     * @Route("/des_add", name="des_add")
     */
    public function des_addAction(Request $request)
    {
        if($this->logged()== true)
        {
            if($request->getMethod()== 'POST')
            {
                $des_name = $request->get('designation');
                $base_amount = $request->get('base_amount');
                $em = $this->getDoctrine()->getManager();
                $des = new Designation();
                $des->setDesignation($des_name);
                $des->setBase($base_amount);
                $em->persist($des);
                $em->flush();
                $designation_details = $this->getDoctrine()
                    ->getRepository('LoginLoginBundle:Designation')
                    ->findAll();

                return $this->render('LoginLoginBundle:Default:designation.html.twig',array('designation'=>$designation_details));

            }
        }
        else{
            return $this->redirectToRoute('login_login_homepage');
        }

    }
    /**
     * @Route("/des_remove/{id}", name="des_remove")
     */
    public function des_removeAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $dep = $em->getRepository('LoginLoginBundle:Designation')->find($id);
        $em->remove($dep);
        $em->flush();

        $designation_details = $this->getDoctrine()
            ->getRepository('LoginLoginBundle:Designation')
            ->findAll();

        return $this->render('LoginLoginBundle:Default:designation.html.twig',array('designation'=>$designation_details));

    }
}
