<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;
use Nette\Security\User;

final class LoginPresenter extends BasePresenter
{
    /** @persistent */
    public $backlink = '';

    /** @var User */
    private $user;

    public function __construct(User $user)
    {
        parent::__construct();
        $this->user = $user;
    }

    protected function createComponentLoginForm(): Form
    {
        $form = new Form;
        $form->addEmail("email", null)
            ->setRequired('Please enter your email.');
        $form->addPassword("password", null)
            ->setRequired('Please enter your password.');
        $form->addCheckbox('remember', null)
            ->setDefaultValue(false);
        $form->addSubmit("login", null);
        $form->onSuccess[] = [$this, 'loginFormSucceeded'];
        return $form;
    }

    public function loginFormSucceeded(Form $form, \stdClass $values): void
    {
        try {
            $this->user->setExpiration($values->remember ? '14 days' : '20 minutes');
            $this->user->login($values->email, $values->password);

            $this->flashMessage('You have been successfully logged in.', "Success");
            $this->restoreRequest($this->backlink);
            $this->redirect('Homepage:');
        } catch (AuthenticationException $e) {
            $form->addError('The username or password is incorrect.');
            return;
        }
    }
}
