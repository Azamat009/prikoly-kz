<?php

namespace App\Controller\Admin;

use App\Entity\Video;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class VideoCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Video::class;
    }

    public function configureCrud(Crud $crud): Crud{
        return $crud
            ->setEntityLabelInSingular('Video')
            ->setEntityLabelInPlural('Videos')
            ->setSearchFields(['id', 'title', 'description', 'filePath', 'createdAt'])
            ->setDefaultSort(['createdAt' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable{
        yield IdField::new('id', 'ID');
        yield TextField::new('title', 'Title');
        yield TextField::new('description', 'Description');
        yield TextField::new('filePath', 'Video URL');
        yield DateField::new('createdAt', 'Created At');

    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
