<?php


namespace OxygenModule\Pages\Fields;

use Oxygen\Auth\Entity\User;
use Oxygen\Core\Form\ContentFieldName;
use Oxygen\Core\Form\FieldSet;
use OxygenModule\Pages\Entity\Page;

class PageFieldSet extends FieldSet implements ContentFieldName {

    /**
     * Creates the fields in the set.
     *
     * @return array
     */
    public function createFields() {
        $userDisplay = function(User $user) {
            return $user->getFullName();
        };

        return $this->makeFields([
            [
                'name'              => 'id',
                'label'             => 'ID'
            ],
            [
                'name'              => 'content',
                'type'              => 'editor',
                'editable'          => true,
                'options'           => [
                    'language'      => 'twig',
                    'fullWidth'     => true
                ]
            ],
            [
                'name'              => 'title',
                'editable'          => true
            ],
            [
                'name'              => 'slugPart',
                'label'             => 'URL Part',
                'editable'          => true,
                'description'       => __('oxygen/mod-pages::descriptions.page.slug')
            ],
            [
                'name'              => 'description',
                'type'              => 'textarea',
                'editable'          => true,
                'attributes'        => [ 'rows' => 3 ]
            ],
            [
                'name'              => 'tags',
                'type'              => 'textarea',
                'description'       => __('oxygen/mod-pages::descriptions.page.tags'),
                'editable'          => true,
                'attributes'        => [ 'rows' => 2 ]
            ],
            [
                'name'              => 'meta',
                'type'              => 'editor-mini',
                'description'       => __('oxygen/mod-pages::descriptions.page.meta'),
                'editable'          => true,
                'options'           => [
                    'language'      => 'html',
                    'mode'          => 'code'
                ]
            ],
            [
                'name'              => 'options',
                'type'              => 'editor-mini',
                'editable'          => true,
                'options'           => [
                    'language'      => 'json',
                    'mode'          => 'code'
                ]
            ],
            [
                'name'      => 'createdAt',
                'type'      => 'date'
            ],
            [
                'name'      => 'createdBy',
                'type'      => 'relationship',
                'options'   => [ 'displayFn'  => $userDisplay ],
                'editable'  => false
            ],
            [
                'name'      => 'updatedAt',
                'type'      => 'date'
            ],
            [
                'name'      => 'updatedBy',
                'type'      => 'relationship',
                'options'   => [ 'displayFn'  => $userDisplay ],
                'editable'  => false
            ],
            [
                'name'      => 'deletedAt',
                'type'      => 'date'
            ],
            [
                'name'      => 'headVersion',
                'label'     => 'Head Version',
                'type'      => 'relationship',
                'editable'  => false,
                'options'   => [
                    'type'       => 'manyToOne',
                    'blueprint'  => 'Page',
                    'allowNull' => true,
                    'items' => function() {
                        $repo = resolve('Oxygen\Pages\Repository\PageRepositoryInterface');
                        return $repo->columns(['id', 'title']);
                    }
                ]
            ],
            [
                'name' => 'stage',
                'type' => 'select',
                'editable' => false,
                'options' => [
                    Page::STAGE_DRAFT => 'Draft',
                    Page::STAGE_PENDING_REVIEW => 'Pending Review',
                    Page::STAGE_PUBLISHED => 'Published',
                    Page::STAGE_ARCHIVED => 'Archived'
                ]
            ]
        ]);
    }

    /**
     * Returns the name of the title field.
     *
     * @return mixed
     */
    public function getTitleFieldName() {
        return 'title';
    }

    /**
     * Returns the name of the field that contains the content.
     *
     * @return string
     */
    public function getContentFieldName() {
        return 'content';
    }
}
