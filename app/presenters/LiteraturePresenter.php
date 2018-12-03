<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\LiteratureManager;
use Nette;

final class LiteraturePresenter extends BasePresenter
{
    /** @var Nette\Database\Context */
    private $database;

    public function __construct(Nette\Database\Context $database, LiteratureManager $literatureManager)
    {
        parent::__construct($literatureManager);
        $this->database = $database;
    }

    public function renderDefault(int $id): void
    {
        $literature = $this->database->table("literature")->where("id = ?", $id)->fetch();
        if (!$literature) {
            $this->flashMessage("Literature was not found.", "Error");
            $this->redirect("Homepage:");
        }

        $this->template->validDate = true;
        if (strtotime($literature->publication_date->format("Y-m-d")) <= 0) {
            $this->template->validDate = false;
        }

        $this->template->literature = $literature;
        $this->template->authors = $this->database->table("literature_has_author")->where("literature_id = ?", $id)->fetchAll();
    }
}
