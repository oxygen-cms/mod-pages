<?php

namespace Oxygen\Pages\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;
use Oxygen\Data\Behaviour\Accessors;
use Oxygen\Data\Behaviour\Fillable;
use Oxygen\Data\Behaviour\PrimaryKey;
use Oxygen\Data\Behaviour\Publishes;
use Oxygen\Data\Behaviour\Timestamps;
use Oxygen\Data\Behaviour\SoftDeletes;
use Oxygen\Data\Behaviour\Versions;
use Oxygen\Data\Validation\Validatable;

/**
 * @ORM\Entity
 * @ORM\Table(name="partials")
 * @ORM\HasLifecycleCallbacks
 */

class Partial implements Validatable {

    use PrimaryKey, Timestamps, SoftDeletes, Versions, Publishes {
        Publishes::__clone insteadof PrimaryKey;
    }
    use Accessors, Fillable;

    const STAGE_DRAFT = 0;
    const STAGE_PUBLISHED = 1;

    /**
     * @ORM\Column(name="`key`", type="string")
     */

    protected $key;

    /**
     * @ORM\Column(type="string")
     */

    protected $title;

    /**
     * @ORM\Column(type="string", nullable=true)
     */

    protected $author;

    /**
     * @ORM\Column(type="text", nullable=true)
     */

    protected $content;

    /**
     * @ORM\OneToMany(targetEntity="Oxygen\Pages\Entity\Partial", mappedBy="headVersion", cascade={"persist", "remove", "merge"})
     * @ORM\OrderBy({ "updatedAt" = "DESC" })
     */

    private $versions;

    /**
     * @ORM\ManyToOne(targetEntity="Oxygen\Pages\Entity\Partial",  inversedBy="versions")
     * @ORM\JoinColumn(name="head_version", referencedColumnName="id")
     */

    private $headVersion;

    /**
     * Constructs a new Partial.
     */
    public function __construct() {
        $this->versions = new ArrayCollection();
    }

    /**
     * Returns an array of validation rules used to validate the model.
     *
     * @return array
     */
    public function getValidationRules() {
        return [
            'key' =>[
                'required',
                'alpha_dot',
                'max:50',
                $this->getUniqueValidationRule('key')
            ],
            'title' => [
                'required',
                'max:255'
            ],
            'author' => [
                'name',
                'max:255'
            ],
            'stage' => [
                'in:0,1'
            ]
        ];
    }

    /**
     * Returns the fields that should be fillable.
     *
     * @return array
     */

    protected function getFillableFields() {
        return ['key', 'title', 'author', 'content', 'stage'];
    }

}