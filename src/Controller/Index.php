<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

class Index extends AbstractController
{
    #[Route('/')]
    public function index()
    {
        return $this->render('base.html.twig');
    }
}
