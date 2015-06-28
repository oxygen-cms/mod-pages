<?php


namespace OxygenModule\Pages\Fields;

use Lang;

use Oxygen\Core\Form\FieldSet;
use OxygenModule\Pages\Entity\Page;

class PageFieldSet extends FieldSet {

    /**
     * Creates the fields in the set.
     *
     * @return array
     */
    public function createFields() {
        return $this->makeFields([
            [
                'name'              => 'id',
                'label'             => 'ID'
            ],
            [
                'name'              => 'slug',
                'editable'          => true,
                'description'       => Lang::get('oxygen/mod-pages::descriptions.page.slug')
            ],
            [
                'name'              => 'title',
                'editable'          => true
            ],
            [
                'name'              => 'author',
                'editable'          => true
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
                'editable'          => true,
                'attributes'        => [ 'rows' => 2 ]
            ],
            [
                'name'              => 'meta',
                'type'              => 'editor-mini',
                'editable'          => true,
                'options'           => [
                    'language'      => 'html',
                    'mode'          => 'code'
                ]
            ],
            [
                'name'              => 'content',
                'type'              => 'editor',
                'editable'          => true,
                'options'           => [
                    'language'      => 'php'
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
                'name'      => 'updatedAt',
                'type'      => 'date'
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
                        $repo = App::make('Oxygen\Pages\Repository\PageRepositoryInterface');
                        return $repo->columns(['id', 'title']);
                    }
                ]
            ],
            [
                'name' => 'stage',
                'type' => 'select',
                'editable' => true,
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
}