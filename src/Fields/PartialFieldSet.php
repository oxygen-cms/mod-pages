<?php


namespace OxygenModule\Pages\Fields;

use Oxygen\Core\Form\FieldSet;
use OxygenModule\Pages\Entity\Partial;

class PartialFieldSet extends FieldSet {

    /**
     * Creates the fields in the set.
     *
     * @return array
     */
    public function createFields() {
        return $this->makeFields(
            [
                'name'              => 'id',
                'label'             => 'ID'
            ],
            [
                'name'              => 'key',
                'editable'          => true
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
                'name'              => 'content',
                'type'              => 'editor',
                'editable'          => true,
                'options'           => [
                    'language'      => 'php'
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
                'name'      => 'stage',
                'type'      => 'select',
                'editable'  => true,
                'options'   => [
                    Partial::STAGE_DRAFT => 'Draft',
                    Partial::STAGE_PUBLISHED => 'Published',
                ]
            ]
        );
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