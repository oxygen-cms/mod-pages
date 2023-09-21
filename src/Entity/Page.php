<?php

namespace OxygenModule\Pages\Entity;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping AS ORM;
use Exception;
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
use Oxygen\Data\Validation\Rules\Unique;
use Oxygen\Data\Validation\Validatable;
use Oxygen\Data\Behaviour\Searchable;
use Oxygen\Data\Validation\ValidationService;
use OxygenModule\Pages\ParentChildCycleException;

/**
 * @ORM\Entity
 * @ORM\Table(name="pages")
 * @ORM\HasLifecycleCallbacks
 */

class Page implements PrimaryKeyInterface, Validatable, CacheInvalidatorInterface, Searchable, StatusIconInterface, Templatable, Versionable, HasUpdatedAt, Publishable, Blameable {

    use PrimaryKey, Timestamps, SoftDeletes, Versions, Publishes, CacheInvalidator, Blames;
    use Accessors, Fillable;

    const STAGE_PENDING_REVIEW = 1;
    const STAGE_PUBLISHED = 2;
    const STAGE_ARCHIVED = 3;

    /**
     * @ORM\Column(type="string")
     */
    protected $slugPart;

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
     * @ORM\Column(type="json", nullable=true)
     */
    protected $richContent;

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
    private ?Page $headVersion = null;

    /**
     * @ORM\ManyToOne(targetEntity="OxygenModule\Pages\Entity\Page",  inversedBy="children")
     * @ORM\JoinColumn(name="parent", referencedColumnName="id")
     */
    protected ?Page $parent = null;

    /**
     * @ORM\OneToMany(targetEntity="OxygenModule\Pages\Entity\Page", mappedBy="parent", cascade={"persist"})
     * @ORM\OrderBy({ "slugPart" = "ASC" })
     */
    private $children;

    /**
     * Constructs a new Page.
     */
    public function __construct() {
        $this->setOptions([]);
        $this->stage = Publishable::STAGE_DRAFT;
        $this->versions = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

    /**
     * Returns an array of validation rules used to validate the model.
     *
     * @return array
     */
    public function getValidationRules() {
        return [
            'slugPart' => [
                'required',
                'regex:/^(\\/|[a-z0-9_\-]+)$/i',
                'max:255',
                $this->getUniqueSlugValidationRule(),
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
                'in:0,1,2,3'
            ]
        ];
    }

    /**
     * `name` must be unique, amongst directories that are siblings.
     *
     * @return Unique
     */
    private function getUniqueSlugValidationRule(): Unique {
        $unique = Unique::amongst(Page::class)->field('slugPart')->ignoreWithId($this->getId())
            ->addWhere('parent', ValidationService::EQUALS, $this->parent ? $this->parent->getId() : null);
        if($this->isHead()) {
            $unique = $unique->addWhere('headVersion', ValidationService::EQUALS, ValidationService::NULL);
        } else {
            $unique->addWhere('id', ValidationService::EQUALS, $this->getId());
        }
        return $unique;
    }

    /**
     * Returns the fields that should be fillable.
     *
     * @return array
     */
    public function getFillableFields(): array {
        return ['slugPart', 'title', 'author', 'description', 'tags', 'meta', 'content', 'richContent', 'options', 'parent', 'stage'];
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
        return ['slugPart', 'title', 'description'];
    }

    // TODO: get rid of this
    /**
     * Retrieves the status icon for the model.
     *
     * @return string|null
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
        return $this->getSlug();
    }

    /**
     * @return string
     */
    public function getTemplateCode() {
        return $this->content;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string {
        return $this->title;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string {
        return $this->description;
    }

    /**
     * @return string|null
     */
    public function getTags(): ?string {
        return $this->tags;
    }

    /**
     * @return string|null
     */
    public function getMeta(): ?string {
        return $this->meta;
    }

    /**
     * @param mixed $tags
     */
    public function setTags($tags): void {
        if(is_array($tags)) {
            $tags = implode(', ', $tags);
        }
        $this->tags = $tags;
    }

    /**
     * @param int|Page|null $page
     * @return $this
     * @throws ParentChildCycleException if there is a cycle
     */
    public function setParent($page): Page {
        if(is_integer($page))
        {
            $page = app(EntityManager::class)->find(Page::class, $page);
        }
        $parent = $page;
        while($parent) {
            if($parent->getHead() === $this->getHead())
            {
                throw new ParentChildCycleException('refusing to create cycle in parent - child relationship');
            }
            $parent = $parent->getParent();
        }
        if($page === null || $page->getSlug() === '/') {
            $this->parent = null;
        } else {
            $this->parent = $page;
        }
        return $this;
    }

    /**
     * Canonicalises the parent page.
     * $parent with slug '/' is the same as $parent = null
     *
     * @return Page|null
     */
    public function getParent(): ?Page
    {
        if(!$this->parent)
        {
            return null;
        }
        if($this->parent->getSlug() === '/')
        {
            return null;
        }
        return $this->parent;
    }

    /**
     * Return the slug for this page.
     * @return string
     */
    public function getSlug() {
        if($this->getParent() === null && $this->slugPart === '/')
        {
            return '/';
        }
        return ltrim(rtrim($this->getParent() ? $this->getParent()->getSlug() : '/', '/') . '/' . $this->slugPart, '/');
    }

    /**
     * @return array
     */
    public function toArray(): array {
        return [
            'id' => $this->id,
            'slug' => $this->getSlug(),
            'slugPart' => $this->slugPart,
            'title' => $this->title,
            'tags' => array_map(function($item) { return trim($item); }, explode(',', $this->tags)),
            'description' => $this->description,
            'meta' => $this->meta,
            'options' => $this->options,
            'richContent' => $this->richContent,
            'stage' => $this->stage,
            'headVersion' => $this->headVersion === null ? null : $this->headVersion->getId(),
            'createdAt' => $this->createdAt !== null ? $this->createdAt->format(DateTimeInterface::ATOM) : null,
            'createdBy' => $this->getCreatedBy() ? $this->getCreatedBy()->toArray() : null,
            'updatedAt' => $this->updatedAt !== null ? $this->updatedAt->format(DateTimeInterface::ATOM) : null,
            'updatedBy' => $this->getUpdatedBy() ? $this->getUpdatedBy()->toArray() : null,
            'deletedAt' => $this->deletedAt !== null ? $this->deletedAt->format(DateTimeInterface::ATOM) : null,
            'parent' => $this->getParent() ? $this->getParent()->getId() : null,
            // re-implement ExcludeTrashed and ExcludeVersions scope
            // Note: edge-case if deletedAt > current time
            'numChildren' => $this->children->matching((new Criteria())->where(new Comparison('deletedAt', Comparison::EQ, null))->andWhere(new Comparison('headVersion', Comparison::EQ, null)))->count()
        ];
    }

}
