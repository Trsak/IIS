<?php

declare(strict_types=1);

namespace App\Presenters;

final class UserPresenter extends BasePresenter
{

    public function actionLogout(): void
    {
        $this->getUser()->logout();
        $this->flashMessage('You have been successfully logged out.', "Success");
        $this->redirect('Homepage:');
    }
}
