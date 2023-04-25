<?php

namespace OxygenModule\Pages;

use Oxygen\Core\Content\ObjectLinkType;
use Oxygen\Core\Support\Str;
use Oxygen\Data\Exception\NoResultException;
use OxygenModule\Pages\Entity\Page;
use OxygenModule\Pages\Repository\PageRepositoryInterface;

class PageLinkType implements ObjectLinkType {

    private PageRepositoryInterface $pages;
    private array $pageLookupCache;

    public function __construct(PageRepositoryInterface $pages)
    {
        $this->pages = $pages;
        $this->pageLookupCache = [];
    }

    public function getName(): string {
        return 'page';
    }

    public function getParseConfig(): array {
        return [
            [
                'tag' => 'a',
                'getAttrs' => function(\DOMElement $DOMNode) {
                    return $this->getPageLink($DOMNode) !== null;
                }
            ]
        ];
    }

    public function getPageLink(\DOMElement $DOMNode): ?Page
    {
        dump($DOMNode->tagName);
        $url = $DOMNode->getAttribute('href');
        if($DOMNode->tagName == 'a' && $url !== null && Str::startsWith($url, '/')) {
            if(!isset($this->pageLookupCache[$url]))
            {
                try {
                    $this->pageLookupCache[$url] = $this->pages->findBySlug($url);
                } catch(NoResultException $e) {
                    $this->pageLookupCache[$url] = null;
                }
            }
            return $this->pageLookupCache[$url];
        } else {
            return null;
        }
    }

    public function parse(\DOMElement $DOMNode): ?array {
        $page = $this->getPageLink($DOMNode);
        if($page === null) {
            return null;
        }
        return [
            'type' => 'page',
            'id' => $page->getId(),
            'content' => $DOMNode->textContent
        ];
    }

    public function resolveLink(int $id): array {
        $page = $this->pages->find($id);
        return [
            'url' => $page->getSlug(),
            'title' => $page->getTitle(),
            'object' => $page->toArray()
        ];
    }
}