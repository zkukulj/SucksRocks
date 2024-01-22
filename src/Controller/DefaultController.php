<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", methods: ['GET'], name="default")
     */
    public function index(): Response
    {
        return new Response('
            <h1>For testing JSON SucksRocks API go to</h1>
            <p><a href="score?term=php&from=GitHub">JSON scores API</a></p>
        
        ');
    }
}