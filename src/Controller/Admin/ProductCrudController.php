<?php

namespace App\Controller\Admin;

use DateTimeImmutable;
use App\Entity\Product;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PhpParser\Builder\Function_;

class ProductCrudController extends AbstractCrudController
{

    public const ACTION_DUPLICATE = 'duplicate';
    public const PRODUCTS_BASE_PATH = 'upload/images/products';
    public const PRODUCTS_UPLOAD_DIR  = 'public/upload/images/products';


    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $duplicate = Action::new(self::ACTION_DUPLICATE)->linkToCrudAction('duplicateProduct')
                                                        //Ajouter une class CSS au btn
                                                        ->setCssClass('btn btn-info');

        return $actions->add(Crud::PAGE_EDIT, $duplicate)
                        ->reorder(Crud::PAGE_EDIT, [self::ACTION_DUPLICATE, Action::SAVE_AND_RETURN]);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),

            // TextField::new('name', 'Label')->setRequired(false) ne pas rendre le champ obligatoire
            TextField::new('name', 'Label'),

            TextEditorField::new('description'),

            //->setCurrency('EUR'), pour donner une valeur de base ici EUR
            MoneyField::new('price')->setCurrency('EUR'),

            //setBasePath, pour définir le chemin de base des images
            ImageField::new('image')->setBasePath(self::PRODUCTS_BASE_PATH)
            //->setUploadDir, pour définir le répertoire de téléchargement
                                    ->setUploadDir(self::PRODUCTS_UPLOAD_DIR)
                                    ->setSortable(false),
            BooleanField::new('active'),

            //->setQueryBuilder(function (QueryBuilder $queryBuilder) {
                // $queryBuilder->where('entity.active = true');
                // } pour permettre l'affichage de tte les category active
            AssociationField::new('category')->setQueryBuilder(function (QueryBuilder $queryBuilder) {
                $queryBuilder->where('entity.active = true');
            }),

            
            DateTimeField::new('updatedAt')->hideOnForm(),
            DateTimeField::new('createdAt')->hideOnForm(),
            
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
    
        if(!$entityInstance instanceof Product) return; 

        $entityInstance->setCreatedAt(new \DateTimeImmutable());
        
        //On appel la methode parente qui va persist et flush depuis l'abstract controller
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        
        if(!$entityInstance instanceof Product) return; 

        $entityInstance->setUpdatedAt(new \DateTimeImmutable());
        
        //On appel la methode parente qui va persist et flush depuis l'abstract controller
        parent::updateEntity($entityManager, $entityInstance);

    }

    public function duplicateProduct(AdminContext $context,AdminUrlGenerator $adminUrlGenerator,EntityManagerInterface $entityManager
    ): Response {
        /** @var Product $product */
        $product = $context->getEntity()->getInstance();

        $duplicatedProduct = clone $product;

        parent::persistEntity($entityManager, $duplicatedProduct);

        $url = $adminUrlGenerator->setController(self::class)
            ->setAction(Action::DETAIL)
            ->setEntityId($duplicatedProduct->getId())
            ->generateUrl();

        return $this->redirect($url);
    }
    
}
