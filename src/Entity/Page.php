<?php

namespace OxygenModule\Pages\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;
use Oxygen\Core\Templating\Templatable;
use Oxygen\Data\Behaviour\Accessors;
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
 * @ORM\Table(name="pages")
 * @ORM\HasLifecycleCallbacks
 */

class Page implements PrimaryKeyInterface, Validatable, CacheInvalidatorInterface, Searchable, StatusIconInterface, Templatable, Versionable, HasUpdatedAt, Publishable {

    use PrimaryKey, Timestamps, SoftDeletes, Versions, Publishes, CacheInvalidator;
    use Accessors, Fillable;

    const STAGE_PENDING_REVIEW = 1;
    const STAGE_PUBLISHED = 2;
    const STAGE_ARCHIVED = 3;

    /**
     * @ORM\Column(type="string")
     */
    protected $slug;

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

    protected $description;

    /**
     * @ORM\Column(type="text", nullable=true)
     */

    protected $tags;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $meta;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $content;

    /**
     * @ORM\Column(type="text")
     */
    protected $options;

    /**
     * @ORM\OneToMany(targetEntity="OxygenModule\Pages\Entity\Page", mappedBy="headVersion", cascade={"persist", "remove", "merge"})
     * @ORM\OrderBy({ "updatedAt" = "DESC" })
     */
    private $versions;

    /**
     * @ORM\ManyToOne(targetEntity="OxygenModule\Pages\Entity\Page",  inversedBy="versions")
     * @ORM\JoinColumn(name="head_version", referencedColumnName="id")
     */
    private $headVersion;

    /**
     * Constructs a new Page.
     */
    public function __construct() {
        $this->setOptions([]);
        $this->versions = new ArrayCollection();
    }

    /**
     * Returns an array of validation rules used to validate the model.
     *
     * @return array
     */
    public function getValidationRules() {
        return [
            'slug' => [
                'required',
                'slug',
                'max:255',
                $this->getUniqueValidationRule('slug')
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
                'twig_template'
            ],
            'stage' => [
                'in:0,1,2,3'
            ]
        ];
    }

    /**
     * Returns the fields that should be fillable.
     *
     * @return array
     */
    public function getFillableFields(): array {
        return ['slug', 'title', 'author', 'description', 'tags', 'meta', 'content', 'options', 'stage'];
    }

    /**
     * Returns the options of the page.
     *
     * @return array
     */
    public function getOptions() {
        return json_decode($this->options, true);
    }

    /**
     * Sets the page options.
     *
     * @param  array|string $options
     * @return $this
     */
    public function setOptions($options) {
        $this->options = is_string($options) ? $options : json_encode($options, JSON_PRETTY_PRINT);
        return  $this;
    }

    /**
     * Returns the fields that should be searched.
     *
     * @return array
     */
    public static function getSearchableFields() {
        return ['slug', 'title', 'content'];
    }

    /**
     * Retrieves the status icon for the model.
     *
     * @return string
     */
    public function getStatusIcon() {
        if($this->stage == self::STAGE_ARCHIVED) {
            return 'archive';
        } else if($this->stage == self::STAGE_DRAFT) {
            return 'pencil-square';
        } else if($this->stage == self::STAGE_PENDING_REVIEW) {
            return 'user-edit';
        } else if($this->stage == self::STAGE_PUBLISHED) {
            return 'globe-asia';
        } else {
            return null;
        }
    }

    /**
     * @return string
     */
    public function getResourceType() {
        return 'pages';
    }

    /**
     * @return string
     */
    public function getResourceKey() {
        return $this->slug;
    }

    /**
     * @return string
     */
    public function getTemplateCode() {
        return $this->content;
    }

}
