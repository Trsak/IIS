<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\LiteratureManager;
use App\Model\UserException;
use App\Model\UserManager;
use Nette;
use Nette\Application\UI\Form;

final class UserPresenter extends BasePresenter
{
    public function beforeRender(): void
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->flashMessage("You have to be logged in to view this page!", "Warning");
            $this->redirect('Login:', ['backlink' => $this->storeRequest()]);
        }
    }

    /** @var Nette\Database\Context */
    private $database;


    /** @var UserManager */
    private $userManager;

    public function __construct(Nette\Database\Context $database, LiteratureManager $literatureManager, UserManager $userManager)
    {
        parent::__construct($literatureManager);
        $this->database = $database;
        $this->userManager = $userManager;
    }

    public function renderBorrowings(): void
    {
        $borrowings = $this->database->table("borrowing")->select("borrowing.*, literature.title, literature.subtitle")->joinWhere("literature", "literature.id = borrowing.literature_id")
            ->where("borrowing.user_id = ?", $this->getUser()->getId())->fetchAll();
        $this->template->borrowings = $borrowings;
    }

    public function actionLogout(): void
    {
        $this->getUser()->logout();
        $this->flashMessage('You have been successfully logged out.', "Success");
        $this->redirect('Homepage:');
    }

    protected function createComponentChangePasswordForm(): Form
    {
        $form = new Form;
        $form->addPassword("passwordCurrent", null)
            ->setRequired("You have to fill your current password!");
        $form->addPassword('passwordNew', null)
            ->setRequired('You have to set your new password.')
            ->addRule(Form::MIN_LENGTH, 'Password must contain atleast %d characters.', 5);
        $form->addPassword('passwordVerify', null)
            ->setRequired('You have to fill your new password again for verification.')
            ->addRule(Form::EQUAL, 'New passwords are not equal!', $form['passwordNew']);
        $form->addSubmit("change", null);
        $form->onSuccess[] = [$this, 'changePassswordFormSucceeded'];
        return $form;
    }

    public function changePassswordFormSucceeded(Form $form, \stdClass $values): void
    {
        try {
            $this->userManager->changePassword($this->getUser()->getId(), $values->passwordCurrent, $values->passwordNew);
            $this->flashMessage("Your password has been changed!", "Success");
            $this->redirect("this");
        } catch (UserException $e) {
            $this->flashMessage($e->getMessage(), "Error");
        }
    }

    protected function createComponentEditMemberForm(): Form
    {
        $form = new Form;
        $form->addEmail("email", null)
            ->setRequired('Please insert email.');
        $form->addText("name", null)
            ->setRequired('Please insert name.');
        $form->addText("last_name", null)
            ->setRequired('Please insert last name.');
        $form->addText("telephone", null);
        $form->addText("birthdate", null);
        $form->addSubmit("edit", null);
        $form->onSuccess[] = [$this, 'editMemberFormSucceeded'];

        $form->setDefaults([
            'email' => $this->getUser()->getIdentity()->email,
            'name' => $this->getUser()->getIdentity()->name,
            'last_name' => $this->getUser()->getIdentity()->last_name,
            'telephone' => $this->getUser()->getIdentity()->telephone
        ]);

        if (strtotime($this->getUser()->getIdentity()->birthdate->format('d/m/Y')) > 0) {
            $form->setDefaults([
                'birthdate' => $this->getUser()->getIdentity()->birthdate->format('d/m/Y')
            ]);
        }

        return $form;
    }

    public function editMemberFormSucceeded(Form $form, \stdClass $values): void
    {
        try {
            $this->userManager->updateMember($this->getUser()->getId(), $values);
            $this->flashMessage("Details were sucessfully changed.", "Success");
            $this->redirect("this");
        } catch (UserException $e) {
            $this->flashMessage($e->getMessage(), "Error");
        }
    }
}
