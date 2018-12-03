<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\LiteratureManager;
use Nette;
use Nette\Application\UI\Form;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Pagerfanta\View\TwitterBootstrap4View;

final class HomepagePresenter extends BasePresenter
{
    /** @var Nette\Database\Context */
    private $database;

    public function __construct(Nette\Database\Context $database, LiteratureManager $literatureManager)
    {
        parent::__construct($literatureManager);
        $this->database = $database;
    }

    public function renderDefault(int $page = 1, int $order = 1, $search = ""): void
    {
        $this->template->filter = false;
        $literatures = $this->database->table("literature");

        switch ($order) {
            case 2:
                $literatures->order("id ASC");
                $this->template->filter = true;
                break;
            case 3:
                $literatures->order("title ASC");
                $this->template->filter = true;
                break;
            case 4:
                $literatures->order("title DESC");
                $this->template->filter = true;
                break;
            default:
                $literatures->order("id DESC");
                break;
        }

        if ($search != "") {
            $literatures->where("title LIKE ?", "%" . $search . "%");
            $this->template->filter = true;
        }

        $literatures = $literatures->fetchAll();

        $this->template->count = count($literatures);
        $adapter = new ArrayAdapter($literatures);
        $pagerfanta = new Pagerfanta($adapter);

        $pagerfanta->setMaxPerPage(6);
        $pagerfanta->setCurrentPage($page);

        $routeGenerator = function ($page) {
            $query = $_GET;
            unset($query['page']);
            return $this->template->basePath . '/literatures/' . $page . '/?' . http_build_query($query);
        };

        $view = new TwitterBootstrap4View();
        $options = array('proximity' => 3);
        $this->template->pagination = $view->render($pagerfanta, $routeGenerator, $options);
        $this->template->literatures = $pagerfanta->getCurrentPageResults();
    }

    protected function createComponentSearchLiteratureForm(): Form
    {
        $form = new Form;
        $form->addText("title", null);
        $form->addSelect('ordering', null, [
            '1' => 'Order by newest',
            '2' => 'Order by oldest',
            '3' => 'Order by title ascending',
            '4' => 'Order by title descending',
        ])
            ->setRequired('Please select ordering.');
        $form->addSubmit("search", null);
        $form->onSuccess[] = [$this, 'searchLiteratureFormSucceeded'];


        $params = $this->request->getParameters();

        if (isset($params["search"])) {
            $form->setDefaults([
                'title' => $params["search"]
            ]);
        }

        if (isset($params["order"])) {
            $form->setDefaults([
                'ordering' => $params["order"]
            ]);
        }

        return $form;
    }

    public function searchLiteratureFormSucceeded(Form $form, \stdClass $values): void
    {
        $this->redirect("Homepage:Default", array("page" => 1, "search" => $values->title, "order" => $values->ordering));
    }
}
