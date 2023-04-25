<?php


namespace OxygenModule\Pages\Console;

use Illuminate\Auth\AuthManager;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Oxygen\Auth\Repository\UserRepositoryInterface;
use Oxygen\Data\Exception\InvalidEntityException;
use Oxygen\Data\Repository\QueryParameters;
use OxygenModule\Pages\Entity\Page;
use OxygenModule\Pages\Repository\PageRepositoryInterface;

class AddTreeHierarchy extends Command {

    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'pages:tree-conversion {--as-user=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Discovers tree hierarchy for pages...';

    /**
     * @throws InvalidEntityException
     * @throws \Exception
     */
    public function handle(PageRepositoryInterface $pages, AuthManager $auth, UserRepositoryInterface $users) {
        $user = $users->findByUsername($this->option('as-user'));
        $auth->guard()->login($user);
        $processedPages = [];
        $allPages = $pages->all(QueryParameters::make()->excludeVersions());
        Arr::sort($allPages, function(Page $page) { return $page->getSlug(); });
        foreach($allPages as $page) {
            // try to find a page which has a given parent...
            $slug = ltrim($page->getSlug(), '/');
            $slugParts = explode('/', $slug);
            $slugPart = array_pop($slugParts);
            $slugPart = $slugPart === '' ? '/' : $slugPart;
            $page->setSlugPart($slugPart);
            $parentSlug = implode('/', $slugParts);
            if(!str_contains($slug, '/')) {
                $processedPages[$slug] = $page;
                $page->setParent(null);
            } else if(isset($processedPages[$parentSlug])) {
                $page->setParent($processedPages[$parentSlug]);
                $processedPages[$slug] = $page;
            } else {
                throw new \Exception('missing parent for page `' . $slug . '`');
            }
            $pages->persist($page, false);
        }
        $disp = [];
        foreach($processedPages as $page) {
            $disp[$page->getSlug()] = [ 'part' => $page->getSlugPart(), 'parent' => $page->getParent() ? $page->getParent()->getSlugPart() : null ];
        }
        dump($disp);

        // now go through an update all the versions as well...
        foreach($pages->all() as $page) {
            if($page->isHead()) { continue; }
            $page->setParent($page->getHead()->getParent());
            $page->setSlugPart($page->getHead()->getSlugPart());
            $pages->persist($page, false);
        }
        try {
            $pages->flush();
        } catch(InvalidEntityException $e) {
            $this->output->writeln('page with id ' . $e->getEntity()->getId() . ' failed validation ' . $e);
        }
//        $page = $pages->find(intval($this->argument('id')));
//        $templateCompiler->setConvertToTipTap($page);
//        $rendered = $templateCompiler->render($page);
//        dump($rendered);
//        $editor = new Editor([
//            'extensions' => [
//                new StarterKit,
//                new Underline,
//                new Link,
//                new GridRowNode,
//                new GridCellNode,
//                new PartialNode,
//                new MediaItemNode,
//                new RawHtmlNode(),
//                new BlockEmphasisNode(),
//                new SmallMark(),
//                new ObjectLinkMark($registry)
//            ]
//        ]);
//        $editor->setContent($rendered);
//        $jsonDoc = $editor->getDocument();
//        dump($jsonDoc);
//        $page->setRichContent($jsonDoc);
//        $pages->persist($page);
    }

}
