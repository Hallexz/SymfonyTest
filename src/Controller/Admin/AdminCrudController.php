<?php

namespace App\Controller\Admin;

use App\Entity\Admin;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;

class AdminCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Admin::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('email'),
            TextField::new('password')->setFormTypeOption('attr', ['type' => 'password']),
            ChoiceField::new('roles')->setChoices([
                'Admin' => 'ROLE_ADMIN',
                'User' => 'ROLE_USER'
            ])->allowMultipleChoices(),
        ];
    }
}
