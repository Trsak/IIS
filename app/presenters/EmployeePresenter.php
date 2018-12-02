<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\LiteratureAddException;
use App\Model\LiteratureManager;
use Nette\Application\UI\Form;
use Scriptotek\GoogleBooks\GoogleBooks;
use Nette;

final class EmployeePresenter extends SecureEmployeePresenter
{
    private $books;

    private $literatureManager;

    /** @var Nette\Database\Context */
    private $database;

    public function __construct(Nette\Database\Context $database, GoogleBooks $books, LiteratureManager $literatureManager)
    {
        parent::__construct();
        $this->books = $books;
        $this->literatureManager = $literatureManager;
        $this->database = $database;
    }

    public function beforeRender(): void
    {
        $this->template->literatures = $this->database->table("literature")
            ->fetchAll();
    }

    public function actionRemove(int $id): void
    {
        $this->database->table('literature_has_author')
            ->where('literature_id', $id)
            ->delete();

        $this->database->table('literature')
        ->where('id', $id)
        ->delete();

        $this->flashMessage("Literature was sucessfully deleted.", "Success");
        $this->redirect("Employee:Literature");
    }

    protected function createComponentAddLiteratureForm(): Form
    {
        $form = new Form;
        $form->addText("isbn", null)
            ->setRequired('Please insert ISBN.')
            ->addRule(Form::PATTERN, 'Wrong ISBN format.', '(97(8|9))?\d{9}(\d|X)');
        $form->addText("pieces_total", null)
            ->setRequired('Please insert total pieces.')
            ->addRule(Form::INTEGER, 'Total pieces muset be an number.');
        $form->addSubmit("add", null);
        $form->onSuccess[] = [$this, 'addLiteratureFormSucceeded'];
        return $form;
    }

    public function addLiteratureFormSucceeded(Form $form, \stdClass $values): void
    {
        $volume = $this->books->volumes->byIsbn($values->isbn);
        try {
            $this->literatureManager->add($volume, [$values->isbn, $values->pieces_total]);
            $this->flashMessage("Literature was sucessfully added.", "Success");
        } catch (LiteratureAddException $e) {
            $this->flashMessage($e->getMessage(), "Error");
        }

        $this->redirect("Employee:Literature");
    }
}
