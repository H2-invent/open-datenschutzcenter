<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 06.06.2020
 * Time: 19:01
 */

namespace App\Service;


use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpWord\Writer\Word2007;
use Symfony\Component\Form\FormFactoryInterface;


class BerichtService
{
    private $em;
    private $word;

    public function __construct(EntityManagerInterface $entityManager, FormFactoryInterface $formBuilder, Word2007 $word)
    {
        $this->em = $entityManager;
        $this->word = $word;
    }

    function information($data, User $user)
    {

    }
}
