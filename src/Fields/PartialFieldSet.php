<?php


namespace OxygenModule\Pages\Fields;

use Oxygen\Auth\Entity\User;
use Oxygen\Core\Form\ContentFieldName;
use Oxygen\Core\Form\FieldSet;
use OxygenModule\Pages\Entity\Partial;

class PartialFieldSet extends FieldSet implements ContentFieldName {

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
                'name'              => 'key',
                'editable'          => true
            ],
            [
                'name'              => 'title',
                'editable'          => true
            ],
            [
                'name'              => 'content',
                'type'              => 'editor',
                'editable'          => true,
                'options'           => [
                    'language'      => 'twig'
                ]
            ],
            [
                'name'              => 'richContent',
                'type'              => 'editor',
                'editable'          => false,
                'options'           => [
                    'language'      => 'json'
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
                'name'      => 'stage',
                'type'      => 'select',
                'editable'  => false,
                'options'   => [
                    Partial::STAGE_DRAFT => 'Draft',
                    Partial::STAGE_PUBLISHED => 'Published',
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