<?php

namespace OxygenModule\Pages\Cache;


use OxygenModule\Pages\Entity\Page;

interface CacheInvalidationInterface {

    public function contentChanged(Page $item, $old, $new);
    public function pageRemoved(Page $page);

}