<?php


namespace OxygenModule\Pages\Console;

use Illuminate\Auth\AuthManager;
use Illuminate\Console\Command;
use Illuminate\Contracts\Auth\StatefulGuard;
use Oxygen\Auth\Repository\UserRepositoryInterface;
use Oxygen\Core\Content\BlockEmphasisNode;
use Oxygen\Core\Content\GridCellNode;
use Oxygen\Core\Content\GridRowNode;
use Oxygen\Core\Content\MediaItemNode;
use Oxygen\Core\Content\ObjectLinkMark;
use Oxygen\Core\Content\ObjectLinkRegistry;
use Oxygen\Core\Content\PartialNode;
use Oxygen\Core\Content\RawHtmlNode;
use Oxygen\Core\Content\SmallMark;
use Oxygen\Core\Templating\TwigTemplateCompiler;
use Oxygen\Core\Theme\ThemeManager;
use OxygenModule\Pages\Repository\PageRepositoryInterface;
use Tiptap\Editor;
use Tiptap\Extensions\StarterKit;
use Tiptap\Marks\Link;
use Tiptap\Marks\Underline;
use Webmozart\Assert\Assert;

class ConvertPageContent extends Command {

    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'pages:convert-content {id} {--as-user=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Converts page content from raw HTML to rich Tiptap document structure';

    public function handle(PageRepositoryInterface $pages, AuthManager $auth, UserRepositoryInterface $users, TwigTemplateCompiler $templateCompiler, ThemeManager $theme, ObjectLinkRegistry $registry) {
        $theme->current()->boot();
        $user = $users->findByUsername($this->option('as-user'));
        $guard = $auth->guard();
        Assert::isInstanceOf($guard, StatefulGuard::class);
        $guard->login($user);
        $page = $pages->find(intval($this->argument('id')));
        $templateCompiler->setConvertToTipTap($page);
        $rendered = $templateCompiler->render($page);
        dump($rendered);
        $editor = new Editor([
            'extensions' => [
                new StarterKit,
                new Underline,
                new Link,
                new GridRowNode,
                new GridCellNode,
                new PartialNode,
                new MediaItemNode,
                new RawHtmlNode(),
                new BlockEmphasisNode(),
                new SmallMark(),
                new ObjectLinkMark($registry)
            ]
        ]);
        $editor->setContent($rendered);
        $jsonDoc = $editor->getDocument();
        dump($jsonDoc);
        $page->setRichContent($jsonDoc);
        $pages->persist($page);
    }

}
