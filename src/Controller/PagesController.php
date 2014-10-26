<?php

namespace Oxygen\Pages\Controller;

use App;
use DbView;
use Config;
use View;
use Response;
use Lang;

use Oxygen\Core\Blueprint\Manager as BlueprintManager;
use Oxygen\Core\Http\Notification;
use Oxygen\Core\Model\Validating\InvalidModelException;
use Oxygen\Crud\Controller\VersionableCrudController;
use Oxygen\Pages\Model\Page;

use Illuminate\Database\Eloquent\ModelNotFoundException;

class PagesController extends VersionableCrudController {

    /**
     * Constructs the AuthController.
     *
     * @param BlueprintManager $manager
     */

    public function __construct(BlueprintManager $manager) {
        parent::__construct($manager, 'Page', 'Oxygen\Pages\Model\Page');
    }

    /**
     * View a page with a theme.
     *
     * @param string $slug the URI slug
     * @return Response
     */

    public function getView($slug = '/') {
        try {
            $page = Page::where('slug', '=', $slug)->firstOrFail();
        } catch(ModelNotFoundException $e) {
            App::abort(404, "Slug not found");
        }

        $content = View::model($page, 'content')->with(['page' => $page])->render();
        $options = json_decode($page->options, true);

        return View::make(Config::get('oxygen/pages::theme'), [
            'page' => $page,
            'title' => $page->title,
            'content' => $content,
            'options' => $options,
            'description' => $page->description,
            'tags' => $page->tags,
            'meta' => $page->meta
        ]);
    }

    /**
     * Publish or unpublish a page.
     *
     * @param mixed $item the item
     * @return Response
     */

    public function postPublish($item) {
        try {
            $item = $this->getItem($item);
            $item->stage = $item->published() ? Page::STAGE_DRAFT : Page::STAGE_PUBLISHED;
            $item->save();

            return Response::notification(
                new Notification(
                    Lang::get($item->published() ? 'messages.pages.published' : 'messages.pages.unpublished', [
                        'title' => $item->title
                    ])
                ),
                ['refresh' => true]
            );
        } catch(InvalidModelException $e) {
            return Response::notification(
                new Notification($e->getErrors()->first(), Notification::FAILED)
            );
        }
    }

}
