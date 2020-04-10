<?php

namespace OxygenModule\Pages\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;
use Oxygen\Data\Behaviour\Accessors;
use Oxygen\Data\Behaviour\CacheInvalidator;
use Oxygen\Data\Behaviour\CacheInvalidatorInterface;
use Oxygen\Data\Behaviour\Fillable;
use Oxygen\Data\Behaviour\PrimaryKey;
use Oxygen\Data\Behaviour\PrimaryKeyInterface;
use Oxygen\Data\Behaviour\Publishes;
use Oxygen\Data\Behaviour\StatusIconInterface;
use Oxygen\Data\Behaviour\Timestamps;
use Oxygen\Data\Behaviour\SoftDeletes;
use Oxygen\Data\Behaviour\Versions;
use Oxygen\Data\Validation\Validatable;
use Oxygen\Data\Behaviour\Searchable;

/**
 * @ORM\Entity
 * @ORM\Table(name="partials")
 * @ORM\HasLifecycleCallbacks
 */

class Partial implements PrimaryKeyInterface, Validatable, CacheInvalidatorInterface, Searchable, StatusIconInterface {

    use PrimaryKey, Timestamps, SoftDeletes, Versions, Publishes, CacheInvalidator {
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
     * @ORM\OneToMany(targetEntity="OxygenModule\Pages\Entity\Partial", mappedBy="headVersion", cascade={"persist", "remove", "merge"})
     * @ORM\OrderBy({ "updatedAt" = "DESC" })
     */

    private $versions;

    /**
     * @ORM\ManyToOne(targetEntity="OxygenModule\Pages\Entity\Partial",  inversedBy="versions")
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
            'content' => [
                'blade_template'
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

    /**
     * Returns the fields that should be searched.
     *
     * @return array
     */
    public static function getSearchableFields() {
        return ['key', 'title'];
    }

    /**
     * Retrieves the status icon for the model.
     *
     * @return string
     */
    public function getStatusIcon() {
        if($this->stage == self::STAGE_DRAFT) {
            return 'pencil-square';
        } else {
            return 'globe-asia';
        }
    }
}
