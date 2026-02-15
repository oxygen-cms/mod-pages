<?php

namespace OxygenModule\Pages\Entity;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;
use Oxygen\Core\Templating\Templatable;
use Oxygen\Data\Behaviour\Accessors;
use Oxygen\Auth\Entity\Blames;
use Oxygen\Data\Behaviour\Blameable;
use Oxygen\Data\Behaviour\CacheInvalidator;
use Oxygen\Data\Behaviour\CacheInvalidatorInterface;
use Oxygen\Data\Behaviour\Fillable;
use Oxygen\Data\Behaviour\HasUpdatedAt;
use Oxygen\Data\Behaviour\PrimaryKey;
use Oxygen\Data\Behaviour\PrimaryKeyInterface;
use Oxygen\Data\Behaviour\Publishable;
use Oxygen\Data\Behaviour\Publishes;
use Oxygen\Data\Behaviour\StatusIconInterface;
use Oxygen\Data\Behaviour\Timestamps;
use Oxygen\Data\Behaviour\SoftDeletes;
use Oxygen\Data\Behaviour\Versionable;
use Oxygen\Data\Behaviour\Versions;
use Oxygen\Data\Validation\Validatable;
use Oxygen\Data\Behaviour\Searchable;

/**
 * @ORM\Entity
 * @ORM\Table(name="partials")
 * @ORM\HasLifecycleCallbacks
 */

class Partial implements PrimaryKeyInterface, Validatable, CacheInvalidatorInterface, Searchable, StatusIconInterface, Templatable, Versionable, HasUpdatedAt, Publishable, Blameable {

    use PrimaryKey, Timestamps, SoftDeletes, Versions, Publishes, CacheInvalidator, Blames;
    use Accessors, Fillable;

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
     * @ORM\Column(type="json", nullable=true)
     */
    protected $richContent;

    /**
     * @ORM\OneToMany(targetEntity="OxygenModule\Pages\Entity\Partial", mappedBy="headVersion", cascade={"persist", "remove", "merge"})
     * @ORM\OrderBy({ "updatedAt" = "DESC" })
     */
    private $versions;

    /**
     * @ORM\ManyToOne(targetEntity="OxygenModule\Pages\Entity\Partial",  inversedBy="versions", cascade={"persist"})
     * @ORM\JoinColumn(name="head_version", referencedColumnName="id")
     */
    private ?Partial $headVersion;

    /**
     * Constructs a new Partial.
     */
    public function __construct() {
        $this->versions = new ArrayCollection();
        $this->stage = Partial::STAGE_DRAFT;
        $this->headVersion = null;
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
                'nullable',
                'name',
                'max:255'
            ],
            'content' => [
                'twig_template'
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
    public function getFillableFields(): array {
        return ['key', 'title', 'author', 'content', 'richContent'];
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

    /**
     * @return string
     */
    public function getResourceType() {
        return 'partials';
    }

    /**
     * @return string
     */
    public function getResourceKey() {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getTemplateCode() {
        return $this->content;
    }

    /**
     * @return array
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'title' => $this->title,
            'content' => $this->content,
            'richContent' => $this->richContent,
            'stage' => $this->stage,
            'headVersion' => $this->headVersion === null ? null : $this->headVersion->getId(),
            'createdAt' => $this->createdAt !== null ? $this->createdAt->format(DateTimeInterface::ATOM) : null,
            'createdBy' => $this->getCreatedBy() ? $this->getCreatedBy()->toArray() : null,
            'updatedAt' => $this->updatedAt !== null ? $this->updatedAt->format(DateTimeInterface::ATOM) : null,
            'updatedBy' => $this->getUpdatedBy() ? $this->getUpdatedBy()->toArray() : null,
            'deletedAt' => $this->deletedAt !== null ? $this->deletedAt->format(DateTimeInterface::ATOM) : null,
        ];
    }
}
