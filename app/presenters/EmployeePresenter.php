<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\LiteratureAddException;
use App\Model\LiteratureManager;
use App\Model\UserException;
use App\Model\UserManager;
use DateTime;
use Nette\Application\UI\Form;
use Scriptotek\GoogleBooks\GoogleBooks;
use Nette;

final class EmployeePresenter extends SecureEmployeePresenter
{
    private $books;

    private $literatureManager;

    /** @var UserManager */
    private $userManager;

    /** @var Nette\Database\Context */
    private $database;

    public function __construct(Nette\Database\Context $database, GoogleBooks $books, LiteratureManager $literatureManager, UserManager $userManager)
    {
        parent::__construct();
        $this->books = $books;
        $this->literatureManager = $literatureManager;
        $this->database = $database;
        $this->userManager = $userManager;
    }

    public function renderMembers(): void
    {
        $this->template->members = $this->database->table("user")
            ->fetchAll();
    }

    public function renderLiterature(): void
    {
        $this->template->literatures = $this->database->table("literature")
            ->fetchAll();
    }

    public function renderEditMember(int $id): void
    {
        $member = $this->database->table("user")->where("id = ?", $id)->fetch();
        if (!$member) {
            $this->flashMessage("Member was not found.", "Error");
            $this->redirect("Homepage:");
        }
        $this->template->member = $member;
    }

    public function actionRemoveMember(int $id): void
    {
        $this->database->table('user')
            ->where('id', $id)
            ->delete();

        $this->flashMessage("Member was sucessfully deleted.", "Success");
        $this->redirect("Employee:Members");
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

    protected function createComponentExtendMemberForm(): Form
    {
        $form = new Form;
        $form->addSelect('extendTime', '', [
            '+1 month' => 'One month',
            '+6 months' => 'Six months',
            '+1 year' => 'One year',
        ])
            ->setRequired("You have to select extend time!");
        $form->addSubmit("extend", null);
        $form->onSuccess[] = [$this, 'extendMemberFormSucceeded'];
        return $form;
    }

    protected function createComponentAddMemberForm(): Form
    {
        $form = new Form;
        $form->addEmail("email", null)
            ->setRequired('Please insert member email.');
        $form->addText("name", null)
            ->setRequired('Please insert member name.');
        $form->addText("last_name", null)
            ->setRequired('Please insert member last name.');
        $form->addText("telephone", null);
        $form->addText("birthdate", null);
        $form->addText("member_until", null)
            ->setRequired('Please insert membership until date.');
        $form->addSubmit("add", null);
        $form->onSuccess[] = [$this, 'addMemberFormSucceeded'];
        return $form;
    }

    public function addMemberFormSucceeded(Form $form, \stdClass $values): void
    {
        try {
            $this->userManager->add($values);
            $this->flashMessage("Member was sucessfully added.", "Success");
        } catch (UserException $e) {
            $this->flashMessage($e->getMessage(), "Error");
        }
        $this->redirect("Employee:Members");
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
