<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\LiteratureAddException;
use App\Model\LiteratureManager;
use DateInterval;
use DateTime;
use Nette;

abstract class BasePresenter extends Nette\Application\UI\Presenter
{
    private $literatureManager;

    public function __construct(LiteratureManager $literatureManager)
    {
        parent::__construct();
        $this->literatureManager = $literatureManager;
    }

    public function actionBorrowLiterature($literature): void
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->flashMessage("You have to be logged in to borrow literature!", "Warning");
            $this->redirect('Login:', ['backlink' => $this->storeRequest()]);
        }

        $dateNow = new DateTime();
        $timeInterval = DateInterval::createFromDateString("1 month");
        $dateNow->add($timeInterval);

        $values = (object)[
            'user' => $this->getUser()->getId(),
            'literature' => $literature,
            'return_until' => $dateNow->format("d/m/Y"),
        ];

        try {
            $this->literatureManager->addBorrowing($values);
            $this->flashMessage("Literature was sucesfully borrowed! You can now pick it up in our library.", "Success");
        } catch (LiteratureAddException $e) {
            $this->flashMessage($e->getMessage(), "Error");
        }
        $this->redirect("User:Borrowings");
    }
}
