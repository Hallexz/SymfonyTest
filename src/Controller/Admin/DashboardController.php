<?php


namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends AbstractDashboardController
{
    /**
     * @Route("/admin/login", name="admin_dashboard")
     */
    public function index(): Response
    {
        return $this->render('book/dashboard.html.twig');
    }
}
