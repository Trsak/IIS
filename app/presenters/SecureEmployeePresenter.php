<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;

abstract class SecureEmployeePresenter extends Nette\Application\UI\Presenter
{
    public function beforeRender(): void
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->flashMessage("You have to be logged in to view this page!", "Warning");
            $this->redirect('Login:', ['backlink' => $this->storeRequest()]);
        }

        $roles = $this->getUser()->getRoles();

        if ($roles[0] != 1) {
            $this->flashMessage("You have no permissions to view this page!", "Error");
            $this->redirect('Homepage:');
        }
    }
}
